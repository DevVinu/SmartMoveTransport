<?php
// Database manager
class Database
{
    // Oracle XE Configuration
    private static $oraUser    = 'system';
    private static $oraPass    = 'darkking10';
    private static $oraHost    = 'localhost';
    private static $oraPort    = '1521';
    private static $oraService = 'XEPDB1';
    private static $oraCharset = 'AL32UTF8';
    private static $oraConn    = null;

    // MongoDB Configuration
    private static $mongoUri   = 'mongodb://localhost:27017';
    private static $mongoDb    = 'smartmove_db';
    private static $mongoConn  = null;

    // Column aliases for clean PHP access
    private static $aliases = [
        'tripid' => 'tripId', 'routeid' => 'routeId', 'routename' => 'routeName', 'routetype' => 'routeType',
        'vehicleid' => 'vehicleId', 'vehicletype' => 'vehicleType', 'registrationno' => 'registrationNo',
        'driverid' => 'driverId', 'drivername' => 'driverName', 'driverstatus' => 'driverStatus',
        'licensenumber' => 'licenseNumber', 'licenseno' => 'licenseNo', 'tripdate' => 'tripDate',
        'departuretime' => 'departureTime', 'arrivaltime' => 'arrivalTime', 'availableseats' => 'availableSeats',
        'fareamount' => 'fareAmount', 'tripstatus' => 'tripStatus', 'shiftid' => 'shiftId', 'shiftstatus' => 'shiftStatus',
        'maintenanceid' => 'maintenanceId', 'maintenancestatus' => 'maintenanceStatus', 'nextservicedate' => 'nextServiceDate',
        'staffid' => 'staffId', 'staffname' => 'staffName', 'bookingid' => 'bookingId', 'bookingstatus' => 'bookingStatus',
        'passengerid' => 'passengerId', 'passengername' => 'passengerName', 'seatnumber' => 'seatNumber',
        'ticketprice' => 'ticketPrice', 'paymentmethod' => 'paymentMethod', 'paymentstatus' => 'paymentStatus',
        'accountstatus' => 'accountStatus', 'userid' => 'userId', 'firstname' => 'firstName', 'lastname' => 'lastName',
        'distancekm' => 'distanceKm', 'estimateddurationminutes' => 'estimatedDurationMinutes', 'basefare' => 'baseFare'
    ];

    // Establish Oracle Connection
    public static function getOracle()
    {
        if (self::$oraConn !== null) {
            return self::$oraConn;
        }
        if (!function_exists('oci_connect')) {
            return null;
        }

        $dsn = sprintf('%s:%s/%s', self::$oraHost, self::$oraPort, self::$oraService);
        self::$oraConn = @oci_connect(self::$oraUser, self::$oraPass, $dsn, self::$oraCharset);
        return self::$oraConn ?: null;
    }

    // Execute Oracle SELECT query
    public static function queryOracle($sql, $params = [])
    {
        $conn = self::getOracle();
        if (!$conn) return [];

        $stmt = oci_parse($conn, $sql);
        if (!$stmt) return [];

        foreach ($params as $k => $v) {
            $bind = strpos($k, ':') === 0 ? $k : ':' . $k;
            oci_bind_by_name($stmt, $bind, $params[$k]);
        }

        if (!@oci_execute($stmt)) {
            oci_free_statement($stmt);
            return [];
        }

        $rows = [];
        while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $item = [];
            foreach ($row as $k => $v) {
                $low = strtolower($k);
                $item[$k] = $v;
                $item[$low] = $v;
                if (isset(self::$aliases[$low])) {
                    $item[self::$aliases[$low]] = $v;
                }
            }
            $rows[] = $item;
        }

        oci_free_statement($stmt);
        return $rows;
    }

    // Execute Oracle INSERT, UPDATE, DELETE (Auto-commit)
    public static function executeOracle($sql, $params = [])
    {
        $conn = self::getOracle();
        if (!$conn) return false;

        $stmt = oci_parse($conn, $sql);
        if (!$stmt) return false;

        foreach ($params as $k => $v) {
            $bind = strpos($k, ':') === 0 ? $k : ':' . $k;
            oci_bind_by_name($stmt, $bind, $params[$k]);
        }

        $ok = @oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($stmt);
        return $ok;
    }

    // Establish MongoDB Connection
    public static function getMongo()
    {
        if (self::$mongoConn !== null) {
            return self::$mongoConn;
        }
        if (!extension_loaded('mongodb')) {
            return null;
        }

        try {
            if (class_exists('MongoDB\\Client')) {
                $client = new \MongoDB\Client(self::$mongoUri);
                self::$mongoConn = $client->selectDatabase(self::$mongoDb);
            } else {
                self::$mongoConn = new \MongoDB\Driver\Manager(self::$mongoUri);
            }
            return self::$mongoConn;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Query MongoDB Collection
    public static function queryMongo($collection, $filter = [], $options = [])
    {
        $db = self::getMongo();
        if (!$db) return [];

        try {
            $results = [];
            if (is_object($db) && method_exists($db, 'selectCollection')) {
                $cursor = $db->selectCollection($collection)->find($filter, $options);
                foreach ($cursor as $doc) {
                    $results[] = json_decode(json_encode($doc), true);
                }
            } elseif ($db instanceof \MongoDB\Driver\Manager) {
                $query = new \MongoDB\Driver\Query($filter, $options);
                $cursor = $db->executeQuery(self::$mongoDb . '.' . $collection, $query);
                foreach ($cursor as $doc) {
                    $results[] = json_decode(json_encode($doc), true);
                }
            }
            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    // Insert into MongoDB
    public static function insertMongo($collection, $documents)
    {
        $db = self::getMongo();
        if (!$db || empty($documents)) return false;

        $docs = isset($documents[0]) && is_array($documents[0]) ? $documents : [$documents];
        try {
            if (is_object($db) && method_exists($db, 'selectCollection')) {
                return $db->selectCollection($collection)->insertMany($docs)->getInsertedCount() > 0;
            } elseif ($db instanceof \MongoDB\Driver\Manager) {
                $bulk = new \MongoDB\Driver\BulkWrite();
                foreach ($docs as $d) $bulk->insert($d);
                return $db->executeBulkWrite(self::$mongoDb . '.' . $collection, $bulk)->getInsertedCount() > 0;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    // Delete from MongoDB
    public static function deleteMongo($collection, $filter = [])
    {
        $db = self::getMongo();
        if (!$db) return false;

        try {
            if (is_object($db) && method_exists($db, 'selectCollection')) {
                return $db->selectCollection($collection)->deleteMany($filter)->getDeletedCount() > 0;
            } elseif ($db instanceof \MongoDB\Driver\Manager) {
                $bulk = new \MongoDB\Driver\BulkWrite();
                $bulk->delete($filter);
                return $db->executeBulkWrite(self::$mongoDb . '.' . $collection, $bulk)->getDeletedCount() > 0;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    // Dual Database Health Check
    public static function checkHealth()
    {
        $ora = self::getOracle();
        $mng = self::getMongo();

        return [
            'oracle' => [
                'enabled'   => function_exists('oci_connect'),
                'connected' => $ora !== null,
                'version'   => $ora ? oci_server_version($ora) : null,
                'message'   => $ora ? 'Connected to ' . self::$oraService : 'Connection failed'
            ],
            'mongodb' => [
                'enabled'   => extension_loaded('mongodb'),
                'connected' => $mng !== null,
                'version'   => null,
                'message'   => $mng ? 'Connected to ' . self::$mongoDb : 'Connection failed'
            ]
        ];
    }
}
