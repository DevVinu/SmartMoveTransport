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
        'maintenancetype' => 'maintenanceType',
        'staffid' => 'staffId', 'staffname' => 'staffName', 'staffstatus' => 'staffStatus',
        'bookingid' => 'bookingId', 'bookingstatus' => 'bookingStatus', 'bookingdate' => 'bookingDate',
        'passengerid' => 'passengerId', 'passengername' => 'passengerName', 'seatnumber' => 'seatNumber',
        'ticketprice' => 'ticketPrice', 'paymentmethod' => 'paymentMethod', 'paymentstatus' => 'paymentStatus',
        'accountstatus' => 'accountStatus', 'userid' => 'userId', 'firstname' => 'firstName', 'lastname' => 'lastName',
        'distancekm' => 'distanceKm', 'estimateddurationminutes' => 'estimatedDurationMinutes', 'basefare' => 'baseFare',
        'tripdateformatted' => 'tripDateFormatted', 'bookingdateformatted' => 'bookingDateFormatted',
        'dateofbirth' => 'dateOfBirth', 'licenseexpirydate' => 'licenseExpiryDate', 'licensetype' => 'licenseType',
        'shiftdate' => 'shiftDate', 'starttime' => 'startTime', 'endtime' => 'endTime',
        'fuellogid' => 'fuelLogId', 'refueldate' => 'refuelDate', 'stationname' => 'stationName',
        'literspumped' => 'litersPumped', 'fuelquantityliters' => 'fuelQuantityLiters', 'totalcost' => 'totalCost', 'odometerreading' => 'odometerReading',
        'refundid' => 'refundId', 'refundamount' => 'refundAmount', 'refundreason' => 'refundReason', 'refunddate' => 'refundDate', 'refundstatus' => 'refundStatus',
        'paymentid' => 'paymentId', 'amountpaid' => 'amountPaid', 'paymentdate' => 'paymentDate', 'transactionreference' => 'transactionReference',
        'promoid' => 'promoId', 'promocode' => 'promoCode', 'discountpercentage' => 'discountPercentage',
        'fullname' => 'fullName', 'admincode' => 'adminCode', 'privilegelevel' => 'privilegeLevel', 'preferredpaymentmethod' => 'preferredPaymentMethod',
        'nextid' => 'nextId', 'completedtrips' => 'completedTrips', 'totalshifts' => 'totalShifts', 'totalfuellogged' => 'totalFuelLogged',
        'totalbookings' => 'totalBookings', 'totalrevenue' => 'totalRevenue', 'transactioncount' => 'transactionCount', 'totalcollected' => 'totalCollected',
        'urgencystatus' => 'urgencyStatus',
        'routestopid' => 'routeStopId', 'stoporder' => 'stopOrder', 'stopid' => 'stopId', 'stopname' => 'stopName', 'locationaddress' => 'locationAddress',
        'arrivaltimeoffset' => 'arrivalTimeOffset', 'departuretimeoffset' => 'departureTimeOffset',
        'feedbackid' => 'feedbackId', 'feedbackdate' => 'feedbackDate',
        'licensedocument' => 'licenseDocument'
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

    // Execute Oracle INSERT, UPDATE, DELETE 
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
    
    public static function callCursorProcedure($procedureCall, $cursorParam = ':pCursor')
    {
        $conn = self::getOracle();
        if (!$conn) return [];

        $cursor = oci_new_cursor($conn);
        if (!$cursor) return [];

        $stmt = oci_parse($conn, $procedureCall);
        if (!$stmt) {
            oci_free_statement($cursor);
            return [];
        }

        oci_bind_by_name($stmt, $cursorParam, $cursor, -1, OCI_B_CURSOR);

        if (!@oci_execute($stmt)) {
            oci_free_statement($cursor);
            oci_free_statement($stmt);
            return [];
        }

        if (!@oci_execute($cursor)) {
            oci_free_statement($cursor);
            oci_free_statement($stmt);
            return [];
        }

        $rows = [];
        while ($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) {
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

        oci_free_statement($cursor);
        oci_free_statement($stmt);
        return $rows;
    }

    // Call ticket booking procedure
    public static function callBookTicket($passengerId, $tripId, $promoCode, $seatNumber, $paymentMethod)
    {
        $conn = self::getOracle();
        if (!$conn) {
            return ['status' => 'ERROR', 'message' => 'Oracle database offline'];
        }

        $sql = "BEGIN prcBookTicket(:pPassengerId, :pTripId, :pPromoCode, :pSeatNumber, :pPaymentMethod, :pBookingId, :pAmountPaid, :pStatus, :pMessage); END;";
        $stmt = oci_parse($conn, $sql);
        if (!$stmt) {
            return ['status' => 'ERROR', 'message' => 'Failed to prepare booking statement'];
        }

        $pPassengerId = (int)$passengerId;
        $pTripId = (int)$tripId;
        $pPromoCode = (string)($promoCode ?? '');
        $pSeatNumber = !empty($seatNumber) ? (int)$seatNumber : null;
        $pPaymentMethod = (string)($paymentMethod ?: 'CARD');

        $pBookingId = 0;
        $pAmountPaid = 0.0;
        $pStatus = str_repeat(' ', 30);
        $pMessage = str_repeat(' ', 300);

        oci_bind_by_name($stmt, ':pPassengerId', $pPassengerId);
        oci_bind_by_name($stmt, ':pTripId', $pTripId);
        oci_bind_by_name($stmt, ':pPromoCode', $pPromoCode);
        oci_bind_by_name($stmt, ':pSeatNumber', $pSeatNumber);
        oci_bind_by_name($stmt, ':pPaymentMethod', $pPaymentMethod);

        oci_bind_by_name($stmt, ':pBookingId', $pBookingId, 32);
        oci_bind_by_name($stmt, ':pAmountPaid', $pAmountPaid, 32);
        oci_bind_by_name($stmt, ':pStatus', $pStatus, 50);
        oci_bind_by_name($stmt, ':pMessage', $pMessage, 500);

        $res = @oci_execute($stmt);
        if (!$res) {
            $e = oci_error($stmt);
            oci_free_statement($stmt);
            return ['status' => 'ERROR', 'message' => $e['message'] ?? 'Execution error'];
        }

        oci_free_statement($stmt);
        return [
            'bookingId'   => (int)$pBookingId,
            'amountPaid'  => (float)$pAmountPaid,
            'status'      => trim($pStatus),
            'message'     => trim($pMessage)
        ];
    }

    // Call refund processing procedure
    public static function callProcessRefund($bookingId, $staffId, $refundReason)
    {
        $conn = self::getOracle();
        if (!$conn) {
            return ['status' => 'ERROR', 'message' => 'Oracle database offline'];
        }

        $sql = "BEGIN prcProcessRefund(:pBookingId, :pStaffId, :pRefundReason, :pRefundId, :pRefundAmount, :pStatus, :pMessage); END;";
        $stmt = oci_parse($conn, $sql);
        if (!$stmt) {
            return ['status' => 'ERROR', 'message' => 'Failed to prepare refund statement'];
        }

        $pBookingId = (int)$bookingId;
        $pStaffId = (int)($staffId ?: 1);
        $pRefundReason = (string)($refundReason ?: 'Passenger Cancellation');

        $pRefundId = 0;
        $pRefundAmount = 0.0;
        $pStatus = str_repeat(' ', 30);
        $pMessage = str_repeat(' ', 300);

        oci_bind_by_name($stmt, ':pBookingId', $pBookingId);
        oci_bind_by_name($stmt, ':pStaffId', $pStaffId);
        oci_bind_by_name($stmt, ':pRefundReason', $pRefundReason);

        oci_bind_by_name($stmt, ':pRefundId', $pRefundId, 32);
        oci_bind_by_name($stmt, ':pRefundAmount', $pRefundAmount, 32);
        oci_bind_by_name($stmt, ':pStatus', $pStatus, 50);
        oci_bind_by_name($stmt, ':pMessage', $pMessage, 500);

        $res = @oci_execute($stmt);
        if (!$res) {
            $e = oci_error($stmt);
            oci_free_statement($stmt);
            return ['status' => 'ERROR', 'message' => $e['message'] ?? 'Execution error'];
        }

        oci_free_statement($stmt);
        return [
            'refundId'     => (int)$pRefundId,
            'refundAmount' => (float)$pRefundAmount,
            'status'       => trim($pStatus),
            'message'      => trim($pMessage)
        ];
    }

    // Confirm payment (Cash / Post-paid settlement)
    public static function callConfirmPayment($paymentId, $collectorId = 0, $notes = 'Payment confirmed at boarding')
    {
        $conn = self::getOracle();
        if (!$conn) {
            return ['status' => 'ERROR', 'message' => 'Oracle database offline'];
        }

        $paymentId = (int)$paymentId;
        $collectorId = (int)$collectorId;

        // Fetch payment details
        $rows = self::queryOracle("
            SELECT p.paymentId, p.bookingId, p.amountPaid, p.paymentMethod, p.paymentStatus, b.passengerId
            FROM payment p
            JOIN booking b ON p.bookingId = b.bookingId
            WHERE p.paymentId = :pId
        ", [':pId' => $paymentId]);

        if (empty($rows)) {
            return ['status' => 'ERROR', 'message' => 'Payment transaction not found'];
        }

        $payRec = $rows[0];
        $bookingId = (int)($payRec['bookingId'] ?? $payRec['bookingid']);
        $amountPaid = (float)($payRec['amountPaid'] ?? $payRec['amountpaid']);
        $method = $payRec['paymentMethod'] ?? $payRec['paymentmethod'];

        // Update payment in Oracle
        self::executeOracle("
            UPDATE payment
            SET paymentStatus = 'PAID', paymentDate = SYSDATE
            WHERE paymentId = :pId
        ", [':pId' => $paymentId]);

        // Ensure booking is marked CONFIRMED
        self::executeOracle("
            UPDATE booking
            SET bookingStatus = 'CONFIRMED'
            WHERE bookingId = :bId
        ", [':bId' => $bookingId]);

        // Log payment audit in MongoDB
        self::insertMongo('payment_audit_logs', [
            'paymentId'     => $paymentId,
            'bookingId'     => $bookingId,
            'amountPaid'    => $amountPaid,
            'paymentMethod' => $method,
            'action'        => 'PAYMENT_SETTLED',
            'confirmedBy'   => $collectorId,
            'notes'         => $notes,
            'settledAt'     => date('Y-m-d H:i:s')
        ]);

        return [
            'status'     => 'SUCCESS',
            'paymentId'  => $paymentId,
            'bookingId'  => $bookingId,
            'amountPaid' => $amountPaid,
            'message'    => "Payment #$paymentId confirmed and verified successfully."
        ];
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
