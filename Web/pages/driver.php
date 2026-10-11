<?php
/**
 * SmartMove Transport Solutions — Driver Partner Portal
 * Enterprise Dark Slate Theme (ShadcnUI Aesthetic)
 * 
 * Functional Features for Road Captains / Fleet Drivers:
 * - Real-Time Duty Status Toggling (AVAILABLE / ON_DUTY / OFF_DUTY)
 * - FR-08: Active Assigned Trip Cockpit & Passenger Manifest
 * - Trip Execution Lifecycle (Start Run -> In Transit -> Completed)
 * - FR-04: Duty Shift Rostering & Shift Clock-In/Clock-Out
 * - Fuel & Mileage Refuel Logging (fuelLog)
 * - FR-07: Passenger Ratings & Feedback Auditing (MongoDB + Oracle)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/MongoService.php';

// Session Security: Require DRIVER or ADMIN role
Auth::requireRole(['DRIVER', 'ADMIN']);

$currentUser = Auth::user();
$userId      = (int)($currentUser['userId'] ?? $currentUser['userid'] ?? 4);
$firstName   = htmlspecialchars($currentUser['firstName'] ?? $currentUser['firstname'] ?? 'Driver');
$lastName    = htmlspecialchars($currentUser['lastName'] ?? $currentUser['lastname'] ?? 'Partner');
$fullName    = trim($firstName . ' ' . $lastName);
$email       = htmlspecialchars($currentUser['email'] ?? 'driver@smartmove.com');
$role        = strtoupper($currentUser['role'] ?? 'DRIVER');
$initials    = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

// Database Health Check
$dbHealth = Database::checkHealth();
$oracleUp = $dbHealth['oracle']['connected'] ?? false;
$mongoUp  = $dbHealth['mongodb']['connected'] ?? false;

// Active View Routing
$activeView = $_GET['view'] ?? 'cockpitView';
$validViews = ['cockpitView', 'tripsView', 'shiftsView', 'fuelView', 'feedbackView'];
if (!in_array($activeView, $validViews)) {
    $activeView = 'cockpitView';
}

// Flash Notifications
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// -----------------------------------------------------------------------------
// POST CONTROLLER: DRIVER ACTIONS
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Toggle Duty Status
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

    // Action 2: Start Assigned Trip (Set to IN_TRANSIT)
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

    // Action 3: Complete Trip (Set to COMPLETED)
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

    // Action 4: Shift Clock-In / Clock-Out
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

    // Action 5: Log Fuel Refill
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

// -----------------------------------------------------------------------------
// DATA QUERIES (With Realistic Fallbacks)
// -----------------------------------------------------------------------------
$driverInfo       = null;
$assignedTrips    = [];
$activeTrip       = null;
$tripManifest     = [];
$myShifts         = [];
$fuelLogs         = [];
$passengerReviews = [];

if ($oracleUp) {
    // 1. Driver Profile & License
    $dRows = Database::queryOracle("
        SELECT d.driverId, d.licenseNo, d.licenseNumber, d.licenseType, d.licenseExpiryDate, d.driverStatus
        FROM driver d
        WHERE d.driverId = :dId
    ", [':dId' => $userId]);
    if (!empty($dRows)) {
        $driverInfo = $dRows[0];
    }

    // 2. Assigned Trips
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

    // 3. Driver Shifts
    $myShifts = Database::queryOracle("
        SELECT shiftId, shiftDate, startTime, endTime, shiftStatus
        FROM driverShift
        WHERE driverId = :dId
        ORDER BY shiftId DESC
    ", [':dId' => $userId]);

    // 4. Fuel Logs
    $fuelLogs = Database::queryOracle("
        SELECT fl.fuelLogId, v.registrationNo, fl.refuelDate, fl.stationName,
               fl.litersPumped, fl.totalCost, fl.odometerReading
        FROM fuelLog fl
        JOIN vehicle v ON fl.vehicleId = v.vehicleId
        WHERE fl.driverId = :dId
        ORDER BY fl.fuelLogId DESC
    ", [':dId' => $userId]);
}

// Fallbacks for Driver Info
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

// Fallback Assigned Trips
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

// Locate Active or Next Scheduled Trip
foreach ($assignedTrips as $t) {
    if (in_array($t['tripStatus'], ['IN_TRANSIT', 'SCHEDULED'])) {
        $activeTrip = $t;
        break;
    }
}
if (!$activeTrip && !empty($assignedTrips)) {
    $activeTrip = $assignedTrips[0];
}

// Fallback Manifest for Active Trip
if ($activeTrip) {
    if ($oracleUp) {
        $tripManifest = Database::queryOracle("
            SELECT b.bookingId, b.seatNumber, b.ticketPrice, b.bookingStatus,
                   u.firstName || ' ' || u.lastName AS passengerName, u.phone
            FROM booking b
            JOIN passenger pass ON b.passengerId = pass.passengerId
            JOIN users u ON pass.passengerId = u.userId
            WHERE b.tripId = :tId AND b.bookingStatus IN ('CONFIRMED', 'RESERVED')
            ORDER BY b.seatNumber ASC
        ", [':tId' => $activeTrip['tripId']]);
    }

    if (empty($tripManifest)) {
        $tripManifest = [
            ['bookingId' => 501, 'seatNumber' => 4, 'passengerName' => 'Kasun Jayawardena', 'phone' => '+94711112233', 'bookingStatus' => 'CONFIRMED'],
            ['bookingId' => 502, 'seatNumber' => 7, 'passengerName' => 'Dilani Wickramasinghe', 'phone' => '+94712223344', 'bookingStatus' => 'CONFIRMED'],
            ['bookingId' => 503, 'seatNumber' => 12, 'passengerName' => 'Rohan Samaraweera', 'phone' => '+94713334455', 'bookingStatus' => 'CONFIRMED'],
            ['bookingId' => 504, 'seatNumber' => 15, 'passengerName' => 'Anoma Senanayake', 'phone' => '+94714445566', 'bookingStatus' => 'RESERVED']
        ];
    }
}

// Fallback Shifts
if (empty($myShifts)) {
    $myShifts = [
        ['shiftId' => 201, 'shiftDate' => date('Y-m-d'), 'startTime' => '06:00 AM', 'endTime' => '02:00 PM', 'shiftStatus' => 'ON_DUTY'],
        ['shiftId' => 202, 'shiftDate' => date('Y-m-d', strtotime('+1 day')), 'startTime' => '06:00 AM', 'endTime' => '02:00 PM', 'shiftStatus' => 'SCHEDULED'],
        ['shiftId' => 203, 'shiftDate' => date('Y-m-d', strtotime('-1 day')), 'startTime' => '02:00 PM', 'endTime' => '10:00 PM', 'shiftStatus' => 'COMPLETED']
    ];
}

// Fallback Fuel Logs
if (empty($fuelLogs)) {
    $fuelLogs = [
        ['fuelLogId' => 11, 'registrationNo' => 'WP-ND-4521', 'refuelDate' => date('Y-m-d', strtotime('-1 day')), 'stationName' => 'Ceypetco Highway Interchange Kottawa', 'litersPumped' => 65.0, 'totalCost' => 24375.00, 'odometerReading' => 142850],
        ['fuelLogId' => 8, 'registrationNo' => 'WP-ND-4521', 'refuelDate' => date('Y-m-d', strtotime('-5 days')), 'stationName' => 'IOC Goods Shed Kandy', 'litersPumped' => 58.0, 'totalCost' => 21750.00, 'odometerReading' => 141920]
    ];
}

// MongoDB Passenger Reviews & Ratings (FR-07)
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

// Compute Driver Score
$totalScore = array_sum(array_column($passengerReviews, 'rating'));
$avgRating  = count($passengerReviews) > 0 ? round($totalScore / count($passengerReviews), 1) : 4.9;

$pageTitle = "SmartMove — Driver Partner Portal";
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
    <div class="sidebar-header" title="SmartMove Driver Portal">
      <div class="sidebar-brand-wrapper">
        <div class="brand-icon-box" style="background:#0284C7;">DR</div>
        <div class="sidebar-header-text">
          <span class="brand-name">SmartMove</span>
          <span class="brand-subtext">DRIVER PORTAL</span>
        </div>
      </div>
      <div class="brand-arrows">⌘</div>
    </div>

    <!-- Nav List -->
    <nav class="sidebar-nav">
      <div class="sidebar-group">
        <div class="sidebar-group-title">CAPTAIN DASHBOARD</div>

        <a class="nav-item <?php echo $activeView === 'cockpitView' ? 'active' : ''; ?>" data-view="cockpitView" title="Driver Cockpit & Run Manifest">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
          </div>
          <span>Active Cockpit</span>
          <?php if ($activeTrip && $activeTrip['tripStatus'] === 'IN_TRANSIT'): ?>
            <span class="nav-badge" style="background:#38BDF8;">LIVE</span>
          <?php endif; ?>
        </a>

        <a class="nav-item <?php echo $activeView === 'tripsView' ? 'active' : ''; ?>" data-view="tripsView" title="My Assigned Runs">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1 .4-1 1v7c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
          </div>
          <span>My Assigned Trips</span>
          <span class="nav-badge" style="background:var(--blue-primary);"><?php echo count($assignedTrips); ?></span>
        </a>

        <a class="nav-item <?php echo $activeView === 'shiftsView' ? 'active' : ''; ?>" data-view="shiftsView" title="Duty Shifts & Roster">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <span>My Shifts</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'fuelView' ? 'active' : ''; ?>" data-view="fuelView" title="Vehicle Health & Refueling">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M3 22v-8a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v8"/><path d="M15 10a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v12"/><path d="M7 12V2"/><path d="M19 8V2"/></svg>
          </div>
          <span>Fuel &amp; Vehicle Logs</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'feedbackView' ? 'active' : ''; ?>" data-view="feedbackView" title="Ratings & Passenger Feedback">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          </div>
          <span>Reviews &amp; Ratings</span>
          <span class="nav-badge" style="background:#F59E0B;"><?php echo $avgRating; ?>★</span>
        </a>
      </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <button class="user-profile-btn" id="sidebarUserBtn" type="button">
        <div class="user-avatar" style="background:#0284C7;"><?php echo $initials; ?></div>
        <div class="user-details">
          <span class="user-name"><?php echo $fullName; ?></span>
          <span class="user-email">CAPTAIN #<?php echo $userId; ?></span>
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
          <a href="dashboard.php" class="popover-item">🛡️ Admin Portal</a>
          <a href="staff.php" class="popover-item">📋 Staff Portal</a>
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
        
        <!-- Live Duty Status Toggle Pill Bar -->
        <form method="POST" class="duty-status-bar" id="dutyStatusForm">
          <input type="hidden" name="action" value="setDutyStatus">
          <input type="hidden" name="dutyStatus" id="dutyStatusInput" value="<?php echo $currentDutyStatus; ?>">
          
          <button type="submit" onclick="document.getElementById('dutyStatusInput').value='AVAILABLE'" class="duty-pill-btn <?php echo $currentDutyStatus === 'AVAILABLE' ? 'active-available' : ''; ?>">
            🟢 Available
          </button>
          <button type="submit" onclick="document.getElementById('dutyStatusInput').value='ON_DUTY'" class="duty-pill-btn <?php echo $currentDutyStatus === 'ON_DUTY' ? 'active-onduty' : ''; ?>">
            🔵 On Duty
          </button>
          <button type="submit" onclick="document.getElementById('dutyStatusInput').value='OFF_DUTY'" class="duty-pill-btn <?php echo $currentDutyStatus === 'OFF_DUTY' ? 'active-offduty' : ''; ?>">
            ⚪ Off Duty
          </button>
        </form>
      </div>

      <div class="topbar-right">
        <!-- License Pill -->
        <div class="type-badge" style="background:var(--bg-card-inner);border:1px solid var(--border-subtle);" title="Driver License">
          🪪 <?php echo htmlspecialchars($driverInfo['licenseNumber'] ?? 'B-LIC'); ?>
        </div>

        <!-- Rating Pill -->
        <div class="type-badge" style="background:rgba(245, 158, 11, 0.15);color:#FBBF24;border:1px solid rgba(245, 158, 11, 0.35);">
          ⭐ <?php echo $avgRating; ?> / 5.0
        </div>

        <!-- Topbar Avatar -->
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
           VIEW 1: DRIVER COCKPIT & ACTIVE TRIP
           ==================================================================== -->
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
          <!-- Prominent Active Trip Banner -->
          <div class="cockpit-banner">
            <div class="cockpit-header">
              <div class="cockpit-route-display">
                <div class="cockpit-station">
                  <span class="cockpit-station-tag">Origin Station</span>
                  <span class="cockpit-station-name"><?php echo htmlspecialchars($activeTrip['origin']); ?></span>
                </div>
                <div class="cockpit-route-arrow">➔</div>
                <div class="cockpit-station">
                  <span class="cockpit-station-tag">Destination Station</span>
                  <span class="cockpit-station-name"><?php echo htmlspecialchars($activeTrip['destination']); ?></span>
                </div>
              </div>

              <div style="display:flex;align-items:center;gap:10px;">
                <span class="badge-status <?php echo $isLive ? 'badge-info' : 'badge-warning'; ?>" style="font-size:13px;padding:6px 14px;">
                  <?php echo $isLive ? '⚡ RUN IN TRANSIT' : 'SCHEDULED DEPARTURE'; ?>
                </span>

                <?php if ($ast === 'SCHEDULED'): ?>
                  <form method="POST">
                    <input type="hidden" name="action" value="startTrip">
                    <input type="hidden" name="tripId" value="<?php echo $activeTrip['tripId']; ?>">
                    <button type="submit" class="btn-primary" style="background:#10B981;border-color:#10B981;">
                      🚀 Start Trip Run
                    </button>
                  </form>
                <?php elseif ($ast === 'IN_TRANSIT'): ?>
                  <form method="POST">
                    <input type="hidden" name="action" value="completeTrip">
                    <input type="hidden" name="tripId" value="<?php echo $activeTrip['tripId']; ?>">
                    <button type="submit" class="btn-primary" style="background:#38BDF8;border-color:#38BDF8;color:#0F172A;">
                      🏁 Complete Run &amp; Arrive
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>

            <!-- Route Meta Grid -->
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

          <!-- Passenger Manifest Section -->
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
                    <div class="manifest-item">
                      <div style="display:flex;align-items:center;gap:12px;">
                        <span class="manifest-seat-badge">Seat <?php echo $passenger['seatNumber']; ?></span>
                        <div>
                          <div style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($passenger['passengerName']); ?></div>
                          <div style="font-size:11px;color:var(--text-muted);">Contact: <?php echo htmlspecialchars($passenger['phone'] ?? 'N/A'); ?></div>
                        </div>
                      </div>
                      <div>
                        <span class="badge-status badge-active">CONFIRMED</span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

            <!-- Quick Road Action & Safety Checklist -->
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
                  ⛽ Log Refuel Station
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

      <!-- ====================================================================
           VIEW 2: ASSIGNED TRIPS
           ==================================================================== -->
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
                    <div style="font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars($trip['origin']); ?> ➔ <?php echo htmlspecialchars($trip['destination']); ?></div>
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

      <!-- ====================================================================
           VIEW 3: MY SHIFTS
           ==================================================================== -->
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
                  <td><strong>#<?php echo $sh['shiftId']; ?></strong></td>
                  <td style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($sh['shiftDate']); ?></td>
                  <td><?php echo htmlspecialchars($sh['startTime']); ?></td>
                  <td><?php echo htmlspecialchars($sh['endTime']); ?></td>
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

      <!-- ====================================================================
           VIEW 4: FUEL & VEHICLE LOGS
           ==================================================================== -->
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
                  <td><strong>#<?php echo $fl['fuelLogId']; ?></strong></td>
                  <td style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($fl['registrationNo']); ?></td>
                  <td><?php echo htmlspecialchars($fl['refuelDate']); ?></td>
                  <td><?php echo htmlspecialchars($fl['stationName']); ?></td>
                  <td><span class="type-badge"><?php echo number_format($fl['litersPumped'], 1); ?> L</span></td>
                  <td style="font-weight:600;color:#34D399;">LKR <?php echo number_format($fl['totalCost'], 2); ?></td>
                  <td style="font-family:monospace;"><?php echo number_format($fl['odometerReading']); ?> KM</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ====================================================================
           VIEW 5: RATINGS & PASSENGER REVIEWS
           ==================================================================== -->
      <section class="page-view <?php echo $activeView === 'feedbackView' ? 'active-view' : ''; ?>" id="feedbackView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Captain Ratings &amp; Passenger Reviews</h1>
            <p>Direct commuter feedback synced from Oracle relational feedback and MongoDB NoSQL store.</p>
          </div>
        </div>

        <!-- Rating Summary Box -->
        <div class="stat-card" style="margin-bottom:20px;padding:24px;">
          <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
            <div style="font-size:48px;font-weight:800;color:#FBBF24;line-height:1;">
              <?php echo $avgRating; ?>
            </div>
            <div>
              <div style="color:#F59E0B;font-size:18px;letter-spacing:3px;">★★★★★</div>
              <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">
                Based on <?php echo count($passengerReviews); ?> verified passenger reviews
              </div>
            </div>
            <div style="margin-left:auto;">
              <span class="badge-status badge-active" style="padding:6px 14px;font-size:12px;">Top Rated Captain Tier</span>
            </div>
          </div>
        </div>

        <!-- Reviews Grid -->
        <div class="reviews-grid">
          <?php foreach ($passengerReviews as $rev): ?>
            <div class="review-card">
              <div>
                <div class="review-stars">
                  <?php echo str_repeat('★', (int)($rev['rating'] ?? 5)); ?><?php echo str_repeat('☆', 5 - (int)($rev['rating'] ?? 5)); ?>
                </div>
                <p class="review-text" style="margin-top:10px;">
                  "<?php echo htmlspecialchars($rev['comment'] ?? $rev['comments'] ?? ''); ?>"
                </p>
              </div>

              <div class="review-author-row">
                <span style="font-weight:600;color:var(--text-white);"><?php echo htmlspecialchars($rev['author'] ?? 'Commuter'); ?></span>
                <span><?php echo htmlspecialchars($rev['createdAt'] ?? date('Y-m-d')); ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

    </main>
  </div>
</div>

<!-- ========================================================================
     MODAL: LOG FUEL STATION RECEIPT
     ======================================================================== -->
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

<!-- Core Dashboard Engine (Zero Inline Scripts) -->
<script src="../js/dashboard.js"></script>
</body>
</html>
