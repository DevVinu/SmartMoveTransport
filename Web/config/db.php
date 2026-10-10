<?php
/**
 * SmartMove Transport Solutions — Dual Database Manager
 * Handles hybrid connectivity:
 *   1. Oracle Database 19c/21c XE (Relational / ACID transactions)
 *   2. MongoDB Community Server 7.0+ (NoSQL / Unstructured Documents & Telemetry)
 */

class Database {
    // ------------------------------------------------------------------------
    // ORACLE CONFIGURATION
    // ------------------------------------------------------------------------
    private static $oraUser    = 'smartmove_user';
    private static $oraPass    = 'password123';
    private static $oraHost    = 'localhost';
    private static $oraPort    = '1521';
    private static $oraService = 'XEPDB1';
    private static $oraCharset = 'AL32UTF8';
    private static $oraConn    = null;

    // ------------------------------------------------------------------------
    // MONGODB CONFIGURATION
    // ------------------------------------------------------------------------
    private static $mongoUri   = 'mongodb://localhost:27017';
    private static $mongoDb    = 'smartmove_db';
    private static $mongoConn  = null;

    /**
     * Get or establish an active Oracle OCI8 connection.
     * @return resource|null OCI8 connection resource or null on failure
     */
    public static function getOracle() {
        if (self::$oraConn !== null) {
            return self::$oraConn;
        }

        if (!function_exists('oci_connect')) {
            error_log("[Database] Oracle OCI8 extension is not enabled in php.ini");
            return null;
        }

        $connectionString = sprintf(
            '%s:%s/%s',
            self::$oraHost,
            self::$oraPort,
            self::$oraService
        );

        // Attempt connection to Oracle XE
        self::$oraConn = @oci_connect(
            self::$oraUser,
            self::$oraPass,
            $connectionString,
            self::$oraCharset
        );

        if (!self::$oraConn) {
            $err = oci_error();
            error_log("[Database] Oracle Connection Error: " . ($err['message'] ?? 'Unknown error'));
            self::$oraConn = null;
        }

        return self::$oraConn;
    }

    /**
     * Execute an Oracle SELECT query and return rows as an associative array.
     * @param string $sql SQL query with optional :bind variables
     * @param array $params Associative array of bind parameters e.g. [':id' => 1]
     * @return array Array of associative rows
     */
    public static function queryOracle($sql, $params = []) {
        $conn = self::getOracle();
        if (!$conn) {
            return [];
        }

        $stmt = oci_parse($conn, $sql);
        if (!$stmt) {
            $err = oci_error($conn);
            error_log("[Database] Oracle Parse Error: " . ($err['message'] ?? ''));
            return [];
        }

        // Bind parameters
        foreach ($params as $key => $val) {
            $bindKey = strpos($key, ':') === 0 ? $key : ':' . $key;
            oci_bind_by_name($stmt, $bindKey, $params[$key]);
        }

        $exec = @oci_execute($stmt);
        if (!$exec) {
            $err = oci_error($stmt);
            error_log("[Database] Oracle Execute Error: " . ($err['message'] ?? ''));
            oci_free_statement($stmt);
            return [];
        }

        $rows = [];
        while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
            // Normalize column keys to lowercase for cleaner access in PHP
            $normalized = [];
            foreach ($row as $k => $v) {
                $normalized[strtolower($k)] = $v;
            }
            $rows[] = $normalized;
        }

