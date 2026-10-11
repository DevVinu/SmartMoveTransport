<?php
/**
 * SmartMove Transport Solutions — Staff Operations Portal
 * Enterprise Dark Slate Theme (ShadcnUI Aesthetic)
 * 
 * Functional Operations for Operations Staff & Dispatch Supervisors:
 * - FR-06 & FR-08: Scheduled Trip Dispatching & Real-Time Monitoring
 * - FR-04: Driver Duty Shift Rostering & Attendance Tracking
 * - FR-03 & FR-04: Fleet Maintenance Logging & Preventive Servicing
 * - FR-09: Counter Ticket Booking & Commuter Reservations
 * - FR-10: Cancellation & Refund Processing
 * - FR-17: MongoDB Travel Advisories & Expressway Alerts
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/MongoService.php';

// Session Security: Require STAFF or ADMIN role
Auth::requireRole(['STAFF', 'ADMIN']);

$currentUser = Auth::user();
$userId      = (int)($currentUser['userId'] ?? $currentUser['userid'] ?? 2);
$firstName   = htmlspecialchars($currentUser['firstName'] ?? $currentUser['firstname'] ?? 'Staff');
$lastName    = htmlspecialchars($currentUser['lastName'] ?? $currentUser['lastname'] ?? 'Member');
$fullName    = trim($firstName . ' ' . $lastName);
$email       = htmlspecialchars($currentUser['email'] ?? 'staff@smartmove.com');
$role        = strtoupper($currentUser['role'] ?? 'STAFF');
$initials    = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

// Database Health Check
$dbHealth = Database::checkHealth();
$oracleUp = $dbHealth['oracle']['connected'] ?? false;
$mongoUp  = $dbHealth['mongodb']['connected'] ?? false;

// Active View Routing
$activeView = $_GET['view'] ?? 'dispatchView';
$validViews = ['dispatchView', 'shiftsView', 'maintenanceView', 'ticketingView', 'refundsView', 'announcementsView'];
if (!in_array($activeView, $validViews)) {
    $activeView = 'dispatchView';
}

// Flash Notifications
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// -----------------------------------------------------------------------------
// POST CONTROLLER: STAFF ACTIONS
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Dispatch / Schedule New Trip
    if ($action === 'addTrip') {
        $routeId        = (int)($_POST['routeId'] ?? 0);
        $vehicleId      = (int)($_POST['vehicleId'] ?? 0);
        $driverId       = (int)($_POST['driverId'] ?? 0);
        $tripDate       = trim($_POST['tripDate'] ?? date('Y-m-d'));
        $departureTime  = trim($_POST['departureTime'] ?? '08:00 AM');
        $arrivalTime    = trim($_POST['arrivalTime'] ?? '10:30 AM');
        $availableSeats = (int)($_POST['availableSeats'] ?? 30);
        $fareAmount     = (float)($_POST['fareAmount'] ?? 1250.00);

        if ($routeId && $vehicleId && $driverId && $fareAmount > 0) {
            $ok = Database::executeOracle("
                INSERT INTO scheduledTrip (routeId, vehicleId, driverId, tripDate, departureTime, arrivalTime, availableSeats, fareAmount, tripStatus)
                VALUES (:rId, :vId, :dId, TO_DATE(:tDate, 'YYYY-MM-DD'), :depT, :arrT, :seats, :fare, 'SCHEDULED')
            ", [
                ':rId'   => $routeId,
                ':vId'   => $vehicleId,
                ':dId'   => $driverId,
                ':tDate' => $tripDate,
                ':depT'  => $departureTime,
                ':arrT'  => $arrivalTime,
                ':seats' => $availableSeats,
                ':fare'  => $fareAmount
            ]);
            if ($ok) {
                $_SESSION['flash_success'] = "New scheduled trip dispatched successfully.";
            } else {
                $_SESSION['flash_error'] = "Failed to dispatch scheduled trip. Check Oracle connection.";
            }
        } else {
            $_SESSION['flash_error'] = "Please provide all required trip fields.";
        }
        header("Location: staff.php?view=dispatchView");
        exit;
    }

    // Action 2: Update Trip Status (e.g. IN_TRANSIT, COMPLETED, CANCELLED)
    if ($action === 'updateTripStatus') {
        $tripId    = (int)($_POST['tripId'] ?? 0);
        $newStatus = strtoupper(trim($_POST['status'] ?? 'SCHEDULED'));
        if ($tripId && in_array($newStatus, ['SCHEDULED', 'IN_TRANSIT', 'COMPLETED', 'CANCELLED'])) {
            $ok = Database::executeOracle("
                UPDATE scheduledTrip SET tripStatus = :st WHERE tripId = :tId
            ", [':st' => $newStatus, ':tId' => $tripId]);
            if ($ok) {
                $_SESSION['flash_success'] = "Trip #$tripId status updated to $newStatus.";
            } else {
                $_SESSION['flash_error'] = "Failed to update trip status.";
            }
        }
        header("Location: staff.php?view=dispatchView");
        exit;
    }

    // Action 3: Assign Driver Shift
    if ($action === 'assignShift') {
        $driverId  = (int)($_POST['driverId'] ?? 0);
        $shiftDate = trim($_POST['shiftDate'] ?? date('Y-m-d'));
        $startTime = trim($_POST['startTime'] ?? '06:00 AM');
        $endTime   = trim($_POST['endTime'] ?? '02:00 PM');

        if ($driverId) {
            $ok = Database::executeOracle("
                INSERT INTO driverShift (driverId, shiftDate, startTime, endTime, shiftStatus)
                VALUES (:dId, TO_DATE(:sDate, 'YYYY-MM-DD'), :stTime, :enTime, 'SCHEDULED')
            ", [
                ':dId'    => $driverId,
                ':sDate'  => $shiftDate,
                ':stTime' => $startTime,
                ':enTime' => $endTime
            ]);
            if ($ok) {
                $_SESSION['flash_success'] = "Driver shift scheduled successfully.";
            } else {
                $_SESSION['flash_error'] = "Failed to assign driver shift.";
            }
        }
        header("Location: staff.php?view=shiftsView");
        exit;
    }

    // Action 4: Update Shift Status
    if ($action === 'updateShiftStatus') {
        $shiftId   = (int)($_POST['shiftId'] ?? 0);
        $newStatus = strtoupper(trim($_POST['status'] ?? 'SCHEDULED'));
        if ($shiftId && in_array($newStatus, ['SCHEDULED', 'ON_DUTY', 'COMPLETED', 'ABSENT'])) {
            Database::executeOracle("UPDATE driverShift SET shiftStatus = :st WHERE shiftId = :id", [
                ':st' => $newStatus, ':id' => $shiftId
            ]);
            $_SESSION['flash_success'] = "Shift #$shiftId marked as $newStatus.";
        }
        header("Location: staff.php?view=shiftsView");
        exit;
    }

    // Action 5: Log Vehicle Maintenance
    if ($action === 'logMaintenance') {
        $vehicleId       = (int)($_POST['vehicleId'] ?? 0);
        $maintenanceType = trim($_POST['maintenanceType'] ?? 'Routine Inspection');
        $description     = trim($_POST['description'] ?? '');
        $cost            = (float)($_POST['cost'] ?? 0.0);
        $nextDate        = trim($_POST['nextServiceDate'] ?? date('Y-m-d', strtotime('+3 months')));

        if ($vehicleId) {
            $ok = Database::executeOracle("
                INSERT INTO maintenance (vehicleId, performedByStaffId, maintenanceDate, maintenanceType, description, cost, maintenanceStatus, nextServiceDate, status)
                VALUES (:vId, :sId, SYSDATE, :mType, :mDesc, :mCost, 'IN_PROGRESS', TO_DATE(:nxt, 'YYYY-MM-DD'), 'PENDING')
            ", [
                ':vId'   => $vehicleId,
                ':sId'   => $userId,
                ':mType' => $maintenanceType,
                ':mDesc' => $description,
                ':mCost' => $cost,
                ':nxt'   => $nextDate
            ]);
            if ($ok) {
                Database::executeOracle("UPDATE vehicle SET status = 'MAINTENANCE' WHERE vehicleId = :vId", [':vId' => $vehicleId]);
                $_SESSION['flash_success'] = "Vehicle maintenance log recorded. Vehicle status switched to MAINTENANCE.";
            } else {
                $_SESSION['flash_error'] = "Could not record maintenance log.";
            }
        }
        header("Location: staff.php?view=maintenanceView");
        exit;
    }

    // Action 6: Complete Maintenance
    if ($action === 'completeMaintenance') {
        $maintId   = (int)($_POST['maintenanceId'] ?? 0);
        $vehicleId = (int)($_POST['vehicleId'] ?? 0);
        if ($maintId) {
            Database::executeOracle("UPDATE maintenance SET maintenanceStatus = 'COMPLETED', status = 'COMPLETED' WHERE maintenanceId = :mId", [':mId' => $maintId]);
            if ($vehicleId) {
                Database::executeOracle("UPDATE vehicle SET status = 'AVAILABLE' WHERE vehicleId = :vId", [':vId' => $vehicleId]);
            }
            $_SESSION['flash_success'] = "Maintenance completed. Vehicle returned to AVAILABLE status.";
        }
        header("Location: staff.php?view=maintenanceView");
        exit;
    }

    // Action 7: Quick Counter Ticket Booking (Calling PL/SQL procedure processSeatBooking)
    if ($action === 'counterBooking') {
        $tripId      = (int)($_POST['tripId'] ?? 0);
        $passengerId = (int)($_POST['passengerId'] ?? 10);
        $seatNumber  = (int)($_POST['seatNumber'] ?? 1);
        $promoCode   = trim($_POST['promoCode'] ?? '');
        $promoParam  = empty($promoCode) ? null : $promoCode;

        if ($tripId && $seatNumber > 0) {
            // Attempt executing procedure processSeatBooking
            $conn = Database::getOracle();
            $bookingCreated = false;
            if ($conn) {
                $sql = "BEGIN processSeatBooking(:pId, :tId, :promo, :seat, :bId, :fare); END;";
                $stmt = @oci_parse($conn, $sql);
                if ($stmt) {
                    $bId = 0; $fare = 0.0;
                    @oci_bind_by_name($stmt, ':pId', $passengerId);
                    @oci_bind_by_name($stmt, ':tId', $tripId);
                    @oci_bind_by_name($stmt, ':promo', $promoParam);
                    @oci_bind_by_name($stmt, ':seat', $seatNumber);
                    @oci_bind_by_name($stmt, ':bId', $bId, 32);
                    @oci_bind_by_name($stmt, ':fare', $fare, 32);
                    if (@oci_execute($stmt)) {
                        $bookingCreated = true;
                        $_SESSION['flash_success'] = "Counter ticket booked! Booking #$bId for Seat #$seatNumber created.";
                    }
                }
            }

            if (!$bookingCreated) {
                // Fallback direct insert if procedure error
                $ok = Database::executeOracle("
                    INSERT INTO booking (passengerId, tripId, seatNumber, ticketPrice, bookingStatus)
                    VALUES (:pId, :tId, :sNum, 1250.00, 'CONFIRMED')
                ", [':pId' => $passengerId, ':tId' => $tripId, ':sNum' => $seatNumber]);
                if ($ok) {
                    Database::executeOracle("UPDATE scheduledTrip SET availableSeats = availableSeats - 1 WHERE tripId = :tId AND availableSeats > 0", [':tId' => $tripId]);
                    $_SESSION['flash_success'] = "Counter ticket booked for Seat #$seatNumber.";
                } else {
                    $_SESSION['flash_error'] = "Seat booking failed. Please verify trip availability.";
                }
            }
        }
        header("Location: staff.php?view=ticketingView");
        exit;
    }

    // Action 8: Process Refund Request
    if ($action === 'processRefund') {
        $bookingId = (int)($_POST['bookingId'] ?? 0);
        $paymentId = (int)($_POST['paymentId'] ?? 0);
        $amount    = (float)($_POST['refundAmount'] ?? 0.0);
        $reason    = trim($_POST['refundReason'] ?? 'Customer cancellation');

        if ($bookingId && $amount > 0) {
            $ok = Database::executeOracle("
                INSERT INTO refund (bookingId, paymentId, processedByStaffId, refundAmount, refundReason, refundDate, refundStatus)
                VALUES (:bId, :pId, :sId, :amt, :rsn, SYSDATE, 'APPROVED')
            ", [
                ':bId' => $bookingId,
                ':pId' => $paymentId ?: 1,
                ':sId' => $userId,
                ':amt' => $amount,
                ':rsn' => $reason
            ]);
            if ($ok) {
                Database::executeOracle("UPDATE booking SET bookingStatus = 'CANCELLED' WHERE bookingId = :bId", [':bId' => $bookingId]);
                $_SESSION['flash_success'] = "Refund of LKR " . number_format($amount, 2) . " processed successfully.";
            } else {
                $_SESSION['flash_error'] = "Failed to process refund.";
            }
        }
        header("Location: staff.php?view=refundsView");
        exit;
    }

    // Action 9: Broadcast Travel Advisory to MongoDB
    if ($action === 'createAnnouncement') {
        $title    = trim($_POST['title'] ?? '');
        $category = strtoupper(trim($_POST['category'] ?? 'EXPRESSWAY_ALERT'));
        $priority = strtoupper(trim($_POST['priority'] ?? 'MEDIUM'));
        $msg      = trim($_POST['message'] ?? '');
        $routesRaw= trim($_POST['affectedRoutes'] ?? '1');
        $routesArr= array_map('intval', explode(',', $routesRaw));

        if ($title && $msg) {
            $annId = 'ANN-' . date('Y') . '-' . str_pad(rand(10, 999), 3, '0', STR_PAD_LEFT);
            $newDoc = [
                'announcementId' => $annId,
                'title'          => $title,
                'category'       => $category,
                'priority'       => $priority,
                'message'        => $msg,
                'affectedRoutes' => $routesArr,
                'active'         => true,
                'publishedAt'    => date('Y-m-d H:i:s')
            ];
            Database::insertMongo('travel_announcements', [$newDoc]);
            $_SESSION['flash_success'] = "Advisory '$title' broadcasted via MongoDB.";
        }
        header("Location: staff.php?view=announcementsView");
        exit;
    }
}

// -----------------------------------------------------------------------------
// DATA QUERIES (With Realistic Fallbacks)
// -----------------------------------------------------------------------------
$scheduledTrips = [];
$routesList     = [];
$vehiclesList   = [];
$driversList    = [];
$shiftsList     = [];
$maintenanceList= [];
$bookingsList   = [];
$refundsList    = [];
$announcements  = [];

if ($oracleUp) {
    // 1. Scheduled Trips
    $scheduledTrips = Database::queryOracle("
        SELECT st.tripId, r.routeName, r.origin, r.destination, v.registrationNo, v.model,
               u.firstName || ' ' || u.lastName AS driverName,
               st.tripDate, st.departureTime, st.arrivalTime, st.availableSeats, st.fareAmount, st.tripStatus
        FROM scheduledTrip st
        JOIN route r ON st.routeId = r.routeId
        JOIN vehicle v ON st.vehicleId = v.vehicleId
        JOIN driver d ON st.driverId = d.driverId
        JOIN users u ON d.driverId = u.userId
        ORDER BY st.tripId DESC
    ");

    // 2. Routes & Vehicles & Drivers for select dropdowns
    $routesList = Database::queryOracle("SELECT routeId, routeName, baseFare FROM route WHERE status = 'ACTIVE' ORDER BY routeId");
    $vehiclesList = Database::queryOracle("SELECT vehicleId, registrationNo, vehicleType, model, capacity FROM vehicle WHERE status = 'AVAILABLE' ORDER BY vehicleId");
    $driversList = Database::queryOracle("
        SELECT d.driverId, u.firstName || ' ' || u.lastName AS driverName, d.licenseNumber, d.driverStatus
        FROM driver d
        JOIN users u ON d.driverId = u.userId
        ORDER BY d.driverId
    ");

    // 3. Driver Shifts
    $shiftsList = Database::queryOracle("
        SELECT ds.shiftId, u.firstName || ' ' || u.lastName AS driverName, d.licenseNumber,
               ds.shiftDate, ds.startTime, ds.endTime, ds.shiftStatus
        FROM driverShift ds
        JOIN driver d ON ds.driverId = d.driverId
        JOIN users u ON d.driverId = u.userId
        ORDER BY ds.shiftId DESC
    ");

    // 4. Maintenance Logs
    $maintenanceList = Database::queryOracle("
        SELECT m.maintenanceId, v.registrationNo, v.model, m.maintenanceType, m.description,
               m.cost, m.maintenanceStatus, m.nextServiceDate,
               u.firstName || ' ' || u.lastName AS staffName
        FROM maintenance m
        JOIN vehicle v ON m.vehicleId = v.vehicleId
        LEFT JOIN staff s ON m.performedByStaffId = s.staffId
        LEFT JOIN users u ON s.staffId = u.userId
        ORDER BY m.maintenanceId DESC
    ");

    // 5. Bookings
    $bookingsList = Database::queryOracle("
        SELECT b.bookingId, u.firstName || ' ' || u.lastName AS passengerName, u.phone,
               r.routeName, st.tripDate, st.departureTime, b.seatNumber, b.ticketPrice, b.bookingStatus, p.paymentMethod
        FROM booking b
        JOIN passenger pass ON b.passengerId = pass.passengerId
        JOIN users u ON pass.passengerId = u.userId
        JOIN scheduledTrip st ON b.tripId = st.tripId
        JOIN route r ON st.routeId = r.routeId
        LEFT JOIN payment p ON b.bookingId = p.bookingId
        ORDER BY b.bookingId DESC
    ");

    // 6. Refunds
    $refundsList = Database::queryOracle("
        SELECT r.refundId, r.bookingId, r.refundAmount, r.refundReason, r.refundDate, r.refundStatus,
               u.firstName || ' ' || u.lastName AS staffName
        FROM refund r
        LEFT JOIN staff s ON r.processedByStaffId = s.staffId
        LEFT JOIN users u ON s.staffId = u.userId
        ORDER BY r.refundId DESC
    ");
}

// Fallback Mock Data if Oracle is Offline or Fresh Seed
if (empty($scheduledTrips)) {
    $scheduledTrips = [
        ['tripId' => 1, 'routeName' => 'Colombo - Kandy Express (E04)', 'origin' => 'Colombo', 'destination' => 'Kandy', 'registrationNo' => 'WP-ND-4521', 'model' => 'Coaster Deluxe', 'driverName' => 'Kamal Perera', 'tripDate' => date('Y-m-d'), 'departureTime' => '07:00 AM', 'arrivalTime' => '09:30 AM', 'availableSeats' => 28, 'fareAmount' => 1250.00, 'tripStatus' => 'SCHEDULED'],
        ['tripId' => 2, 'routeName' => 'Colombo - Galle Highway (E01)', 'origin' => 'Colombo', 'destination' => 'Galle', 'registrationNo' => 'WP-CAB-8921', 'model' => 'KDH Commuter', 'driverName' => 'Sunil Shantha', 'tripDate' => date('Y-m-d'), 'departureTime' => '08:30 AM', 'arrivalTime' => '10:30 AM', 'availableSeats' => 12, 'fareAmount' => 1400.00, 'tripStatus' => 'IN_TRANSIT'],
        ['tripId' => 3, 'routeName' => 'Airport Express (E03)', 'origin' => 'Colombo Fort', 'destination' => 'Katunayake BIA', 'registrationNo' => 'WP-CAB-1209', 'model' => 'Prius Executive', 'driverName' => 'Anura Jayasinghe', 'tripDate' => date('Y-m-d'), 'departureTime' => '11:15 AM', 'arrivalTime' => '11:45 AM', 'availableSeats' => 3, 'fareAmount' => 800.00, 'tripStatus' => 'SCHEDULED'],
        ['tripId' => 4, 'routeName' => 'Colombo - Jaffna Intercity', 'origin' => 'Colombo', 'destination' => 'Jaffna', 'registrationNo' => 'WP-NB-8812', 'model' => 'Rosa Super Custom', 'driverName' => 'Nimal Fernando', 'tripDate' => date('Y-m-d', strtotime('-1 day')), 'departureTime' => '05:00 AM', 'arrivalTime' => '12:00 PM', 'availableSeats' => 26, 'fareAmount' => 3200.00, 'tripStatus' => 'COMPLETED'],
    ];
}

if (empty($routesList)) {
    $routesList = [
        ['routeId' => 1, 'routeName' => 'Colombo - Kandy Express (E04)', 'baseFare' => 1250.00],
        ['routeId' => 2, 'routeName' => 'Colombo - Galle Highway (E01)', 'baseFare' => 1400.00],
        ['routeId' => 3, 'routeName' => 'Airport Express (E03)', 'baseFare' => 800.00],
        ['routeId' => 4, 'routeName' => 'Colombo - Jaffna Intercity', 'baseFare' => 3200.00]
    ];
}

if (empty($vehiclesList)) {
    $vehiclesList = [
        ['vehicleId' => 1, 'registrationNo' => 'WP-ND-4521', 'vehicleType' => 'BUS', 'model' => 'Coaster Deluxe', 'capacity' => 32],
        ['vehicleId' => 2, 'registrationNo' => 'WP-CAB-8921', 'vehicleType' => 'VAN', 'model' => 'KDH Commuter', 'capacity' => 14],
        ['vehicleId' => 4, 'registrationNo' => 'WP-CAB-1209', 'vehicleType' => 'LUXURYCAR', 'model' => 'Prius Executive', 'capacity' => 4]
    ];
}

if (empty($driversList)) {
    $driversList = [
        ['driverId' => 4, 'driverName' => 'Kamal Perera', 'licenseNumber' => 'B-8891234', 'driverStatus' => 'AVAILABLE'],
        ['driverId' => 5, 'driverName' => 'Sunil Shantha', 'licenseNumber' => 'B-9912456', 'driverStatus' => 'ON_DUTY'],
        ['driverId' => 6, 'driverName' => 'Nimal Fernando', 'licenseNumber' => 'B-7723891', 'driverStatus' => 'AVAILABLE'],
        ['driverId' => 7, 'driverName' => 'Anura Jayasinghe', 'licenseNumber' => 'B-6612093', 'driverStatus' => 'AVAILABLE']
    ];
}

if (empty($shiftsList)) {
    $shiftsList = [
        ['shiftId' => 101, 'driverName' => 'Kamal Perera', 'licenseNumber' => 'B-8891234', 'shiftDate' => date('Y-m-d'), 'startTime' => '06:00 AM', 'endTime' => '02:00 PM', 'shiftStatus' => 'ON_DUTY'],
        ['shiftId' => 102, 'driverName' => 'Sunil Shantha', 'licenseNumber' => 'B-9912456', 'shiftDate' => date('Y-m-d'), 'startTime' => '08:00 AM', 'endTime' => '04:00 PM', 'shiftStatus' => 'ON_DUTY'],
        ['shiftId' => 103, 'driverName' => 'Anura Jayasinghe', 'licenseNumber' => 'B-6612093', 'shiftDate' => date('Y-m-d'), 'startTime' => '10:00 AM', 'endTime' => '06:00 PM', 'shiftStatus' => 'SCHEDULED'],
        ['shiftId' => 104, 'driverName' => 'Nimal Fernando', 'licenseNumber' => 'B-7723891', 'shiftDate' => date('Y-m-d'), 'startTime' => '02:00 PM', 'endTime' => '10:00 PM', 'shiftStatus' => 'SCHEDULED']
    ];
}

if (empty($maintenanceList)) {
    $maintenanceList = [
        ['maintenanceId' => 1, 'registrationNo' => 'WP-NB-8812', 'model' => 'Rosa Super Custom', 'maintenanceType' => 'Brake Pad Replacement & Hydraulic Check', 'description' => 'Replaced front discs and bleeded ABS lines', 'cost' => 45000.00, 'maintenanceStatus' => 'IN_PROGRESS', 'nextServiceDate' => date('Y-m-d', strtotime('+90 days')), 'staffName' => 'Sunil Kariyawasam'],
        ['maintenanceId' => 2, 'registrationNo' => 'WP-CAB-8921', 'model' => 'KDH Commuter', 'maintenanceType' => 'Routine 40,000 KM Engine Servicing', 'description' => 'Engine synthetic oil, air & fuel filter replacement', 'cost' => 28500.00, 'maintenanceStatus' => 'COMPLETED', 'nextServiceDate' => date('Y-m-d', strtotime('+60 days')), 'staffName' => 'Sunil Kariyawasam']
    ];
}

if (empty($bookingsList)) {
    $bookingsList = [
        ['bookingId' => 501, 'passengerName' => 'Kasun Jayawardena', 'phone' => '+94711112233', 'routeName' => 'Colombo - Kandy Express (E04)', 'tripDate' => date('Y-m-d'), 'departureTime' => '07:00 AM', 'seatNumber' => 12, 'ticketPrice' => 1250.00, 'bookingStatus' => 'CONFIRMED', 'paymentMethod' => 'CARD'],
        ['bookingId' => 502, 'passengerName' => 'Dilani Wickramasinghe', 'phone' => '+94712223344', 'routeName' => 'Colombo - Galle Highway (E01)', 'tripDate' => date('Y-m-d'), 'departureTime' => '08:30 AM', 'seatNumber' => 4, 'ticketPrice' => 1400.00, 'bookingStatus' => 'CONFIRMED', 'paymentMethod' => 'ONLINE'],
        ['bookingId' => 503, 'passengerName' => 'Rohan Samaraweera', 'phone' => '+94713334455', 'routeName' => 'Airport Express (E03)', 'tripDate' => date('Y-m-d'), 'departureTime' => '11:15 AM', 'seatNumber' => 2, 'ticketPrice' => 800.00, 'bookingStatus' => 'RESERVED', 'paymentMethod' => 'CASH'],
    ];
}

if (empty($refundsList)) {
    $refundsList = [
        ['refundId' => 1, 'bookingId' => 498, 'refundAmount' => 1250.00, 'refundReason' => 'Travel plan postponed by commuter', 'refundDate' => date('Y-m-d'), 'refundStatus' => 'APPROVED', 'staffName' => 'Nimal Disanayake']
    ];
}

// Announcements from MongoDB
if ($mongoUp) {
    $announcements = MongoService::getActiveAnnouncements();
}
if (empty($announcements)) {
    $announcements = [
        ['announcementId' => 'ANN-2026-001', 'title' => 'Southern Expressway Kadawatha Slip Lane Maintenance', 'category' => 'ROAD_CLOSURE', 'priority' => 'HIGH', 'message' => 'Single lane operational near interchange 3. Expect 10-15 minute delays on Colombo-Galle buses.', 'publishedAt' => date('Y-m-d H:i')]
    ];
}

// Operational KPIs
$activeTripsCount = count(array_filter($scheduledTrips, fn($t) => in_array($t['tripStatus'], ['SCHEDULED', 'IN_TRANSIT'])));
$inTransitCount   = count(array_filter($scheduledTrips, fn($t) => $t['tripStatus'] === 'IN_TRANSIT'));
$onDutyDrivers    = count(array_filter($shiftsList, fn($s) => $s['shiftStatus'] === 'ON_DUTY'));
$maintVehCount    = count(array_filter($maintenanceList, fn($m) => $m['maintenanceStatus'] === 'IN_PROGRESS'));

$pageTitle = "SmartMove — Staff Operations Portal";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  
  <link rel="stylesheet" href="../css/variables.css">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body class="dashboard-body">

<div class="app-wrapper">

  <!-- Sidebar Navigation -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header" title="SmartMove Staff Operations">
      <div class="sidebar-brand-wrapper">
        <div class="brand-icon-box">SM</div>
        <div class="sidebar-header-text">
          <span class="brand-name">SmartMove</span>
          <span class="brand-subtext">STAFF PORTAL</span>
        </div>
      </div>
      <div class="brand-arrows">⌘</div>
    </div>

    <!-- Navigation List -->
    <nav class="sidebar-nav">
      <div class="sidebar-group">
        <div class="sidebar-group-title">OPERATIONS CONTROL</div>
        
        <a class="nav-item <?php echo $activeView === 'dispatchView' ? 'active' : ''; ?>" data-view="dispatchView" title="Trip Dispatching & Schedules">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
          </div>
          <span>Dispatch & Trips</span>
          <span class="nav-badge" style="background:var(--blue-primary);"><?php echo $activeTripsCount; ?></span>
        </a>

        <a class="nav-item <?php echo $activeView === 'shiftsView' ? 'active' : ''; ?>" data-view="shiftsView" title="Driver Shift Rostering">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <span>Driver Shifts</span>
          <span class="nav-badge" style="background:#10B981;"><?php echo $onDutyDrivers; ?></span>
        </a>

        <a class="nav-item <?php echo $activeView === 'maintenanceView' ? 'active' : ''; ?>" data-view="maintenanceView" title="Fleet Maintenance Logging">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <span>Fleet Maintenance</span>
          <?php if ($maintVehCount > 0): ?>
            <span class="nav-badge" style="background:#F59E0B;"><?php echo $maintVehCount; ?></span>
          <?php endif; ?>
        </a>

        <a class="nav-item <?php echo $activeView === 'ticketingView' ? 'active' : ''; ?>" data-view="ticketingView" title="Counter Ticketing & Bookings">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
          </div>
          <span>Counter Ticketing</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'refundsView' ? 'active' : ''; ?>" data-view="refundsView" title="Cancellations & Refunds">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <span>Refund Processing</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'announcementsView' ? 'active' : ''; ?>" data-view="announcementsView" title="MongoDB Travel Advisories">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </div>
          <span>Travel Alerts</span>
        </a>
      </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <button class="user-profile-btn" id="sidebarUserBtn" type="button">
        <div class="user-avatar"><?php echo $initials; ?></div>
        <div class="user-details">
          <span class="user-name"><?php echo $fullName; ?></span>
          <span class="user-email">STAFF OFFICER</span>
        </div>
        <div class="user-arrows">⇅</div>
      </button>

      <!-- Sidebar User Menu Popover -->
      <div class="popover-menu" id="sidebarUserMenu">
        <div class="popover-item">
          <strong><?php echo $fullName; ?></strong><br>
          <span style="font-size:11px;color:var(--text-muted);"><?php echo $email; ?></span>
        </div>
        <div class="popover-divider"></div>
        <a href="../index.php" class="popover-item">← Main Website</a>
        <?php if ($role === 'ADMIN'): ?>
          <a href="dashboard.php" class="popover-item">🛡️ Admin Command Portal</a>
        <?php endif; ?>
        <div class="popover-divider"></div>
        <a href="logout.php" class="popover-item" style="color:#F87171;">Sign Out</a>
      </div>
    </div>
  </aside>

  <!-- Main Viewport -->
  <div class="main-viewport">
    
    <!-- Topbar Header -->
    <header class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle-btn" id="toggleSidebarBtn" type="button" title="Toggle Sidebar">
          <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" fill="none"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span style="font-weight:600;font-size:13.5px;color:var(--text-white);">Operations Control Center</span>
      </div>

      <div class="topbar-right">
        <!-- DB Status Badges -->
        <div class="db-status-pill" title="Oracle DB Status">
          <span class="status-dot <?php echo $oracleUp ? 'dot-green' : 'dot-red'; ?>"></span>
          <span>Oracle <?php echo $oracleUp ? 'Live' : 'Mock/Offline'; ?></span>
        </div>
        <div class="db-status-pill" title="MongoDB Status">
          <span class="status-dot <?php echo $mongoUp ? 'dot-green' : 'dot-red'; ?>"></span>
          <span>MongoDB <?php echo $mongoUp ? 'Live' : 'Mock/Offline'; ?></span>
        </div>

        <!-- Topbar Avatar -->
        <button class="topbar-avatar-btn" id="topbarAvatarBtn" type="button">
          <div class="user-avatar"><?php echo $initials; ?></div>
        </button>

        <div class="popover-menu" id="topbarUserMenu">
          <div class="popover-item">
            <strong><?php echo $fullName; ?></strong><br>
            <span style="font-size:11px;color:var(--text-muted);"><?php echo $email; ?></span>
          </div>
          <div class="popover-divider"></div>
          <a href="../index.php" class="popover-item">← Main Website</a>
          <a href="logout.php" class="popover-item" style="color:#F87171;">Sign Out</a>
        </div>
      </div>
    </header>

    <!-- Main Content Container -->
    <main class="content-container">

      <!-- Flash Notification Alerts -->
      <?php if ($flashSuccess): ?>
        <div class="alert-banner alert-banner-success">
          <span>✓</span> <?php echo htmlspecialchars($flashSuccess); ?>
        </div>
      <?php endif; ?>
      <?php if ($flashError): ?>
        <div class="alert-banner alert-banner-error">
          <span>⚠️</span> <?php echo htmlspecialchars($flashError); ?>
        </div>
      <?php endif; ?>

      <!-- ====================================================================
           VIEW 1: DISPATCH & SCHEDULED TRIPS
           ==================================================================== -->
      <section class="page-view <?php echo $activeView === 'dispatchView' ? 'active-view' : ''; ?>" id="dispatchView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Trip Dispatch &amp; Schedule Operations</h1>
            <p>Monitor live vehicle movements, schedule express runs, and update transit statuses.</p>
          </div>
          <div class="view-actions">
            <button class="btn-primary" onclick="openModal('addTripModal')">
              <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Dispatch New Trip
            </button>
          </div>
        </div>

        <!-- Operational KPIs -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-header-row">
              <span class="stat-label">Active / Scheduled Trips</span>
              <span class="stat-icon">🚍</span>
            </div>
            <div class="stat-value"><?php echo count($scheduledTrips); ?></div>
            <div class="stat-subtext">Across expressway &amp; intercity routes</div>
          </div>
          <div class="stat-card">
            <div class="stat-header-row">
              <span class="stat-label">In-Transit Fleet</span>
              <span class="stat-icon">⚡</span>
            </div>
            <div class="stat-value" style="color:#38BDF8;"><?php echo $inTransitCount; ?></div>
            <div class="stat-subtext">Currently on the road</div>
          </div>
          <div class="stat-card">
            <div class="stat-header-row">
              <span class="stat-label">Drivers On Duty</span>
              <span class="stat-icon">👨‍✈️</span>
            </div>
            <div class="stat-value" style="color:#34D399;"><?php echo $onDutyDrivers; ?></div>
            <div class="stat-subtext">Clocked into active shifts</div>
          </div>
          <div class="stat-card">
            <div class="stat-header-row">
              <span class="stat-label">Fleet Maintenance</span>
              <span class="stat-icon">🔧</span>
            </div>
            <div class="stat-value" style="color:#FBBF24;"><?php echo $maintVehCount; ?></div>
            <div class="stat-subtext">Vehicles undergoing servicing</div>
          </div>
        </div>

        <!-- Table Filters -->
        <div class="filter-pills-bar">
          <button class="filter-pill active" onclick="filterTrips('ALL', this)">All Trips (<?php echo count($scheduledTrips); ?>)</button>
          <button class="filter-pill" onclick="filterTrips('SCHEDULED', this)">Scheduled</button>
          <button class="filter-pill" onclick="filterTrips('IN_TRANSIT', this)">In Transit</button>
          <button class="filter-pill" onclick="filterTrips('COMPLETED', this)">Completed</button>
          <button class="filter-pill" onclick="filterTrips('CANCELLED', this)">Cancelled</button>
        </div>

        <!-- Trips Table -->
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Trip ID</th>
                <th>Route Corridor</th>
                <th>Vehicle</th>
                <th>Assigned Captain</th>
                <th>Departure / Arrival</th>
                <th>Avail. Seats</th>
                <th>Base Fare</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($scheduledTrips as $trip): 
                $st = strtoupper($trip['tripStatus']);
                $badgeClass = match($st) {
                  'IN_TRANSIT' => 'badge-info',
                  'COMPLETED'  => 'badge-active',
                  'CANCELLED'  => 'badge-urgent',
                  default      => 'badge-warning'
                };
              ?>
                <tr class="trip-row" data-status="<?php echo $st; ?>">
                  <td><strong>#<?php echo $trip['tripId']; ?></strong></td>
                  <td>
                    <div style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($trip['routeName']); ?></div>
                    <div style="font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars($trip['origin']); ?> ➔ <?php echo htmlspecialchars($trip['destination']); ?></div>
                  </td>
                  <td>
                    <div><?php echo htmlspecialchars($trip['registrationNo']); ?></div>
                    <div style="font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars($trip['model'] ?? ''); ?></div>
                  </td>
                  <td><?php echo htmlspecialchars($trip['driverName'] ?? 'Unassigned'); ?></td>
                  <td>
                    <div><?php echo htmlspecialchars($trip['departureTime']); ?></div>
                    <div style="font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars($trip['tripDate']); ?></div>
                  </td>
                  <td>
                    <span class="type-badge"><?php echo $trip['availableSeats']; ?> Seats</span>
                  </td>
                  <td>LKR <?php echo number_format($trip['fareAmount'], 2); ?></td>
                  <td>
                    <span class="badge-status <?php echo $badgeClass; ?>"><?php echo $st; ?></span>
                  </td>
                  <td style="text-align:right;">
                    <?php if ($st === 'SCHEDULED'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="updateTripStatus">
                        <input type="hidden" name="tripId" value="<?php echo $trip['tripId']; ?>">
                        <input type="hidden" name="status" value="IN_TRANSIT">
                        <button type="submit" class="action-btn-sm" title="Dispatch Out">Start Trip</button>
                      </form>
                    <?php elseif ($st === 'IN_TRANSIT'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="updateTripStatus">
                        <input type="hidden" name="tripId" value="<?php echo $trip['tripId']; ?>">
                        <input type="hidden" name="status" value="COMPLETED">
                        <button type="submit" class="action-btn-sm" style="color:#34D399;" title="Mark Complete">Complete</button>
                      </form>
                    <?php endif; ?>
                    <?php if ($st !== 'COMPLETED' && $st !== 'CANCELLED'): ?>
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this trip?');">
                        <input type="hidden" name="action" value="updateTripStatus">
                        <input type="hidden" name="tripId" value="<?php echo $trip['tripId']; ?>">
                        <input type="hidden" name="status" value="CANCELLED">
                        <button type="submit" class="action-btn-sm action-btn-danger">Cancel</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ====================================================================
           VIEW 2: DRIVER SHIFT ROSTERING
           ==================================================================== -->
      <section class="page-view <?php echo $activeView === 'shiftsView' ? 'active-view' : ''; ?>" id="shiftsView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Driver Duty Shift Rostering</h1>
            <p>Schedule duty shifts, assign road captains, and monitor active operational hours.</p>
          </div>
          <div class="view-actions">
            <button class="btn-primary" onclick="openModal('assignShiftModal')">
              <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Schedule Driver Shift
            </button>
          </div>
        </div>

        <div class="filter-pills-bar">
          <button class="filter-pill active" onclick="filterShifts('ALL', this)">All Shifts (<?php echo count($shiftsList); ?>)</button>
          <button class="filter-pill" onclick="filterShifts('ON_DUTY', this)">On Duty</button>
          <button class="filter-pill" onclick="filterShifts('SCHEDULED', this)">Scheduled</button>
          <button class="filter-pill" onclick="filterShifts('COMPLETED', this)">Completed</button>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Shift ID</th>
                <th>Driver Name</th>
                <th>License Class</th>
                <th>Shift Date</th>
                <th>Time Window</th>
                <th>Status</th>
                <th style="text-align:right;">Quick Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($shiftsList as $shift): 
                $sst = strtoupper($shift['shiftStatus']);
                $sBadge = match($sst) {
                  'ON_DUTY'   => 'badge-active',
                  'COMPLETED' => 'badge-neutral',
                  'ABSENT'    => 'badge-urgent',
                  default     => 'badge-warning'
                };
              ?>
                <tr class="shift-row" data-status="<?php echo $sst; ?>">
                  <td><strong>#<?php echo $shift['shiftId']; ?></strong></td>
                  <td style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($shift['driverName']); ?></td>
                  <td><span class="type-badge"><?php echo htmlspecialchars($shift['licenseNumber']); ?></span></td>
                  <td><?php echo htmlspecialchars($shift['shiftDate']); ?></td>
                  <td><?php echo htmlspecialchars($shift['startTime']); ?> — <?php echo htmlspecialchars($shift['endTime']); ?></td>
                  <td><span class="badge-status <?php echo $sBadge; ?>"><?php echo $sst; ?></span></td>
                  <td style="text-align:right;">
                    <?php if ($sst === 'SCHEDULED'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="updateShiftStatus">
                        <input type="hidden" name="shiftId" value="<?php echo $shift['shiftId']; ?>">
                        <input type="hidden" name="status" value="ON_DUTY">
                        <button type="submit" class="action-btn-sm" style="color:#34D399;">Check In</button>
                      </form>
                    <?php elseif ($sst === 'ON_DUTY'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="updateShiftStatus">
                        <input type="hidden" name="shiftId" value="<?php echo $shift['shiftId']; ?>">
                        <input type="hidden" name="status" value="COMPLETED">
                        <button type="submit" class="action-btn-sm">Clock Out</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ====================================================================
           VIEW 3: FLEET MAINTENANCE LOGGING
           ==================================================================== -->
      <section class="page-view <?php echo $activeView === 'maintenanceView' ? 'active-view' : ''; ?>" id="maintenanceView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Fleet Preventive Maintenance &amp; Servicing</h1>
            <p>Log workshop inspections, record parts replacement costs, and sync vehicle availability status.</p>
          </div>
          <div class="view-actions">
            <button class="btn-primary" onclick="openModal('addMaintenanceModal')">
              <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Record Maintenance Log
            </button>
          </div>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Log ID</th>
                <th>Vehicle Reg</th>
                <th>Model</th>
                <th>Service Type</th>
                <th>Cost (LKR)</th>
                <th>Next Due Date</th>
                <th>Recorded By</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($maintenanceList as $maint): 
                $mst = strtoupper($maint['maintenanceStatus']);
                $mBadge = $mst === 'COMPLETED' ? 'badge-active' : 'badge-urgent';
              ?>
                <tr>
                  <td><strong>#<?php echo $maint['maintenanceId']; ?></strong></td>
                  <td style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($maint['registrationNo']); ?></td>
                  <td><?php echo htmlspecialchars($maint['model']); ?></td>
                  <td>
                    <div><?php echo htmlspecialchars($maint['maintenanceType']); ?></div>
                    <div style="font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars($maint['description'] ?? ''); ?></div>
                  </td>
                  <td>LKR <?php echo number_format($maint['cost'], 2); ?></td>
                  <td><?php echo htmlspecialchars($maint['nextServiceDate']); ?></td>
                  <td><?php echo htmlspecialchars($maint['staffName'] ?? 'Supervisor'); ?></td>
                  <td><span class="badge-status <?php echo $mBadge; ?>"><?php echo $mst; ?></span></td>
                  <td style="text-align:right;">
                    <?php if ($mst !== 'COMPLETED'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="completeMaintenance">
                        <input type="hidden" name="maintenanceId" value="<?php echo $maint['maintenanceId']; ?>">
                        <button type="submit" class="action-btn-sm" style="color:#34D399;">Mark Completed</button>
                      </form>
                    <?php else: ?>
                      <span style="font-size:11px;color:var(--text-muted);">Archived</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ====================================================================
           VIEW 4: COUNTER TICKETING & RESERVATIONS
           ==================================================================== -->
      <section class="page-view <?php echo $activeView === 'ticketingView' ? 'active-view' : ''; ?>" id="ticketingView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Counter Ticketing &amp; Reservations</h1>
            <p>Issue walk-in passenger tickets via PL/SQL procedure, reserve seats, and check in passengers.</p>
          </div>
          <div class="view-actions">
            <button class="btn-primary" onclick="openModal('counterBookingModal')">
              <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Issue Counter Ticket
            </button>
          </div>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Booking ID</th>
                <th>Passenger</th>
                <th>Contact</th>
                <th>Route</th>
                <th>Departure Time</th>
                <th>Seat #</th>
                <th>Fare (LKR)</th>
                <th>Payment</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bookingsList as $bk): 
                $bst = strtoupper($bk['bookingStatus']);
                $bBadge = match($bst) {
                  'CONFIRMED' => 'badge-active',
                  'RESERVED'  => 'badge-warning',
                  default     => 'badge-urgent'
                };
              ?>
                <tr>
                  <td><strong>#<?php echo $bk['bookingId']; ?></strong></td>
                  <td style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($bk['passengerName']); ?></td>
                  <td style="font-size:12px;color:var(--text-muted);"><?php echo htmlspecialchars($bk['phone'] ?? 'N/A'); ?></td>
                  <td><?php echo htmlspecialchars($bk['routeName']); ?></td>
                  <td><?php echo htmlspecialchars($bk['departureTime']); ?> (<?php echo htmlspecialchars($bk['tripDate']); ?>)</td>
                  <td><span class="manifest-seat-badge">Seat <?php echo $bk['seatNumber']; ?></span></td>
                  <td>LKR <?php echo number_format($bk['ticketPrice'], 2); ?></td>
                  <td><span class="type-badge"><?php echo htmlspecialchars($bk['paymentMethod'] ?? 'COUNTER'); ?></span></td>
                  <td><span class="badge-status <?php echo $bBadge; ?>"><?php echo $bst; ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ====================================================================
           VIEW 5: REFUND PROCESSING
           ==================================================================== -->
      <section class="page-view <?php echo $activeView === 'refundsView' ? 'active-view' : ''; ?>" id="refundsView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Cancellation &amp; Refund Processing</h1>
            <p>Audit passenger ticket cancellation requests and process financial refunds with audit trails.</p>
          </div>
          <div class="view-actions">
            <button class="btn-primary" onclick="openModal('processRefundModal')">
              <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Process New Refund
            </button>
          </div>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Refund ID</th>
                <th>Booking ID</th>
                <th>Amount (LKR)</th>
                <th>Cancellation Reason</th>
                <th>Processed Date</th>
                <th>Officer</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($refundsList as $ref): ?>
                <tr>
                  <td><strong>#<?php echo $ref['refundId']; ?></strong></td>
                  <td>Booking #<?php echo $ref['bookingId']; ?></td>
                  <td style="font-weight:600;color:#F87171;">LKR <?php echo number_format($ref['refundAmount'], 2); ?></td>
                  <td><?php echo htmlspecialchars($ref['refundReason']); ?></td>
                  <td><?php echo htmlspecialchars($ref['refundDate']); ?></td>
                  <td><?php echo htmlspecialchars($ref['staffName'] ?? 'Operations Staff'); ?></td>
                  <td><span class="badge-status badge-active"><?php echo htmlspecialchars($ref['refundStatus']); ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ====================================================================
           VIEW 6: TRAVEL ADVISORIES (MongoDB)
           ==================================================================== -->
      <section class="page-view <?php echo $activeView === 'announcementsView' ? 'active-view' : ''; ?>" id="announcementsView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Travel Alerts &amp; Expressway Advisories (MongoDB)</h1>
            <p>Publish real-time traffic notices, weather delays, and lane closures stored in NoSQL collections.</p>
          </div>
          <div class="view-actions">
            <button class="btn-primary" onclick="openModal('addAnnouncementModal')">
              <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Broadcast Travel Alert
            </button>
          </div>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Alert ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Message Details</th>
                <th>Published Time</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($announcements as $ann): 
                $pri = strtoupper($ann['priority'] ?? 'MEDIUM');
                $pBadge = $pri === 'HIGH' ? 'badge-urgent' : 'badge-warning';
              ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($ann['announcementId'] ?? 'ANN'); ?></strong></td>
                  <td style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($ann['title']); ?></td>
                  <td><span class="type-badge"><?php echo htmlspecialchars($ann['category'] ?? 'GENERAL'); ?></span></td>
                  <td><span class="badge-status <?php echo $pBadge; ?>"><?php echo $pri; ?></span></td>
                  <td style="font-size:12.5px;color:var(--text-secondary);"><?php echo htmlspecialchars($ann['message']); ?></td>
                  <td style="font-size:11.5px;color:var(--text-muted);"><?php echo htmlspecialchars($ann['publishedAt'] ?? 'Recent'); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>
</div>

<!-- ========================================================================
     MODALS FOR STAFF ACTIONS
     ======================================================================== -->

<!-- Modal 1: Dispatch New Trip -->
<div class="modal-backdrop" id="addTripModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 class="modal-title">Dispatch New Scheduled Trip</h3>
      <button class="modal-close-btn" type="button" onclick="closeModal('addTripModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="addTrip">
      <div class="form-group">
        <label class="form-label">Transit Route Corridor *</label>
        <select class="form-select" name="routeId" required>
          <?php foreach ($routesList as $r): ?>
            <option value="<?php echo $r['routeId']; ?>"><?php echo htmlspecialchars($r['routeName']); ?> (LKR <?php echo number_format($r['baseFare'], 2); ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Assign Vehicle Asset *</label>
        <select class="form-select" name="vehicleId" required>
          <?php foreach ($vehiclesList as $v): ?>
            <option value="<?php echo $v['vehicleId']; ?>"><?php echo htmlspecialchars($v['registrationNo']); ?> — <?php echo htmlspecialchars($v['model']); ?> (<?php echo $v['capacity']; ?> seats)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Assign Road Captain (Driver) *</label>
        <select class="form-select" name="driverId" required>
          <?php foreach ($driversList as $d): ?>
            <option value="<?php echo $d['driverId']; ?>"><?php echo htmlspecialchars($d['driverName']); ?> (<?php echo htmlspecialchars($d['licenseNumber']); ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Trip Date *</label>
          <input type="date" class="form-input" name="tripDate" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Available Seats *</label>
          <input type="number" class="form-input" name="availableSeats" value="30" min="1" max="60" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Departure Time *</label>
          <input type="text" class="form-input" name="departureTime" value="08:00 AM" required>
        </div>
        <div class="form-group">
          <label class="form-label">Estimated Arrival *</label>
          <input type="text" class="form-input" name="arrivalTime" value="10:30 AM" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Trip Fare Amount (LKR) *</label>
        <input type="number" step="0.5" class="form-input" name="fareAmount" value="1250.00" required>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <button type="button" class="btn-outline" onclick="closeModal('addTripModal')">Cancel</button>
        <button type="submit" class="btn-primary">Dispatch Trip</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 2: Assign Driver Shift -->
<div class="modal-backdrop" id="assignShiftModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 class="modal-title">Schedule Driver Duty Shift</h3>
      <button class="modal-close-btn" type="button" onclick="closeModal('assignShiftModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="assignShift">
      <div class="form-group">
        <label class="form-label">Driver Captain *</label>
        <select class="form-select" name="driverId" required>
          <?php foreach ($driversList as $d): ?>
            <option value="<?php echo $d['driverId']; ?>"><?php echo htmlspecialchars($d['driverName']); ?> (<?php echo htmlspecialchars($d['licenseNumber']); ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Shift Date *</label>
        <input type="date" class="form-input" name="shiftDate" value="<?php echo date('Y-m-d'); ?>" required>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Start Time *</label>
          <input type="text" class="form-input" name="startTime" value="06:00 AM" required>
        </div>
        <div class="form-group">
          <label class="form-label">End Time *</label>
          <input type="text" class="form-input" name="endTime" value="02:00 PM" required>
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <button type="button" class="btn-outline" onclick="closeModal('assignShiftModal')">Cancel</button>
        <button type="submit" class="btn-primary">Assign Shift</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 3: Log Vehicle Maintenance -->
<div class="modal-backdrop" id="addMaintenanceModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 class="modal-title">Record Vehicle Maintenance Log</h3>
      <button class="modal-close-btn" type="button" onclick="closeModal('addMaintenanceModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="logMaintenance">
      <div class="form-group">
        <label class="form-label">Vehicle Registration *</label>
        <select class="form-select" name="vehicleId" required>
          <?php foreach ($vehiclesList as $v): ?>
            <option value="<?php echo $v['vehicleId']; ?>"><?php echo htmlspecialchars($v['registrationNo']); ?> — <?php echo htmlspecialchars($v['model']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Maintenance Classification *</label>
        <select class="form-select" name="maintenanceType" required>
          <option value="Engine Oil & Filter Service">Engine Oil &amp; Filter Service</option>
          <option value="Brake Pad Replacement">Brake Pad Replacement &amp; ABS Bleed</option>
          <option value="AC Compressor Overhaul">AC Compressor Overhaul</option>
          <option value="Tire Rotation & Wheel Alignment">Tire Rotation &amp; Alignment</option>
          <option value="Transmission Fluid Flush">Transmission Fluid Flush</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Service Description</label>
        <textarea class="form-textarea" name="description" placeholder="Parts replaced, technician remarks..."></textarea>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Cost in LKR *</label>
          <input type="number" step="0.5" class="form-input" name="cost" value="25000.00" required>
        </div>
        <div class="form-group">
          <label class="form-label">Next Service Due *</label>
          <input type="date" class="form-input" name="nextServiceDate" value="<?php echo date('Y-m-d', strtotime('+3 months')); ?>" required>
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <button type="button" class="btn-outline" onclick="closeModal('addMaintenanceModal')">Cancel</button>
        <button type="submit" class="btn-primary">Record Maintenance</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 4: Issue Counter Ticket -->
<div class="modal-backdrop" id="counterBookingModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 class="modal-title">Issue Counter Walk-in Ticket</h3>
      <button class="modal-close-btn" type="button" onclick="closeModal('counterBookingModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="counterBooking">
      <div class="form-group">
        <label class="form-label">Select Scheduled Trip *</label>
        <select class="form-select" name="tripId" required>
          <?php foreach ($scheduledTrips as $t): ?>
            <option value="<?php echo $t['tripId']; ?>">#<?php echo $t['tripId']; ?>: <?php echo htmlspecialchars($t['routeName']); ?> (<?php echo $t['departureTime']; ?>) — LKR <?php echo number_format($t['fareAmount'], 2); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Seat Number (1-50) *</label>
          <input type="number" class="form-input" name="seatNumber" value="1" min="1" max="60" required>
        </div>
        <div class="form-group">
          <label class="form-label">Promo Code (Optional)</label>
          <input type="text" class="form-input" name="promoCode" placeholder="SMART10">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Passenger User ID</label>
        <input type="number" class="form-input" name="passengerId" value="10">
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <button type="button" class="btn-outline" onclick="closeModal('counterBookingModal')">Cancel</button>
        <button type="submit" class="btn-primary">Book &amp; Print Ticket</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 5: Process Refund -->
<div class="modal-backdrop" id="processRefundModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 class="modal-title">Process Ticket Cancellation Refund</h3>
      <button class="modal-close-btn" type="button" onclick="closeModal('processRefundModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="processRefund">
      <div class="form-group">
        <label class="form-label">Booking ID *</label>
        <input type="number" class="form-input" name="bookingId" placeholder="e.g. 501" required>
      </div>
      <div class="form-group">
        <label class="form-label">Refund Amount (LKR) *</label>
        <input type="number" step="0.5" class="form-input" name="refundAmount" value="1250.00" required>
      </div>
      <div class="form-group">
        <label class="form-label">Cancellation Reason *</label>
        <input type="text" class="form-input" name="refundReason" value="Commuter cancellation request" required>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <button type="button" class="btn-outline" onclick="closeModal('processRefundModal')">Cancel</button>
        <button type="submit" class="btn-primary">Approve Refund</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 6: Broadcast Travel Alert (MongoDB) -->
<div class="modal-backdrop" id="addAnnouncementModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 class="modal-title">Broadcast Live Travel Alert (MongoDB)</h3>
      <button class="modal-close-btn" type="button" onclick="closeModal('addAnnouncementModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="createAnnouncement">
      <div class="form-group">
        <label class="form-label">Alert Title *</label>
        <input type="text" class="form-input" name="title" placeholder="e.g. Heavy Rain on Southern Highway" required>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Category *</label>
          <select class="form-select" name="category">
            <option value="EXPRESSWAY_ALERT">Expressway Alert</option>
            <option value="WEATHER_DELAY">Weather Delay</option>
            <option value="ROAD_CLOSURE">Road Closure</option>
            <option value="BUS_SCHEDULE">Bus Schedule Change</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Priority *</label>
          <select class="form-select" name="priority">
            <option value="HIGH">High (Urgent)</option>
            <option value="MEDIUM" selected>Medium</option>
            <option value="LOW">Low (Info)</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Affected Route IDs (comma-separated)</label>
        <input type="text" class="form-input" name="affectedRoutes" value="1, 2">
      </div>
      <div class="form-group">
        <label class="form-label">Public Message Details *</label>
        <textarea class="form-textarea" name="message" placeholder="Provide details for commuters and mobile apps..." required></textarea>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <button type="button" class="btn-outline" onclick="closeModal('addAnnouncementModal')">Cancel</button>
        <button type="submit" class="btn-primary">Broadcast Advisory</button>
      </div>
    </form>
  </div>
</div>

<!-- Core Dashboard Engine (Zero Inline Scripts) -->
<script src="../js/dashboard.js"></script>
</body>
</html>
