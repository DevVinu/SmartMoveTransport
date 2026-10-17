<?php
// Driver portal
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/MongoService.php';
require_once __DIR__ . '/../components/dashboard/profile_controller.php';

// Auth check
Auth::requireRole(['DRIVER', 'ADMIN']);

$currentUser = Auth::user();
$userId      = (int)($currentUser['userId'] ?? $currentUser['userid'] ?? 4);
$firstName   = htmlspecialchars($currentUser['firstName'] ?? $currentUser['firstname'] ?? 'Driver');
$lastName    = htmlspecialchars($currentUser['lastName'] ?? $currentUser['lastname'] ?? 'Partner');
$fullName    = trim($firstName . ' ' . $lastName);
$email       = htmlspecialchars($currentUser['email'] ?? 'driver@smartmove.com');
$role        = strtoupper($currentUser['role'] ?? 'DRIVER');
$initials    = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

// Health check
$dbHealth = Database::checkHealth();
$oracleUp = $dbHealth['oracle']['connected'] ?? false;
$mongoUp  = $dbHealth['mongodb']['connected'] ?? false;

// View routing
$activeView = $_GET['view'] ?? 'cockpitView';
$validViews = ['cockpitView', 'tripsView', 'shiftsView', 'fuelView', 'docsView', 'announcementsView', 'feedbackView', 'profileView'];
if (!in_array($activeView, $validViews)) {
    $activeView = 'cockpitView';
}

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Post actions
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';

    // Confirm passenger payment at boarding PL/SQL
    if ($action === 'confirmPassengerPayment') {
        $paymentId = (int)($_POST['paymentId'] ?? 0);
        if ($paymentId > 0) {
            $res = Database::callConfirmPayment($paymentId, $userId, 'Cash collected at boarding by driver captain');
            if ($res['status'] === 'SUCCESS') {
                $_SESSION['flash_success'] = "Payment #" . $paymentId . " verified and collected! Commuter marked as PAID.";
            } else {
                $_SESSION['flash_error'] = $res['message'];
            }
        }
        header("Location: driver.php?view=" . urlencode($activeView));
        exit;
    }

    // Upload driver license document
    if ($action === 'uploadLicenseDoc') {
        if (!empty($_FILES['licensePdf']['tmp_name'])) {
            $res = User::uploadLicenseDocument($userId, $_FILES['licensePdf']);
            if ($res['status'] === 'SUCCESS') {
                $_SESSION['flash_success'] = "Driving license document uploaded and archived in MongoDB!";
            } else {
                $_SESSION['flash_error'] = $res['message'];
            }
        } else {
            $_SESSION['flash_error'] = "Please choose a valid PDF or image file to upload.";
        }
        header("Location: driver.php?view=" . urlencode($activeView));
        exit;
    }

    // Set duty status
    if ($action === 'setDutyStatus') {
        $newDuty = strtoupper(trim($_POST['dutyStatus'] ?? 'AVAILABLE'));
        if (in_array($newDuty, ['AVAILABLE', 'ON_DUTY', 'OFF_DUTY'])) {
            Database::executeOracle("
                UPDATE driver SET driverStatus = :st WHERE driverId = :dId
            ", [':st' => $newDuty, ':dId' => $userId]);
            $_SESSION['flash_success'] = "Duty status updated to $newDuty.";
            $_SESSION['driver_duty_status'] = $newDuty;
        }
        header("Location: driver.php?view=cockpitView");
        exit;
    }

    // Start trip
    if ($action === 'startTrip') {
        $tripId = (int)($_POST['tripId'] ?? 0);
        if ($tripId) {
            $ok = Database::executeOracle("
                UPDATE scheduledTrip SET tripStatus = 'IN_TRANSIT' WHERE tripId = :tId AND driverId = :dId
            ", [':tId' => $tripId, ':dId' => $userId]);
            Database::executeOracle("UPDATE driver SET driverStatus = 'ON_DUTY' WHERE driverId = :dId", [':dId' => $userId]);
            $_SESSION['driver_duty_status'] = 'ON_DUTY';
            $_SESSION['flash_success'] = "Trip #$tripId started! Status is now IN TRANSIT.";
        }
        header("Location: driver.php?view=cockpitView");
        exit;
    }

    // Complete trip
    if ($action === 'completeTrip') {
        $tripId = (int)($_POST['tripId'] ?? 0);
        if ($tripId) {
            $ok = Database::executeOracle("
                UPDATE scheduledTrip SET tripStatus = 'COMPLETED' WHERE tripId = :tId AND driverId = :dId
            ", [':tId' => $tripId, ':dId' => $userId]);
            Database::executeOracle("UPDATE driver SET driverStatus = 'AVAILABLE' WHERE driverId = :dId", [':dId' => $userId]);
            $_SESSION['driver_duty_status'] = 'AVAILABLE';
            $_SESSION['flash_success'] = "Trip #$tripId marked as COMPLETED. Vehicle returned to schedule.";
        }
        header("Location: driver.php?view=cockpitView");
        exit;
    }

    // Clock shift
    if ($action === 'clockShift') {
        $shiftId   = (int)($_POST['shiftId'] ?? 0);
        $newStatus = strtoupper(trim($_POST['status'] ?? 'ON_DUTY'));
        if ($shiftId) {
            Database::executeOracle("
                UPDATE driverShift SET shiftStatus = :st WHERE shiftId = :sId AND driverId = :dId
            ", [':st' => $newStatus, ':sId' => $shiftId, ':dId' => $userId]);
            if ($newStatus === 'ON_DUTY') {
                Database::executeOracle("UPDATE driver SET driverStatus = 'ON_DUTY' WHERE driverId = :dId", [':dId' => $userId]);
                $_SESSION['driver_duty_status'] = 'ON_DUTY';
            }
            $_SESSION['flash_success'] = "Shift #$shiftId updated to $newStatus.";
        }
        header("Location: driver.php?view=shiftsView");
        exit;
    }

    // Log fuel
    if ($action === 'logFuel') {
        $vehicleId       = (int)($_POST['vehicleId'] ?? 1);
        $stationName     = trim($_POST['stationName'] ?? 'Ceypetco Highway Station');
        $litersPumped    = (float)($_POST['litersPumped'] ?? 0.0);
        $totalCost       = (float)($_POST['totalCost'] ?? 0.0);
        $odometerReading = (float)($_POST['odometerReading'] ?? 0.0);

        if ($litersPumped > 0 && $totalCost > 0) {
            $ok = Database::executeOracle("
                INSERT INTO fuelLog (vehicleId, driverId, refuelDate, fuelQuantityLiters, litersPumped, totalCost, odometerReading, stationName)
                VALUES (:vId, :dId, SYSDATE, :lit, :pmp, :cost, :odo, :stn)
            ", [
                ':vId'  => $vehicleId,
                ':dId'  => $userId,
                ':lit'  => $litersPumped,
                ':pmp'  => $litersPumped,
                ':cost' => $totalCost,
                ':odo'  => $odometerReading,
                ':stn'  => $stationName
            ]);
            if ($ok) {
                $_SESSION['flash_success'] = "Fuel log recorded: {$litersPumped}L at {$stationName}.";
            } else {
                $_SESSION['flash_error'] = "Could not record fuel log.";
            }
        }
        header("Location: driver.php?view=fuelView");
        exit;
    }
}

// Queries
$driverInfo       = null;
$assignedTrips    = [];
$activeTrip       = null;
$tripManifest     = [];
$myShifts         = [];
$fuelLogs         = [];
$passengerReviews = [];

if ($oracleUp) {
    // Driver profile
    $dRows = Database::queryOracle("
        SELECT d.driverId, d.licenseNo, d.licenseNumber, d.licenseType, d.licenseExpiryDate, d.driverStatus
        FROM driver d
        WHERE d.driverId = :dId
    ", [':dId' => $userId]);
    if (!empty($dRows)) {
        $driverInfo = $dRows[0];
    }

    // Assigned trips
    $assignedTrips = Database::queryOracle("
        SELECT st.tripId, r.routeName, r.origin, r.destination, r.distanceKm,
               v.vehicleId, v.registrationNo, v.model, v.vehicleType, v.capacity,
               st.tripDate, st.departureTime, st.arrivalTime, st.availableSeats, st.fareAmount, st.tripStatus
        FROM scheduledTrip st
        JOIN route r ON st.routeId = r.routeId
        JOIN vehicle v ON st.vehicleId = v.vehicleId
        WHERE st.driverId = :dId
        ORDER BY st.tripId DESC
    ", [':dId' => $userId]);

    // Shifts
    $myShifts = Database::queryOracle("
        SELECT shiftId, shiftDate, startTime, endTime, shiftStatus
        FROM driverShift
        WHERE driverId = :dId
        ORDER BY shiftId DESC
    ", [':dId' => $userId]);

    // Fuel logs
    $fuelLogs = Database::queryOracle("
        SELECT fl.fuelLogId, v.registrationNo, fl.refuelDate, fl.stationName,
               fl.litersPumped, fl.totalCost, fl.odometerReading
        FROM fuelLog fl
        JOIN vehicle v ON fl.vehicleId = v.vehicleId
        WHERE fl.driverId = :dId
        ORDER BY fl.fuelLogId DESC
    ", [':dId' => $userId]);
}

// Fallback driver info
if (!$driverInfo) {
    $driverInfo = [
        'driverId'          => $userId,
        'licenseNo'         => 'B-8891234',
        'licenseNumber'     => 'B-8891234',
        'licenseType'       => 'HEAVY_COACH & EXECUTIVE',
        'licenseExpiryDate' => '2028-06-30',
        'driverStatus'      => $_SESSION['driver_duty_status'] ?? 'AVAILABLE'
    ];
}
$currentDutyStatus = strtoupper($_SESSION['driver_duty_status'] ?? $driverInfo['driverStatus'] ?? 'AVAILABLE');

// Fallback trips
if (empty($assignedTrips)) {
    $assignedTrips = [
        [
            'tripId'         => 1,
            'routeName'      => 'Colombo - Kandy Express (E04)',
            'origin'         => 'Colombo (Makumbura)',
            'destination'    => 'Kandy (Goods Shed)',
            'distanceKm'     => 115.5,
            'vehicleId'      => 1,
            'registrationNo' => 'WP-ND-4521',
            'model'          => 'Toyota Coaster Deluxe',
            'vehicleType'    => 'BUS',
            'capacity'       => 32,
            'tripDate'       => date('Y-m-d'),
            'departureTime'  => '07:00 AM',
            'arrivalTime'    => '09:30 AM',
            'availableSeats' => 28,
            'fareAmount'     => 1250.00,
            'tripStatus'     => 'SCHEDULED'
        ],
        [
            'tripId'         => 6,
            'routeName'      => 'Colombo - Kandy Express (E04)',
            'origin'         => 'Colombo (Makumbura)',
            'destination'    => 'Kandy (Goods Shed)',
            'distanceKm'     => 115.5,
            'vehicleId'      => 1,
            'registrationNo' => 'WP-ND-4521',
            'model'          => 'Toyota Coaster Deluxe',
            'vehicleType'    => 'BUS',
            'capacity'       => 32,
            'tripDate'       => date('Y-m-d', strtotime('+1 day')),
            'departureTime'  => '04:00 PM',
            'arrivalTime'    => '06:30 PM',
            'availableSeats' => 30,
            'fareAmount'     => 1250.00,
            'tripStatus'     => 'SCHEDULED'
        ],
        [
            'tripId'         => 9,
            'routeName'      => 'Colombo - Galle Southern Highway (E01)',
            'origin'         => 'Colombo (Makumbura)',
            'destination'    => 'Galle (Pinnaduwa)',
            'distanceKm'     => 128.0,
            'vehicleId'      => 2,
            'registrationNo' => 'WP-CAB-8921',
            'model'          => 'Toyota KDH Commuter',
            'vehicleType'    => 'VAN',
            'capacity'       => 14,
            'tripDate'       => date('Y-m-d', strtotime('-2 days')),
            'departureTime'  => '08:00 AM',
            'arrivalTime'    => '10:00 AM',
            'availableSeats' => 2,
            'fareAmount'     => 1400.00,
            'tripStatus'     => 'COMPLETED'
        ]
    ];
}

// Active trip
foreach ($assignedTrips as $t) {
    if (in_array($t['tripStatus'], ['IN_TRANSIT', 'SCHEDULED'])) {
        $activeTrip = $t;
        break;
    }
}
if (!$activeTrip && !empty($assignedTrips)) {
    $activeTrip = $assignedTrips[0];
}

// Fallback manifest
if ($activeTrip) {
    if ($oracleUp) {
        $tripManifest = Database::queryOracle("
            SELECT b.bookingId, b.seatNumber, b.ticketPrice, b.bookingStatus,
                   u.firstName || ' ' || u.lastName AS passengerName, u.phone,
                   p.paymentId, p.paymentMethod, p.paymentStatus, p.amountPaid
            FROM booking b
            JOIN passenger pass ON b.passengerId = pass.passengerId
            JOIN users u ON pass.passengerId = u.userId
            LEFT JOIN payment p ON b.bookingId = p.bookingId
            WHERE b.tripId = :tId AND b.bookingStatus IN ('CONFIRMED', 'RESERVED')
            ORDER BY b.seatNumber ASC
        ", [':tId' => $activeTrip['tripId']]);
    }

    if (empty($tripManifest)) {
        $tripManifest = [
            ['bookingId' => 501, 'seatNumber' => 4, 'passengerName' => 'Kasun Jayawardena', 'phone' => '+94711112233', 'bookingStatus' => 'CONFIRMED'],
            ['bookingId' => 502, 'seatNumber' => 7, 'passengerName' => 'Dilani Wickramasinghe', 'phone' => '+94712223344', 'bookingStatus' => 'CONFIRMED'],
            ['bookingId' => 503, 'seatNumber' => 12, 'passengerName' => 'Rohan Samaraweera', 'phone' => '+94713334455', 'bookingStatus' => 'CONFIRMED'],
            ['bookingId' => 504, 'seatNumber' => 15, 'passengerName' => 'Anoma Senanayake', 'phone' => '+94714445566', 'bookingStatus' => 'RESERVED', 'paymentId' => 304, 'paymentMethod' => 'CASH', 'paymentStatus' => 'PENDING']
        ];
    }
}

// Fallback shifts
if (empty($myShifts)) {
    $myShifts = [
        ['shiftId' => 201, 'shiftDate' => date('Y-m-d'), 'startTime' => '06:00 AM', 'endTime' => '02:00 PM', 'shiftStatus' => 'ON_DUTY'],
        ['shiftId' => 202, 'shiftDate' => date('Y-m-d', strtotime('+1 day')), 'startTime' => '06:00 AM', 'endTime' => '02:00 PM', 'shiftStatus' => 'SCHEDULED'],
        ['shiftId' => 203, 'shiftDate' => date('Y-m-d', strtotime('-1 day')), 'startTime' => '02:00 PM', 'endTime' => '10:00 PM', 'shiftStatus' => 'COMPLETED']
    ];
}

// Fallback fuel logs
if (empty($fuelLogs)) {
    $fuelLogs = [
        ['fuelLogId' => 11, 'registrationNo' => 'WP-ND-4521', 'refuelDate' => date('Y-m-d', strtotime('-1 day')), 'stationName' => 'Ceypetco Highway Interchange Kottawa', 'litersPumped' => 65.0, 'totalCost' => 24375.00, 'odometerReading' => 142850],
        ['fuelLogId' => 8, 'registrationNo' => 'WP-ND-4521', 'refuelDate' => date('Y-m-d', strtotime('-5 days')), 'stationName' => 'IOC Goods Shed Kandy', 'litersPumped' => 58.0, 'totalCost' => 21750.00, 'odometerReading' => 141920]
    ];
}

// Passenger reviews
if ($mongoUp) {
    $mongoRes = Database::queryMongo('passenger_reviews', ['driverId' => $userId]);
    if (!empty($mongoRes)) {
        $passengerReviews = $mongoRes;
    }
}
if (empty($passengerReviews)) {
    $passengerReviews = [
        [
            'rating'      => 5,
            'comment'     => 'Kamal captain is very punctual, drove smoothly on Central Expressway and reached Kandy on time!',
            'author'      => 'Kasun J.',
            'route'       => 'Colombo - Kandy Express',
            'createdAt'   => date('Y-m-d', strtotime('-2 days'))
        ],
        [
            'rating'      => 5,
            'comment'     => 'Extremely clean coach and very safe driving during heavy rain near Mirigama. 5 stars!',
            'author'      => 'Dilani W.',
            'route'       => 'Colombo - Kandy Express',
            'createdAt'   => date('Y-m-d', strtotime('-6 days'))
        ],
        [
            'rating'      => 4,
            'comment'     => 'Courteous captain and polite assistance with heavy suitcases. Highly recommend.',
            'author'      => 'Buddhika A.',
            'route'       => 'Southern Highway Express',
            'createdAt'   => date('Y-m-d', strtotime('-12 days'))
        ]
    ];
}

// Driver rating
$totalScore = array_sum(array_column($passengerReviews, 'rating'));
$avgRating  = count($passengerReviews) > 0 ? round($totalScore / count($passengerReviews), 1) : 4.9;

// Vehicle documents from MongoDB
$vehicleDocs = [];
if ($mongoUp) {
    $assignedVehicleId = !empty($activeTrip['vehicleId']) ? (int)$activeTrip['vehicleId'] : 1;
    $vehicleDocs = Database::queryMongo('vehicle_documents', ['vehicleId' => $assignedVehicleId]);
    if (empty($vehicleDocs)) {
        $vehicleDocs = Database::queryMongo('vehicle_documents', [], ['limit' => 1]);
    }
}
$currentVehicleDoc = !empty($vehicleDocs) ? $vehicleDocs[0] : null;

// Travel announcements from MongoDB
$driverAnnouncements = [];
if ($mongoUp) {
    $driverAnnouncements = Database::queryMongo('travel_announcements', ['active' => true], ['sort' => ['publishedAt' => -1]]);
}

$driverDoc = User::getDriverDocument($userId);
$driverDoc = User::getDriverDocument($userId);
$pageTitle = "SmartMove — Driver Partner Portal";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <link rel="icon" type="image/jpeg" href="../img/logo.jpg">
  
  <link rel="stylesheet" href="../css/variables.css">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body class="dashboard-body">

<div class="app-wrapper">

  <!-- Sidebar -->
  <?php include __DIR__ . '/../components/dashboard/sidebar.php'; ?>

  <!-- Main viewport -->
  <div class="main-viewport">
    
    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle-btn" id="toggleSidebarBtn" type="button" title="Toggle Sidebar">
          <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" fill="none"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        
        <!-- Duty status -->
        <form method="POST" class="duty-status-bar" id="dutyStatusForm">
          <input type="hidden" name="action" value="setDutyStatus">
          <input type="hidden" name="dutyStatus" id="dutyStatusInput" value="<?php echo $currentDutyStatus; ?>">
          
          <button type="submit" onclick="document.getElementById('dutyStatusInput').value='AVAILABLE'" class="duty-pill-btn <?php echo $currentDutyStatus === 'AVAILABLE' ? 'active-available' : ''; ?>">
            Available
          </button>
          <button type="submit" onclick="document.getElementById('dutyStatusInput').value='ON_DUTY'" class="duty-pill-btn <?php echo $currentDutyStatus === 'ON_DUTY' ? 'active-onduty' : ''; ?>">
            On Duty
          </button>
          <button type="submit" onclick="document.getElementById('dutyStatusInput').value='OFF_DUTY'" class="duty-pill-btn <?php echo $currentDutyStatus === 'OFF_DUTY' ? 'active-offduty' : ''; ?>">
            Off Duty
          </button>
        </form>
      </div>

      <div class="topbar-right">
        <!-- License & PDF Document badge -->
        <div style="display:inline-flex; align-items:center; gap:6px;">
          <div class="type-badge" style="background:var(--bg-card-inner);border:1px solid var(--border-subtle);" title="Driver License">
            Lic: <?php echo htmlspecialchars($driverInfo['licenseNumber'] ?? 'B-LIC'); ?>
          </div>
          <?php if (!empty($driverDoc['filePath'])): ?>
            <a href="../<?php echo htmlspecialchars($driverDoc['filePath']); ?>" target="_blank" class="type-badge" style="background:rgba(56,189,248,0.15);color:#38BDF8;border:1px solid rgba(56,189,248,0.3);text-decoration:none;cursor:pointer;" title="View Uploaded License PDF">
              📄 License PDF
            </a>
          <?php else: ?>
            <button type="button" onclick="document.getElementById('driverDocModal').style.display='flex'" class="type-badge" style="background:rgba(245,158,11,0.15);color:#FBBF24;border:1px solid rgba(245,158,11,0.3);cursor:pointer;" title="Upload License PDF">
              + Upload PDF
            </button>
          <?php endif; ?>
        </div>

        <!-- Rating -->
        <div class="type-badge" style="background:rgba(245, 158, 11, 0.15);color:#FBBF24;border:1px solid rgba(245, 158, 11, 0.35);">
          Rating: <?php echo $avgRating; ?> / 5.0
        </div>

        <!-- Avatar -->
        <button class="topbar-avatar-btn" id="topbarAvatarBtn" type="button">
          <div class="user-avatar" style="background:#0284C7;"><?php echo $initials; ?></div>
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

    <!-- Content -->
    <main class="content-container">

      <!-- Flash messages -->
      <?php include __DIR__ . '/../components/dashboard/flash_messages.php'; ?>

      <!-- Cockpit view -->
      <section class="page-view <?php echo $activeView === 'cockpitView' ? 'active-view' : ''; ?>" id="cockpitView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Road Captain Cockpit</h1>
            <p>Welcome back, <?php echo $fullName; ?>. Here is your current dispatch assignment and passenger manifest.</p>
          </div>
        </div>

        <?php if ($activeTrip): 
          $ast = strtoupper($activeTrip['tripStatus']);
          $isLive = ($ast === 'IN_TRANSIT');
        ?>
          <!-- Active trip banner -->
          <div class="cockpit-banner">
            <div class="cockpit-header">
              <div class="cockpit-route-display">
                <div class="cockpit-station">
                  <span class="cockpit-station-tag">Origin Station</span>
                  <span class="cockpit-station-name"><?php echo htmlspecialchars($activeTrip['origin']); ?></span>
                </div>
                <div class="cockpit-route-arrow">--&gt;</div>
                <div class="cockpit-station">
                  <span class="cockpit-station-tag">Destination Station</span>
                  <span class="cockpit-station-name"><?php echo htmlspecialchars($activeTrip['destination']); ?></span>
                </div>
              </div>

              <div style="display:flex;align-items:center;gap:10px;">
                <span class="badge-status <?php echo $isLive ? 'badge-info' : 'badge-warning'; ?>" style="font-size:13px;padding:6px 14px;">
                  <?php echo $isLive ? 'RUN IN TRANSIT' : 'SCHEDULED DEPARTURE'; ?>
                </span>

                <?php if ($ast === 'SCHEDULED'): ?>
                  <form method="POST">
                    <input type="hidden" name="action" value="startTrip">
                    <input type="hidden" name="tripId" value="<?php echo $activeTrip['tripId']; ?>">
                    <button type="submit" class="btn-primary" style="background:#10B981;border-color:#10B981;">
                      Start Trip Run
                    </button>
                  </form>
                <?php elseif ($ast === 'IN_TRANSIT'): ?>
                  <form method="POST">
                    <input type="hidden" name="action" value="completeTrip">
                    <input type="hidden" name="tripId" value="<?php echo $activeTrip['tripId']; ?>">
                    <button type="submit" class="btn-primary" style="background:#38BDF8;border-color:#38BDF8;color:#0F172A;">
                      Complete Run &amp; Arrive
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>

            <!-- Route meta -->
            <div class="cockpit-meta-grid">
              <div class="cockpit-meta-item">
                <span class="cockpit-meta-label">Route Name</span>
                <span class="cockpit-meta-value"><?php echo htmlspecialchars($activeTrip['routeName']); ?></span>
              </div>
              <div class="cockpit-meta-item">
                <span class="cockpit-meta-label">Vehicle Asset</span>
                <span class="cockpit-meta-value"><?php echo htmlspecialchars($activeTrip['registrationNo']); ?> (<?php echo htmlspecialchars($activeTrip['model']); ?>)</span>
              </div>
              <div class="cockpit-meta-item">
                <span class="cockpit-meta-label">Departure Time</span>
                <span class="cockpit-meta-value" style="color:#FBBF24;"><?php echo htmlspecialchars($activeTrip['departureTime']); ?></span>
              </div>
              <div class="cockpit-meta-item">
                <span class="cockpit-meta-label">Distance</span>
                <span class="cockpit-meta-value"><?php echo htmlspecialchars($activeTrip['distanceKm']); ?> KM</span>
              </div>
              <div class="cockpit-meta-item">
                <span class="cockpit-meta-label">Booked Manifest</span>
                <span class="cockpit-meta-value" style="color:#34D399;"><?php echo count($tripManifest); ?> / <?php echo $activeTrip['capacity']; ?> Passengers</span>
              </div>
            </div>
          </div>

          <!-- Manifest -->
          <div class="dash-overview-grid" style="margin-top:12px;">
            <div class="dash-panel">
              <div class="panel-header">
                <h3 class="panel-title">Passenger Boarding Manifest (Trip #<?php echo $activeTrip['tripId']; ?>)</h3>
                <p class="panel-subtitle">Confirmed commuter seat allocations for this run</p>
              </div>

              <div class="manifest-list">
                <?php if (empty($tripManifest)): ?>
                  <div style="padding:16px;text-align:center;color:var(--text-muted);">No passengers currently booked on this run.</div>
                <?php else: ?>
                  <?php foreach ($tripManifest as $passenger): ?>
                    <div class="manifest-item" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                      <div style="display:flex;align-items:center;gap:12px;">
                        <span class="manifest-seat-badge">Seat <?php echo $passenger['seatNumber']; ?></span>
                        <div>
                          <div style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($passenger['passengerName']); ?></div>
                          <div style="font-size:11px;color:var(--text-muted);">Contact: <?php echo htmlspecialchars($passenger['phone'] ?? 'N/A'); ?> • Fare: LKR <?php echo number_format((float)($passenger['ticketPrice'] ?? 0), 2); ?></div>
                        </div>
                      </div>
                      <div style="display:flex;align-items:center;gap:8px;">
                        <?php 
                          $payStatus = strtoupper($passenger['paymentStatus'] ?? 'PAID');
                          $payMethod = strtoupper($passenger['paymentMethod'] ?? 'CARD');
                        ?>
                        <?php if ($payStatus === 'PENDING'): ?>
                          <span class="badge-status badge-urgent" style="font-size:11px;">PENDING (<?php echo $payMethod; ?>)</span>
                          <?php if (!empty($passenger['paymentId'])): ?>
                            <form method="POST" style="display:inline;">
                              <input type="hidden" name="action" value="confirmPassengerPayment">
                              <input type="hidden" name="paymentId" value="<?php echo $passenger['paymentId']; ?>">
                              <button type="submit" class="action-btn-sm" style="background:#059669;color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-weight:600;font-size:11px;" title="Confirm Cash Received at Boarding">
                                ✓ Confirm Payment
                              </button>
                            </form>
                          <?php endif; ?>
                        <?php else: ?>
                          <span class="badge-status badge-active" style="font-size:11px;">PAID (<?php echo $payMethod; ?>)</span>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

            <!-- Safety checklist -->
            <div class="dash-panel">
              <div class="panel-header">
                <h3 class="panel-title">Pre-Departure Safety Checklist</h3>
                <p class="panel-subtitle">Ensure road safety before clearing toll terminal</p>
              </div>

              <div style="display:flex;flex-direction:column;gap:12px;">
                <label style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--text-primary);cursor:pointer;">
                  <input type="checkbox" checked style="accent-color:var(--blue-primary);width:16px;height:16px;">
                  Brake pads &amp; hydraulic fluid pressure verified
                </label>
                <label style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--text-primary);cursor:pointer;">
                  <input type="checkbox" checked style="accent-color:var(--blue-primary);width:16px;height:16px;">
                  Tire pressure &amp; spare tire condition inspected
                </label>
                <label style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--text-primary);cursor:pointer;">
                  <input type="checkbox" checked style="accent-color:var(--blue-primary);width:16px;height:16px;">
                  Passenger luggage compartments secured
                </label>
                <label style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--text-primary);cursor:pointer;">
                  <input type="checkbox" checked style="accent-color:var(--blue-primary);width:16px;height:16px;">
                  Toll pass &amp; electronic fare console synced
                </label>
              </div>

              <div style="margin-top:20px;padding-top:14px;border-top:1px solid var(--border-subtle);display:flex;gap:10px;">
                <button type="button" class="btn-outline" style="width:100%;justify-content:center;" onclick="openModal('logFuelModal')">
                   Log Refuel Station
                </button>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div style="padding:32px;text-align:center;background:var(--bg-card);border:1px solid var(--border-subtle);border-radius:var(--radius-card);">
            <h3>No Scheduled Runs Assigned Right Now</h3>
            <p style="color:var(--text-muted);margin-top:6px;">Check your upcoming shifts with dispatch operations.</p>
          </div>
        <?php endif; ?>
      </section>

      <!-- Trips view -->
      <section class="page-view <?php echo $activeView === 'tripsView' ? 'active-view' : ''; ?>" id="tripsView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>My Assigned Trips &amp; Routes</h1>
            <p>Complete historical log and upcoming scheduled runs assigned to your captain account.</p>
          </div>
        </div>

        <div class="filter-pills-bar">
          <button class="filter-pill active" onclick="filterTrips('ALL', this)">All Runs (<?php echo count($assignedTrips); ?>)</button>
          <button class="filter-pill" onclick="filterTrips('SCHEDULED', this)">Scheduled</button>
          <button class="filter-pill" onclick="filterTrips('IN_TRANSIT', this)">In Transit</button>
          <button class="filter-pill" onclick="filterTrips('COMPLETED', this)">Completed</button>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Trip ID</th>
                <th>Route Corridor</th>
                <th>Vehicle Asset</th>
                <th>Run Date</th>
                <th>Departure Time</th>
                <th>Seats Available</th>
                <th>Fare</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($assignedTrips as $trip): 
                $tst = strtoupper($trip['tripStatus']);
                $tBadge = match($tst) {
                  'IN_TRANSIT' => 'badge-info',
                  'COMPLETED'  => 'badge-active',
                  default      => 'badge-warning'
                };
              ?>
                <tr class="trip-row" data-status="<?php echo $tst; ?>">
                  <td><strong>#<?php echo $trip['tripId']; ?></strong></td>
                  <td>
                    <div style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($trip['routeName']); ?></div>
                    <div style="font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars($trip['origin']); ?>  <?php echo htmlspecialchars($trip['destination']); ?></div>
                  </td>
                  <td>
                    <div><?php echo htmlspecialchars($trip['registrationNo']); ?></div>
                    <div style="font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars($trip['model'] ?? ''); ?></div>
                  </td>
                  <td><?php echo htmlspecialchars($trip['tripDate']); ?></td>
                  <td style="color:#FBBF24;"><?php echo htmlspecialchars($trip['departureTime']); ?></td>
                  <td><span class="type-badge"><?php echo $trip['availableSeats']; ?> Seats Left</span></td>
                  <td>LKR <?php echo number_format($trip['fareAmount'], 2); ?></td>
                  <td><span class="badge-status <?php echo $tBadge; ?>"><?php echo $tst; ?></span></td>
                  <td style="text-align:right;">
                    <?php if ($tst === 'SCHEDULED'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="startTrip">
                        <input type="hidden" name="tripId" value="<?php echo $trip['tripId']; ?>">
                        <button type="submit" class="action-btn-sm" style="color:#34D399;">Start Run</button>
                      </form>
                    <?php elseif ($tst === 'IN_TRANSIT'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="completeTrip">
                        <input type="hidden" name="tripId" value="<?php echo $trip['tripId']; ?>">
                        <button type="submit" class="action-btn-sm" style="color:#38BDF8;">Finish Run</button>
                      </form>
                    <?php else: ?>
                      <span style="font-size:11px;color:var(--text-muted);">Completed</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Shifts view -->
      <section class="page-view <?php echo $activeView === 'shiftsView' ? 'active-view' : ''; ?>" id="shiftsView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Duty Shifts &amp; Clock Roster</h1>
            <p>Track your scheduled duty shifts and clock in/out for attendance verification.</p>
          </div>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Shift ID</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Status</th>
                <th style="text-align:right;">Clock Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($myShifts as $sh): 
                $sst = strtoupper($sh['shiftStatus']);
                $sBadge = match($sst) {
                  'ON_DUTY'   => 'badge-active',
                  'COMPLETED' => 'badge-neutral',
                  default     => 'badge-warning'
                };
              ?>
                <tr>
                  <td><strong>#<?php echo $sh['shiftId'] ?? $sh['shiftid'] ?? ''; ?></strong></td>
                  <td style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($sh['shiftDate'] ?? $sh['shiftdate'] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($sh['startTime'] ?? $sh['starttime'] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($sh['endTime'] ?? $sh['endtime'] ?? ''); ?></td>
                  <td><span class="badge-status <?php echo $sBadge; ?>"><?php echo $sst; ?></span></td>
                  <td style="text-align:right;">
                    <?php if ($sst === 'SCHEDULED'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="clockShift">
                        <input type="hidden" name="shiftId" value="<?php echo $sh['shiftId']; ?>">
                        <input type="hidden" name="status" value="ON_DUTY">
                        <button type="submit" class="action-btn-sm" style="color:#34D399;">Clock In</button>
                      </form>
                    <?php elseif ($sst === 'ON_DUTY'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="clockShift">
                        <input type="hidden" name="shiftId" value="<?php echo $sh['shiftId']; ?>">
                        <input type="hidden" name="status" value="COMPLETED">
                        <button type="submit" class="action-btn-sm">Clock Out</button>
                      </form>
                    <?php else: ?>
                      <span style="font-size:11px;color:var(--text-muted);">Clocked Out</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Fuel view -->
      <section class="page-view <?php echo $activeView === 'fuelView' ? 'active-view' : ''; ?>" id="fuelView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Vehicle Health &amp; Fueling Records</h1>
            <p>Log fuel station receipts, verify odometer readings, and report vehicle consumption.</p>
          </div>
          <div class="view-actions">
            <button class="btn-primary" onclick="openModal('logFuelModal')">
              <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Log Fuel Station
            </button>
          </div>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Log ID</th>
                <th>Vehicle Reg</th>
                <th>Date</th>
                <th>Filling Station</th>
                <th>Liters Pumped</th>
                <th>Total Cost (LKR)</th>
                <th>Odometer Reading</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($fuelLogs as $fl): ?>
                <tr>
                  <td><strong>#<?php echo $fl['fuelLogId'] ?? $fl['fuellogid'] ?? ''; ?></strong></td>
                  <td style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($fl['registrationNo'] ?? $fl['registrationno'] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($fl['refuelDate'] ?? $fl['refueldate'] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($fl['stationName'] ?? $fl['stationname'] ?? ''); ?></td>
                  <td><span class="type-badge"><?php echo number_format((float)($fl['litersPumped'] ?? $fl['literspumped'] ?? 0), 1); ?> L</span></td>
                  <td style="font-weight:600;color:#34D399;">LKR <?php echo number_format((float)($fl['totalCost'] ?? $fl['totalcost'] ?? 0), 2); ?></td>
                  <td style="font-family:monospace;"><?php echo number_format((float)($fl['odometerReading'] ?? $fl['odometerreading'] ?? 0)); ?> KM</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Reviews view -->
      <section class="page-view <?php echo $activeView === 'feedbackView' ? 'active-view' : ''; ?>" id="feedbackView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Captain Ratings &amp; Passenger Reviews</h1>
            <p>Direct commuter feedback synced from Oracle relational feedback and MongoDB NoSQL store.</p>
          </div>
        </div>

        <!-- Rating summary -->
        <div class="stat-card" style="margin-bottom:20px;padding:24px;">
          <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
            <div style="font-size:48px;font-weight:800;color:#FBBF24;line-height:1;">
              <?php echo $avgRating; ?>
            </div>
            <div>
              <div style="color:#F59E0B;font-size:18px;letter-spacing:3px;">* * * * *</div>
              <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">
                Based on <?php echo count($passengerReviews); ?> verified passenger reviews
              </div>
            </div>
            <div style="margin-left:auto;">
              <span class="badge-status badge-active" style="padding:6px 14px;font-size:12px;">Top Rated Captain Tier</span>
            </div>
          </div>
        </div>

        <!-- Reviews grid -->
        <div class="reviews-grid">
          <?php foreach ($passengerReviews as $rev): ?>
            <div class="review-card">
              <div>
                <div class="review-stars">
                  <?php echo str_repeat('*', (int)($rev['rating'] ?? 5)); ?><?php echo str_repeat('-', 5 - (int)($rev['rating'] ?? 5)); ?>
                <?php 
                  $rAuthor = is_string($rev['author'] ?? null) ? $rev['author'] : 'Commuter';
                  $rComment = is_string($rev['comment'] ?? null) ? $rev['comment'] : (is_string($rev['comments'] ?? null) ? $rev['comments'] : '');
                  $rCreated = $rev['createdAt'] ?? date('Y-m-d');
                  if (is_array($rCreated)) {
                    $rCreated = $rCreated['$date'] ?? date('Y-m-d');
                  }
                  if (!is_string($rCreated)) {
                    $rCreated = date('Y-m-d');
                  }
                ?>
                <p class="review-text" style="margin-top:10px;">
                  "<?php echo htmlspecialchars($rComment); ?>"
                </p>
              </div>

              <div class="review-author-row">
                <span style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($rAuthor); ?></span>
                <span><?php echo htmlspecialchars($rCreated); ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- Vehicle Documents View -->
      <section class="page-view <?php echo $activeView === 'docsView' ? 'active-view' : ''; ?>" id="docsView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Vehicle Compliance Documents &amp; Specifications</h1>
            <p>Inspect digital permits, commercial insurance, revenue license, and telemetry of your assigned coach.</p>
          </div>
        </div>

        <?php if ($currentVehicleDoc): 
          $docsList = $currentVehicleDoc['documents'] ?? [];
          $telemetry = $currentVehicleDoc['telemetry'] ?? [];
        ?>
          <!-- Vehicle details card -->
          <div class="dash-panel" style="margin-bottom:1.5rem; padding:1.25rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-subtle); padding-bottom:1rem; margin-bottom:1rem;">
              <div>
                <span style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Assigned Fleet Unit</span>
                <h2 style="font-size:1.4rem; font-weight:800; color:var(--text-white); margin-top:0.2rem;">
                  <?php echo htmlspecialchars($currentVehicleDoc['registrationNo'] ?? 'WP-ND-4521'); ?>
                  <span style="font-size:0.85rem; font-weight:600; background:rgba(56,189,248,0.15); color:#38BDF8; padding:0.2rem 0.5rem; border-radius:4px; margin-left:0.5rem;">
                    <?php echo htmlspecialchars($currentVehicleDoc['vehicleType'] ?? 'BUS'); ?>
                  </span>
                </h2>
              </div>
              <div style="text-align:right;">
                <span style="font-size:0.75rem; color:var(--text-muted);">Model / Year</span>
                <div style="font-size:1rem; font-weight:700; color:var(--text-white);">
                  <?php echo htmlspecialchars(($currentVehicleDoc['manufacturer'] ?? '') . ' ' . ($currentVehicleDoc['model'] ?? '')); ?> (<?php echo htmlspecialchars($currentVehicleDoc['yearOfManufacture'] ?? '2024'); ?>)
                </div>
              </div>
            </div>

            <!-- Telemetry stats -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
              <div style="background:var(--bg-card-inner); padding:0.75rem 1rem; border-radius:6px; border:1px solid var(--border-subtle);">
                <div style="font-size:0.75rem; color:var(--text-muted);">SEATING CAPACITY</div>
                <div style="font-size:1.1rem; font-weight:700; color:#fff;"><?php echo htmlspecialchars($currentVehicleDoc['capacity'] ?? 32); ?> Passengers</div>
              </div>
              <div style="background:var(--bg-card-inner); padding:0.75rem 1rem; border-radius:6px; border:1px solid var(--border-subtle);">
                <div style="font-size:0.75rem; color:var(--text-muted);">GPS TRACKER IMEI</div>
                <div style="font-size:0.95rem; font-weight:700; color:#38BDF8; font-family:monospace;"><?php echo htmlspecialchars($telemetry['gpsTrackingImei'] ?? '864209043219012'); ?></div>
              </div>
              <div style="background:var(--bg-card-inner); padding:0.75rem 1rem; border-radius:6px; border:1px solid var(--border-subtle);">
                <div style="font-size:0.75rem; color:var(--text-muted);">FUEL TYPE</div>
                <div style="font-size:1.1rem; font-weight:700; color:#34D399;"><?php echo htmlspecialchars($telemetry['fuelType'] ?? 'DIESEL'); ?></div>
              </div>
              <div style="background:var(--bg-card-inner); padding:0.75rem 1rem; border-radius:6px; border:1px solid var(--border-subtle);">
                <div style="font-size:0.75rem; color:var(--text-muted);">LAST ODOMETER</div>
                <div style="font-size:1.1rem; font-weight:700; color:#FBBF24;"><?php echo number_format((float)($telemetry['lastOdometerKm'] ?? 48250)); ?> KM</div>
              </div>
            </div>
          </div>

          <!-- Documents list -->
          <div class="dash-panel">
            <h3 style="font-size:1.05rem; font-weight:700; color:#fff; margin-bottom:1rem;">Verified Regulatory Documents</h3>
            <div class="data-table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Type</th>
                    <th>Document Title</th>
                    <th>Certificate #</th>
                    <th>Issuing Authority</th>
                    <th>Valid From</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($docsList as $doc): ?>
                    <tr>
                      <td><span style="background:var(--bg-card-inner); padding:0.25rem 0.5rem; border-radius:4px; font-weight:700; font-size:0.75rem; color:#38BDF8;"><?php echo htmlspecialchars($doc['docType'] ?? 'PERMIT'); ?></span></td>
                      <td><strong><?php echo htmlspecialchars($doc['title'] ?? 'Vehicle Permit'); ?></strong></td>
                      <td style="font-family:monospace;"><?php echo htmlspecialchars($doc['documentNo'] ?? '-'); ?></td>
                      <td><?php echo htmlspecialchars($doc['issuer'] ?? 'Ministry of Transport'); ?></td>
                      <td><?php echo htmlspecialchars($doc['issueDate'] ?? '-'); ?></td>
                      <td><strong><?php echo htmlspecialchars($doc['expiryDate'] ?? '-'); ?></strong></td>
                      <td><span class="badge-status badge-active"><?php echo htmlspecialchars($doc['status'] ?? 'VALID'); ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php else: ?>
          <div class="dash-panel" style="text-align:center; padding:3rem 1rem; color:var(--text-secondary);">
            <h3>No Vehicle Compliance Records Found</h3>
            <p style="margin-top:0.5rem; font-size:0.9rem;">Vehicle documents are retrieved from the MongoDB repository.</p>
          </div>
        <?php endif; ?>
      </section>

      <!-- Travel Announcements View -->
      <section class="page-view <?php echo $activeView === 'announcementsView' ? 'active-view' : ''; ?>" id="announcementsView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Travel Advisories &amp; Expressway Alerts</h1>
            <p>Live road closure notices, weather alerts, and corridor bulletins issued by central dispatch.</p>
          </div>
        </div>

        <?php if (empty($driverAnnouncements)): ?>
          <div class="dash-panel" style="text-align:center; padding:3rem 1rem; color:var(--text-secondary);">
            <h3>All Highway Corridors Clear</h3>
            <p style="margin-top:0.5rem; font-size:0.9rem;">No active severe weather or lane closure advisories at this time.</p>
          </div>
        <?php else: ?>
          <div style="display:grid; gap:1rem;">
            <?php foreach ($driverAnnouncements as $ann): 
              $pri = strtoupper($ann['priority'] ?? 'MEDIUM');
              $priColor = ($pri === 'HIGH') ? '#F87171' : (($pri === 'MEDIUM') ? '#FBBF24' : '#34D399');
              $priBg = ($pri === 'HIGH') ? 'rgba(239,68,68,0.15)' : (($pri === 'MEDIUM') ? 'rgba(251,191,36,0.15)' : 'rgba(52,211,153,0.15)');
            ?>
              <div class="dash-panel" style="padding:1.25rem; border-left:4px solid <?php echo $priColor; ?>;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
                  <div style="display:flex; align-items:center; gap:0.5rem;">
                    <span style="background:<?php echo $priBg; ?>; color:<?php echo $priColor; ?>; font-size:0.7rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:4px;">
                      <?php echo $pri; ?> PRIORITY
                    </span>
                    <span style="font-size:0.75rem; color:var(--text-muted); font-family:monospace;">
                      <?php echo htmlspecialchars($ann['category'] ?? 'GENERAL'); ?>
                    </span>
                  </div>
                  <span style="font-size:0.75rem; color:var(--text-muted);">
                    <?php echo htmlspecialchars($ann['publishedAt'] ?? ''); ?>
                  </span>
                </div>

                <h3 style="color:#fff; font-size:1.1rem; font-weight:700; margin-bottom:0.5rem;">
                  <?php echo htmlspecialchars($ann['title'] ?? ''); ?>
                </h3>
                <p style="color:var(--text-secondary); font-size:0.9rem; line-height:1.5; margin-bottom:0.75rem;">
                  <?php echo htmlspecialchars($ann['message'] ?? ''); ?>
                </p>

                <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:var(--text-muted); padding-top:0.5rem; border-top:1px solid var(--border-subtle);">
                  <span>Issued by: <?php echo htmlspecialchars($ann['author'] ?? 'National Transit Operations'); ?></span>
                  <?php if (!empty($ann['affectedRoutes'])): ?>
                    <span>Affected Routes: Route #<?php echo implode(', #', $ann['affectedRoutes']); ?></span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <?php include __DIR__ . '/../components/dashboard/views/profile_view.php'; ?>

    </main>
  </div>
</div>

<!-- Fuel modal -->
<div class="modal-backdrop" id="logFuelModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 class="modal-title">Record Vehicle Refueling Receipt</h3>
      <button class="modal-close-btn" type="button" onclick="closeModal('logFuelModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="logFuel">
      <div class="form-group">
        <label class="form-label">Fuel Station &amp; Location *</label>
        <input type="text" class="form-input" name="stationName" value="Ceypetco Highway Station, Kottawa" required>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Liters Pumped *</label>
          <input type="number" step="0.1" class="form-input" name="litersPumped" value="50.0" required>
        </div>
        <div class="form-group">
          <label class="form-label">Total Cost in LKR *</label>
          <input type="number" step="0.5" class="form-input" name="totalCost" value="18750.00" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Current Odometer Reading (KM) *</label>
        <input type="number" class="form-input" name="odometerReading" value="143200" required>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <button type="button" class="btn-outline" onclick="closeModal('logFuelModal')">Cancel</button>
        <button type="submit" class="btn-primary">Save Fuel Receipt</button>
      </div>
    </form>
  </div>
</div>

<!-- Script -->
<script src="../js/dashboard.js"></script>

<!-- Driver License Document Upload Modal -->
<div id="driverDocModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
  <div style="background:var(--bg-card);border:1px solid var(--border-subtle);border-radius:var(--radius-lg);max-width:480px;width:100%;padding:1.5rem;box-shadow:none;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;border-bottom:1px solid var(--border-subtle);padding-bottom:0.75rem;">
      <h3 style="color:#fff;font-size:1.1rem;margin:0;">Upload Driving License Document</h3>
      <button type="button" onclick="document.getElementById('driverDocModal').style.display='none'" style="background:none;border:none;color:var(--text-muted);font-size:1.5rem;cursor:pointer;">&times;</button>
    </div>
    <form method="POST" action="driver.php" enctype="multipart/form-data">
      <input type="hidden" name="action" value="uploadLicenseDoc">
      <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:1rem;">
        Upload a digital copy of your heavy vehicle driving license (PDF, JPG, or PNG). This document will be securely cataloged in MongoDB.
      </p>
      <div style="margin-bottom:1.25rem;">
        <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.35rem;">Select Document File *</label>
        <input type="file" name="licensePdf" accept=".pdf,image/png,image/jpeg" required style="width:100%;padding:0.5rem;background:var(--bg-card-inner);border:1px dashed var(--border-subtle);color:#fff;border-radius:6px;cursor:pointer;">
      </div>
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;">
        <button type="button" onclick="document.getElementById('driverDocModal').style.display='none'" class="btn-ghost" style="padding:0.5rem 1rem;">Cancel</button>
        <button type="submit" class="btn-primary" style="padding:0.5rem 1.25rem;">Upload Document</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