        oci_free_statement($stmt);
        return $rows;
    }

    /**
     * Execute an INSERT, UPDATE, DELETE or DDL statement in Oracle with auto-commit.
     * @param string $sql SQL statement
     * @param array $params Bind parameters
     * @return bool True on success, false on error
     */
    public static function executeOracle($sql, $params = []) {
        $conn = self::getOracle();
        if (!$conn) {
            return false;
        }

        $stmt = oci_parse($conn, $sql);
        if (!$stmt) {
            return false;
        }

        foreach ($params as $key => $val) {
            $bindKey = strpos($key, ':') === 0 ? $key : ':' . $key;
            oci_bind_by_name($stmt, $bindKey, $params[$key]);
        }

        $success = @oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
        if (!$success) {
            $err = oci_error($stmt);
            error_log("[Database] Oracle Execution Failure: " . ($err['message'] ?? ''));
        }

        oci_free_statement($stmt);
        return $success;
    }

    /**
     * Get or establish an active MongoDB Connection.
     * Supports both MongoDB\Client (Composer library) and native MongoDB\Driver\Manager.
     * @return mixed MongoDB client or manager instance
     */
    public static function getMongo() {
        if (self::$mongoConn !== null) {
            return self::$mongoConn;
        }

        // Check if MongoDB extension is loaded
        if (!extension_loaded('mongodb')) {
            error_log("[Database] MongoDB extension (php_mongodb.dll) is not loaded in php.ini");
            return null;
        }

        // If composer MongoDB\Client is available
        if (class_exists('MongoDB\\Client')) {
            try {
                $client = new \MongoDB\Client(self::$mongoUri);
                self::$mongoConn = $client->selectDatabase(self::$mongoDb);
                return self::$mongoConn;
            } catch (\Exception $e) {
                error_log("[Database] MongoDB Library Error: " . $e->getMessage());
                return null;
            }
        }

        // Native driver fallback using Manager
        try {
            $manager = new \MongoDB\Driver\Manager(self::$mongoUri);
            self::$mongoConn = $manager;
            return self::$mongoConn;
        } catch (\Exception $e) {
            error_log("[Database] MongoDB Manager Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Query a MongoDB collection and return an array of documents.
     * @param string $collection Collection name (e.g., 'passenger_reviews')
     * @param array $filter MongoDB filter array e.g. ['rating' => 5]
     * @param array $options Query options e.g. ['sort' => ['createdAt' => -1], 'limit' => 10]
     * @return array Array of documents
     */
    public static function queryMongo($collection, $filter = [], $options = []) {
        $db = self::getMongo();
        if (!$db) {
            return [];
        }

        // Composer library format
        if (is_object($db) && method_exists($db, 'selectCollection')) {
            try {
                $col = $db->selectCollection($collection);
                $cursor = $col->find($filter, $options);
                return iterator_to_array($cursor);
            } catch (\Exception $e) {
                error_log("[Database] Mongo Query Error: " . $e->getMessage());
                return [];
            }
        }

        // Native Driver Manager format
        if ($db instanceof \MongoDB\Driver\Manager) {
            try {
                $query = new \MongoDB\Driver\Query($filter, $options);
                $cursor = $db->executeQuery(self::$mongoDb . '.' . $collection, $query);
                return iterator_to_array($cursor);
            } catch (\Exception $e) {
                error_log("[Database] Native Mongo Query Error: " . $e->getMessage());
                return [];
            }
        }

        return [];
    }

    /**
     * Check health status of both database servers for dashboards & system diagnostics.
     * @return array Status array for Oracle and MongoDB
     */
    public static function checkHealth() {
        $status = [
            'oracle' => [
                'enabled'   => function_exists('oci_connect'),
                'connected' => false,
                'version'   => null,
                'message'   => 'Not tested'
            ],
            'mongodb' => [
                'enabled'   => extension_loaded('mongodb'),
                'connected' => false,
                'version'   => null,
                'message'   => 'Not tested'
            ]
        ];

        // Oracle Health Check
        if ($status['oracle']['enabled']) {
            $conn = self::getOracle();
            if ($conn) {
                $status['oracle']['connected'] = true;
                $status['oracle']['version']   = oci_server_version($conn);
                $status['oracle']['message']   = 'Connected successfully to ' . self::$oraService;
            } else {
                $status['oracle']['message']   = 'Could not connect to Oracle XE (Check port 1521 or credentials)';
            }
        } else {
            $status['oracle']['message'] = 'PHP oci8 extension is disabled in php.ini';
        }

        // MongoDB Health Check
        if ($status['mongodb']['enabled']) {
            $mongo = self::getMongo();
            if ($mongo) {
                $status['mongodb']['connected'] = true;
                $status['mongodb']['message']   = 'Connected successfully to ' . self::$mongoDb;
            } else {
                $status['mongodb']['message']   = 'Could not connect to MongoDB service on port 27017';
            }
        } else {
            $status['mongodb']['message'] = 'PHP mongodb extension is disabled in php.ini';
        }

        return $status;
    }
}

