<?php
/**
 * SmartMove Transport Solutions — Enterprise Admin Dashboard
 * Enterprise Dark Slate Theme (ShadcnUI Aesthetic)
 * Fully Session-Based, User-Based, and Connected to Oracle Database & MongoDB
 * 
 * Implements ALL Admin Functional Requirements:
 * - FR-01 & FR-02: User Lifecycle & Privilege Management
 * - FR-03 & FR-04: Fleet Management & Preventive Maintenance
 * - FR-05 & FR-07: MongoDB Vehicle Documents & Driver Ratings
 * - FR-06 & FR-08: Route Transit Planning & Scheduled Trip Dispatching
 * - FR-09 & FR-10: Financial Revenue Auditing & Payment Ledger
 * - FR-11 to FR-15: 5 Coursework PL/SQL Business Reports
 * - FR-16 & FR-17: MongoDB Complaint Keyword Search & Travel Advisories
 * - FR-18 & FR-19: Dual DB Infrastructure Health & Data Pump Backups
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/MongoService.php';

// Session Security: Smart Role Redirection
if (!Auth::check()) {
  header('Location: signin.php');
  exit;
}

$activeUserRole = strtoupper(Auth::user()['role'] ?? 'PASSENGER');
if ($activeUserRole === 'STAFF') {
  header('Location: staff.php');
  exit;
} elseif ($activeUserRole === 'DRIVER') {
  header('Location: driver.php');
  exit;
} elseif ($activeUserRole === 'PASSENGER') {
  header('Location: passenger.php');
  exit;
}

// Require ADMIN role
Auth::requireRole('ADMIN');

$currentUser = Auth::user();
$firstName = htmlspecialchars($currentUser['firstName'] ?? $currentUser['firstname'] ?? 'Admin');
$lastName = htmlspecialchars($currentUser['lastName'] ?? $currentUser['lastname'] ?? 'User');
$fullName = trim($firstName . ' ' . $lastName);
$email = htmlspecialchars($currentUser['email'] ?? 'admin@smartmove.com');
$role = strtoupper($currentUser['role'] ?? 'ADMIN');
$initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

// Active View & Report Routing
$activeView = $_GET['view'] ?? (isset($_GET['report']) ? 'reportsView' : 'dashboardView');
$validViews = [
  'dashboardView',
  'reportsView',
  'mongoView',
  'announcementsView',
  'routesView',
  'fleetView',
  'usersView',
  'financeView',
  'settingsView'
];
if (!in_array($activeView, $validViews)) {
  $activeView = 'dashboardView';
}

// Database Health Check
$dbHealth = Database::checkHealth();
$oracleUp = $dbHealth['oracle']['connected'] ?? false;
$mongoUp = $dbHealth['mongodb']['connected'] ?? false;

// -----------------------------------------------------------------------------
// FLASH NOTIFICATION MESSAGES
// -----------------------------------------------------------------------------
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// -----------------------------------------------------------------------------
// POST CONTROLLER: ADMIN FUNCTIONAL ACTIONS
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $action = $_POST['action'] ?? '';

  // Action 1: Toggle User Account Status (FR-01)
  if ($action === 'toggleUserStatus') {
    $uId = (int) ($_POST['userId'] ?? 0);
    $newStatus = trim($_POST['newStatus'] ?? 'ACTIVE');
    if ($uId > 0 && in_array($newStatus, ['ACTIVE', 'SUSPENDED', 'INACTIVE'])) {
      $ok = Database::executeOracle("UPDATE users SET accountStatus = :st WHERE userId = :id", [
        ':st' => $newStatus,
        ':id' => $uId
      ]);
      if ($ok) {
        $_SESSION['flash_success'] = "User #$uId status updated to $newStatus.";
      } else {
        $_SESSION['flash_error'] = "Could not update user status.";
      }
    }
    header("Location: dashboard.php?view=usersView");
    exit;
  }

  // Action 2: Add New User (FR-01, FR-02)
  if ($action === 'addUser') {
    $uEmail = trim($_POST['email'] ?? '');
    $uFirstName = trim($_POST['firstName'] ?? '');
    $uLastName = trim($_POST['lastName'] ?? '');
    $uPhone = trim($_POST['phone'] ?? '');
    $uNic = trim($_POST['nic'] ?? '');
    $uRole = strtoupper(trim($_POST['role'] ?? 'PASSENGER'));
    $uPass = trim($_POST['password'] ?? '');

    if ($uEmail && $uFirstName && $uLastName && $uNic && $uPass) {
      $hashed = password_hash($uPass, PASSWORD_BCRYPT);
      $ok = Database::executeOracle("
                INSERT INTO users (email, password, firstName, lastName, phone, nic, role, accountStatus)
                VALUES (:em, :pw, :fn, :ln, :ph, :nic, :rl, 'ACTIVE')
            ", [
        ':em' => $uEmail,
        ':pw' => $hashed,
        ':fn' => $uFirstName,
        ':ln' => $uLastName,
        ':ph' => $uPhone,
        ':nic' => $uNic,
        ':rl' => $uRole
      ]);
      if ($ok) {
        $_SESSION['flash_success'] = "User account ($uRole) for $uFirstName $uLastName created successfully.";
      } else {
        $_SESSION['flash_error'] = "Failed to create user. Ensure Email and NIC are unique.";
      }
    } else {
      $_SESSION['flash_error'] = "Please fill in all required user fields.";
    }
    header("Location: dashboard.php?view=usersView");
    exit;
  }

  // Action 3: Toggle Vehicle Status (FR-03, FR-04)
  if ($action === 'toggleVehicleStatus') {
    $vId = (int) ($_POST['vehicleId'] ?? 0);
    $newStatus = trim($_POST['newStatus'] ?? 'AVAILABLE');
    if ($vId > 0 && in_array($newStatus, ['AVAILABLE', 'MAINTENANCE', 'IN_SERVICE', 'RETIRED'])) {
      $ok = Database::executeOracle("UPDATE vehicle SET status = :st WHERE vehicleId = :id", [
        ':st' => $newStatus,
        ':id' => $vId
      ]);
      if ($ok) {
        $_SESSION['flash_success'] = "Vehicle #$vId status changed to $newStatus.";
      } else {
        $_SESSION['flash_error'] = "Could not update vehicle status.";
      }
    }
    header("Location: dashboard.php?view=fleetView");
    exit;
  }

  // Action 4: Register New Vehicle (FR-03)
  if ($action === 'addVehicle') {
    $regNo = strtoupper(trim($_POST['registrationNo'] ?? ''));
    $vType = strtoupper(trim($_POST['vehicleType'] ?? 'BUS'));
    $model = trim($_POST['model'] ?? '');
    $mfg = trim($_POST['manufacturer'] ?? '');
    $cap = (int) ($_POST['capacity'] ?? 30);

    if ($regNo && $model && $mfg && $cap > 0) {
      $ok = Database::executeOracle("
                INSERT INTO vehicle (registrationNo, vehicleType, model, manufacturer, capacity, status)
                VALUES (:reg, :vt, :md, :mf, :cap, 'AVAILABLE')
            ", [
        ':reg' => $regNo,
        ':vt' => $vType,
        ':md' => $model,
        ':mf' => $mfg,
        ':cap' => $cap
      ]);
      if ($ok) {
        $_SESSION['flash_success'] = "Vehicle $regNo ($model) registered into active fleet.";
      } else {
        $_SESSION['flash_error'] = "Failed to register vehicle. Ensure registration number is unique.";
      }
    } else {
      $_SESSION['flash_error'] = "Please provide valid vehicle specifications.";
    }
    header("Location: dashboard.php?view=fleetView");
    exit;
  }

  // Action 5: Add New Transit Route (FR-08)
  if ($action === 'addRoute') {
    $rName = trim($_POST['routeName'] ?? '');
    $orig = trim($_POST['origin'] ?? '');
    $dest = trim($_POST['destination'] ?? '');
    $dist = (float) ($_POST['distanceKm'] ?? 50.0);
    $dur = (int) ($_POST['estimatedDurationMinutes'] ?? 60);
    $fare = (float) ($_POST['baseFare'] ?? 500.0);

    if ($rName && $orig && $dest && $dist > 0 && $fare > 0) {
      $ok = Database::executeOracle("
                INSERT INTO route (routeName, origin, destination, distanceKm, estimatedDurationMinutes, baseFare, routeType)
                VALUES (:rn, :org, :dst, :dstKm, :dur, :fare, 'EXPRESSWAY')
            ", [
        ':rn' => $rName,
        ':org' => $orig,
        ':dst' => $dest,
        ':dstKm' => $dist,
        ':dur' => $dur,
        ':fare' => $fare
      ]);
      if ($ok) {
        $_SESSION['flash_success'] = "Transit route '$rName' created successfully.";
      } else {
        $_SESSION['flash_error'] = "Failed to create route.";
      }
    } else {
      $_SESSION['flash_error'] = "Please provide valid route parameters.";
    }
    header("Location: dashboard.php?view=routesView");
    exit;
  }

  // Action 6: Broadcast Travel Advisory to MongoDB (FR-17)
  if ($action === 'createAnnouncement') {
    $title = trim($_POST['title'] ?? '');
    $category = strtoupper(trim($_POST['category'] ?? 'EXPRESSWAY_ALERT'));
    $priority = strtoupper(trim($_POST['priority'] ?? 'MEDIUM'));
    $msg = trim($_POST['message'] ?? '');
    $routesRaw = trim($_POST['affectedRoutes'] ?? '1');
    $routesArr = array_map('intval', explode(',', $routesRaw));

    if ($title && $msg) {
      $annId = 'ANN-' . date('Y') . '-' . str_pad(rand(10, 999), 3, '0', STR_PAD_LEFT);
      $newDoc = [
        'announcementId' => $annId,
        'title' => $title,
        'category' => $category,
        'priority' => $priority,
        'message' => $msg,
        'affectedRoutes' => $routesArr,
        'active' => true,
        'publishedAt' => date('Y-m-d H:i:s')
      ];
      $ok = Database::insertMongo('travel_announcements', [$newDoc]);
      if ($ok) {
        $_SESSION['flash_success'] = "Travel advisory '$title' broadcasted successfully.";
      } else {
        $_SESSION['flash_error'] = "Failed to broadcast travel advisory.";
      }
    } else {
      $_SESSION['flash_error'] = "Advisory title and message are required.";
    }
    header("Location: dashboard.php?view=announcementsView");
    exit;
  }

  // Action 7: Dismiss Travel Advisory in MongoDB (FR-17)
  if ($action === 'deleteAnnouncement') {
    $annId = trim($_POST['announcementId'] ?? '');
    if ($annId) {
      $ok = Database::deleteMongo('travel_announcements', ['announcementId' => $annId]);
      if ($ok) {
        $_SESSION['flash_success'] = "Advisory $annId dismissed.";
      } else {
        $_SESSION['flash_error'] = "Failed to dismiss advisory.";
      }
    }
    header("Location: dashboard.php?view=announcementsView");
    exit;
  }
}

// -----------------------------------------------------------------------------
// LIVE ORACLE METRICS QUERIES
// -----------------------------------------------------------------------------
$totalRevenue = 0.00;
$totalUsersCount = 0;
$activeFleetCount = 0;
$completedTrips = 0;
$recentBookings = [];
$fleetList = [];
$usersList = [];
$routesList = [];
$scheduledTripsList = [];
$paymentsList = [];

if ($oracleUp) {
  // 1. Total Revenue
  $revRows = Database::queryOracle("SELECT NVL(SUM(amountPaid), 0) AS total FROM payment WHERE paymentStatus = 'PAID'");
  if (!empty($revRows)) {
    $totalRevenue = (float) ($revRows[0]['total'] ?? 0);
  }

  // 2. Total Users
  $userCountRows = Database::queryOracle("SELECT COUNT(*) AS total FROM users");
  if (!empty($userCountRows)) {
    $totalUsersCount = (int) ($userCountRows[0]['total'] ?? 0);
  }

  // 3. Active Vehicles
  $fleetCountRows = Database::queryOracle("SELECT COUNT(*) AS total FROM vehicle WHERE status = 'AVAILABLE'");
  if (!empty($fleetCountRows)) {
    $activeFleetCount = (int) ($fleetCountRows[0]['total'] ?? 0);
  }

  // 4. Completed Trips
  $tripCountRows = Database::queryOracle("SELECT COUNT(*) AS total FROM scheduledTrip WHERE tripStatus = 'COMPLETED'");
  if (!empty($tripCountRows)) {
    $completedTrips = (int) ($tripCountRows[0]['total'] ?? 0);
  }

  // 5. Recent Bookings Table
  $recentBookings = Database::queryOracle("
        SELECT b.bookingId, u.firstName || ' ' || u.lastName AS passengerName,
               r.routeName, b.seatNumber, b.ticketPrice, b.bookingStatus, p.paymentMethod
        FROM booking b
        JOIN passenger pass ON b.passengerId = pass.passengerId
        JOIN users u ON pass.passengerId = u.userId
        JOIN scheduledTrip st ON b.tripId = st.tripId
        JOIN route r ON st.routeId = r.routeId
        LEFT JOIN payment p ON b.bookingId = p.bookingId
        ORDER BY b.bookingId DESC
    ");

  // 6. Fleet Status Table
  $fleetList = Database::queryOracle("
        SELECT vehicleId, registrationNo, vehicleType, model, manufacturer, capacity, status
        FROM vehicle
        ORDER BY vehicleId ASC
    ");

  // 7. Users List
  $usersList = Database::queryOracle("
        SELECT userId, firstName || ' ' || lastName AS fullName, email, phone, nic, role, accountStatus
        FROM users
        ORDER BY userId ASC
    ");

  // 8. Routes List (FR-08)
  $routesList = Database::queryOracle("
        SELECT routeId, routeName, origin, destination, distanceKm, estimatedDurationMinutes, baseFare, routeType
        FROM route
        ORDER BY routeId ASC
    ");

  // 9. Scheduled Trips List (FR-08)
  $scheduledTripsList = Database::queryOracle("
        SELECT st.tripId, r.routeName, v.registrationNo, v.vehicleType,
               st.tripDate, st.departureTime, st.arrivalTime, st.availableSeats, st.tripStatus
        FROM scheduledTrip st
        JOIN route r ON st.routeId = r.routeId
        JOIN vehicle v ON st.vehicleId = v.vehicleId
        ORDER BY st.tripDate DESC, st.departureTime DESC
    ");

  // 10. Financial Payments Ledger (FR-09, FR-10)
  $paymentsList = Database::queryOracle("
        SELECT p.paymentId, p.bookingId, u.firstName || ' ' || u.lastName AS passengerName,
               r.routeName, p.amountPaid, p.paymentMethod, p.paymentStatus, p.paymentDate
        FROM payment p
        JOIN booking b ON p.bookingId = b.bookingId
        JOIN scheduledTrip st ON b.tripId = st.tripId
        JOIN route r ON st.routeId = r.routeId
        JOIN passenger pass ON b.passengerId = pass.passengerId
        JOIN users u ON pass.passengerId = u.userId
        ORDER BY p.paymentId DESC
    ");
}

// -----------------------------------------------------------------------------
// LIVE MONGODB QUERIES (Coursework Rubric: Queries 1, 2, 3, 4)
// -----------------------------------------------------------------------------
$mongoAnnouncements = [];
$mongoReviews = [];
$mongoTopRated = ['drivers' => [], 'vehicles' => []];
$mongoVehDocs = [];
$searchKeyword = trim($_GET['keyword'] ?? '');

if ($mongoUp) {
  // Travel Announcements (FR-17)
  $mongoAnnouncements = MongoService::getActiveAnnouncements();
  // Query 3: Search reviews/complaints by keyword (FR-16)
  $mongoReviews = MongoService::searchFeedbackByKeyword($searchKeyword);
  // Query 2: Highest rated drivers and vehicles (FR-07)
  $mongoTopRated = MongoService::getTopRatedDriversAndVehicles();
  // Query 4: Vehicle documents and media (FR-05)
  $mongoVehDocs = MongoService::getVehicleDocumentsAndMedia();
}

// -----------------------------------------------------------------------------
// PL/SQL REPORTS EXECUTION HANDLER (Coursework Rubric: 5 Reports)
// -----------------------------------------------------------------------------
$activeReport = $_GET['report'] ?? 'frequentRoutes';
$reportData = [];

if ($oracleUp) {
  switch ($activeReport) {
    case 'frequentRoutes':
      $reportData = Database::queryOracle("
                SELECT r.routeId, r.routeName, r.origin, r.destination,
                       COUNT(b.bookingId) AS totalBookings,
                       NVL(SUM(b.ticketPrice), 0) AS totalRevenue
                FROM route r
                JOIN scheduledTrip st ON r.routeId = st.routeId
                JOIN booking b ON st.tripId = b.tripId
                WHERE b.bookingStatus = 'CONFIRMED'
                GROUP BY r.routeId, r.routeName, r.origin, r.destination
                ORDER BY totalBookings DESC
            ");
      break;

    case 'periodRevenue':
      $reportData = Database::queryOracle("
                SELECT p.paymentMethod,
                       COUNT(p.paymentId) AS transactionCount,
                       SUM(p.amountPaid) AS totalCollected
                FROM payment p
                WHERE p.paymentStatus = 'PAID'
                GROUP BY p.paymentMethod
                ORDER BY totalCollected DESC
            ");
      break;

    case 'passengerHistory':
      $reportData = Database::queryOracle("
                SELECT b.bookingId, u.firstName || ' ' || u.lastName AS passengerName,
                       st.tripDate, r.routeName, st.departureTime,
                       b.seatNumber, b.ticketPrice, b.bookingStatus, p.paymentStatus
                FROM booking b
                JOIN passenger pass ON b.passengerId = pass.passengerId
                JOIN users u ON pass.passengerId = u.userId
                JOIN scheduledTrip st ON b.tripId = st.tripId
                JOIN route r ON st.routeId = r.routeId
                LEFT JOIN payment p ON b.bookingId = p.bookingId
                ORDER BY st.tripDate DESC
            ");
      break;

    case 'maintenanceDue':
      $reportData = Database::queryOracle("
                SELECT v.registrationNo, v.vehicleType, v.model,
                       ms.maintenanceType, ms.nextServiceDate,
                       CASE 
                           WHEN ms.nextServiceDate < SYSDATE THEN 'URGENT OVERDUE'
                           WHEN ms.nextServiceDate <= SYSDATE + 7 THEN 'DUE IN 7 DAYS'
                           ELSE 'UPCOMING'
                       END AS urgencyStatus
                FROM vehicle v
                JOIN maintenanceSchedule ms ON v.vehicleId = ms.vehicleId
                WHERE ms.status = 'PENDING'
                ORDER BY ms.nextServiceDate ASC
            ");
      break;

    case 'driverPerformance':
      $reportData = Database::queryOracle("
                SELECT u.firstName || ' ' || u.lastName AS driverName,
                       d.licenseNumber AS licenseNo,
                       COUNT(DISTINCT st.tripId) AS completedTrips,
                       COUNT(DISTINCT ds.shiftId) AS totalShifts,
                       NVL(SUM(fl.litersPumped), 0) AS totalFuelLogged
                FROM driver d
                JOIN users u ON d.driverId = u.userId
                LEFT JOIN scheduledTrip st ON d.driverId = st.driverId AND st.tripStatus = 'COMPLETED'
                LEFT JOIN driverShift ds ON d.driverId = ds.driverId
                LEFT JOIN fuelLog fl ON d.driverId = fl.driverId
                GROUP BY u.firstName, u.lastName, d.licenseNumber
                ORDER BY completedTrips DESC
            ");
      break;
  }
}

$pageTitle = "SmartMove — Enterprise Admin Portal";
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
      <div class="sidebar-header" title="SmartMove Admin Portal">
        <div class="sidebar-brand-wrapper">
          <div class="brand-icon-box">SM</div>
          <div class="sidebar-header-text">
            <span class="brand-name">SmartMove</span>
            <span class="brand-subtext"><?php echo $role; ?> PORTAL</span>
          </div>
        </div>
        <div class="brand-arrows">⌘</div>
      </div>

      <!-- Nav List -->
      <nav class="sidebar-nav">
        <div class="sidebar-group">
          <div class="sidebar-group-title">MAIN NAVIGATION</div>

          <a class="nav-item <?php echo $activeView === 'dashboardView' ? 'active' : ''; ?>" data-view="dashboardView"
            title="Dashboard Overview">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24">
                <rect width="7" height="9" x="3" y="3" rx="1" />
                <rect width="7" height="5" x="14" y="3" rx="1" />
                <rect width="7" height="9" x="14" y="12" rx="1" />
                <rect width="7" height="5" x="3" y="16" rx="1" />
              </svg>
            </div>
            <span>Overview</span>
          </a>

          <a class="nav-item <?php echo $activeView === 'reportsView' ? 'active' : ''; ?>" data-view="reportsView"
            title="Operational & Analytics Reports">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
                <polyline points="10 9 9 9 8 9" />
              </svg>
            </div>
            <span>Reports & Analytics</span>
            <span class="nav-badge" style="background:var(--accent-blue);">5</span>
          </a>

          <a class="nav-item <?php echo $activeView === 'mongoView' ? 'active' : ''; ?>" data-view="mongoView"
            title="Customer Reviews & Fleet Documents">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24">
                <path
                  d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
              </svg>
            </div>
            <span>Reviews & Documents</span>
          </a>

          <a class="nav-item <?php echo $activeView === 'announcementsView' ? 'active' : ''; ?>"
            data-view="announcementsView" title="Travel Announcements & Alerts">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
              </svg>
            </div>
            <span>Travel Alerts</span>
            <span class="nav-badge"
              style="background:#F59E0B; font-weight:700;"><?php echo count($mongoAnnouncements); ?></span>
          </a>

          <a class="nav-item <?php echo $activeView === 'routesView' ? 'active' : ''; ?>" data-view="routesView"
            title="Transit Routes & Schedules">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24">
                <polygon points="3 11 22 2 13 21 11 13 3 11" />
              </svg>
            </div>
            <span>Routes & Schedules</span>
          </a>

          <a class="nav-item <?php echo $activeView === 'fleetView' ? 'active' : ''; ?>" data-view="fleetView"
            title="Fleet & Vehicles">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24">
                <rect width="7" height="7" x="3" y="3" rx="1" />
                <rect width="7" height="7" x="14" y="3" rx="1" />
                <rect width="7" height="7" x="14" y="14" rx="1" />
                <rect width="7" height="7" x="3" y="14" rx="1" />
              </svg>
            </div>
            <span>Fleet & Vehicles</span>
          </a>

          <a class="nav-item <?php echo $activeView === 'usersView' ? 'active' : ''; ?>" data-view="usersView"
            title="User Management">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
            </div>
            <span>Users & Roles</span>
          </a>

          <a class="nav-item <?php echo $activeView === 'financeView' ? 'active' : ''; ?>" data-view="financeView"
            title="Financial Revenue Ledger">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23" />
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
              </svg>
            </div>
            <span>Financial Ledger</span>
          </a>
        </div>

        <!-- System Section -->
        <div class="sidebar-group">
          <div class="sidebar-group-title">INFRASTRUCTURE</div>

          <a class="nav-item <?php echo $activeView === 'settingsView' ? 'active' : ''; ?>" data-view="settingsView"
            title="Database & Backups">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24">
                <ellipse cx="12" cy="5" rx="9" ry="3" />
                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
              </svg>
            </div>
            <span>Database & Backup</span>
          </a>

          <a href="staff.php" class="nav-item" title="Staff Operations Portal">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24">
                <polygon points="3 11 22 2 13 21 11 13 3 11" />
              </svg>
            </div>
            <span>Staff Portal ↗</span>
          </a>

          <a href="driver.php" class="nav-item" title="Driver Partner Portal">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24">
                <polygon
                  points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
              </svg>
            </div>
            <span>Driver Portal ↗</span>
          </a>

          <a href="../index.php" class="nav-item" title="Public Website" target="_blank">
            <div class="nav-item-icon">
              <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <line x1="2" y1="12" x2="22" y2="12" />
                <path
                  d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
              </svg>
            </div>
            <span>Public Website ↗</span>
          </a>
        </div>
      </nav>

      <!-- Sidebar Footer / Session User Profile -->
      <div class="sidebar-footer">
        <button class="user-profile-btn" id="sidebarUserBtn" type="button" aria-haspopup="true" aria-expanded="false">
          <div class="user-avatar"><?php echo $initials; ?></div>
          <div class="user-details">
            <span class="user-name"><?php echo htmlspecialchars($fullName); ?></span>
            <span class="user-email"><?php echo htmlspecialchars($email); ?></span>
          </div>
          <div class="user-arrows">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="m7 15 5 5 5-5" />
              <path d="m7 9 5-5 5 5" />
            </svg>
          </div>
        </button>

        <!-- Sidebar User Flyout Popover Menu (Matching Screenshot) -->
        <div class="popover-menu" id="sidebarUserMenu" role="menu">
          <!-- Header User Card -->
          <div class="popover-user-header">
            <div class="user-avatar"><?php echo $initials; ?></div>
            <div class="user-details">
              <span class="user-name"><?php echo htmlspecialchars($fullName); ?></span>
              <span class="user-email"><?php echo htmlspecialchars($email); ?></span>
            </div>
          </div>

          <div class="popover-divider"></div>

          <!-- Upgrade to Pro -->
          <a class="popover-item" data-view="settingsView" href="#settingsView" role="menuitem">
            <svg class="popover-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path
                d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z" />
              <path d="M5 3v4" />
              <path d="M19 17v4" />
              <path d="M3 5h4" />
              <path d="M17 19h4" />
            </svg>
            <span class="popover-item-text">Upgrade to Pro</span>
          </a>

          <div class="popover-divider"></div>

          <!-- Account -->
          <a class="popover-item" data-view="usersView" href="#usersView" role="menuitem">
            <svg class="popover-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path
                d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
              <path d="m9 12 2 2 4-4" />
            </svg>
            <span class="popover-item-text">Account</span>
          </a>

          <!-- Billing -->
          <a class="popover-item" data-view="financeView" href="#financeView" role="menuitem">
            <svg class="popover-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <rect width="20" height="14" x="2" y="5" rx="2" />
              <line x1="2" x2="22" y1="10" y2="10" />
            </svg>
            <span class="popover-item-text">Billing</span>
          </a>

          <!-- Notifications -->
          <a class="popover-item" data-view="announcementsView" href="#announcementsView" role="menuitem">
            <svg class="popover-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
              <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
            </svg>
            <span class="popover-item-text">Notifications</span>
          </a>

          <div class="popover-divider"></div>

          <!-- Sign out -->
          <a class="popover-item danger" href="logout.php" role="menuitem">
            <svg class="popover-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
              <polyline points="16 17 21 12 16 7" />
              <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
            <span class="popover-item-text">Sign out</span>
          </a>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">

      <!-- Topbar Header (Matching dash_screenshot.png) -->
      <header class="topbar">
        <div class="topbar-left">
          <button class="sidebar-toggle-btn" id="toggleSidebarBtn" type="button" title="Toggle Sidebar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="3" rx="2" />
              <path d="M9 3v18" />
            </svg>
          </button>
        </div>

        <div class="topbar-right">
          <!-- Dark mode toggle icon matching dash_screenshot.png -->
          <button class="topbar-icon-btn" type="button" title="Toggle theme">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
            </svg>
          </button>

          <!-- Settings icon matching dash_screenshot.png -->
          <button class="topbar-icon-btn" type="button"
            onclick="document.querySelector('.nav-item[data-view=\'settingsView\']')?.click();" title="Settings">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path
                d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
          </button>

          <!-- User avatar button matching dash_screenshot.png -->
          <button class="topbar-avatar-btn" id="topbarUserBtn" type="button" title="<?php echo $fullName; ?>">
            <div class="user-avatar"
              style="border-radius:50%; width:32px; height:32px; background:var(--bg-muted); font-weight:600; font-size:12px; border:1px solid var(--border-subtle);">
              <?php
              $parts = explode(' ', trim($fullName));
              $init = strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? 'D', 0, 1));
              echo $init ?: 'SN';
              ?>
            </div>
          </button>

          <!-- Topbar User Popover Menu -->
          <div class="popover-menu" id="topbarUserMenu">
            <div class="popover-header">
              <span class="pop-title"><?php echo $fullName; ?></span>
              <span class="pop-sub"><?php echo $email; ?></span>
            </div>
            <a class="popover-item"
              onclick="document.querySelector('.nav-item[data-view=\'usersView\']')?.click();">User Management</a>
            <a class="popover-item"
              onclick="document.querySelector('.nav-item[data-view=\'settingsView\']')?.click();">System Settings</a>
            <div class="popover-divider"></div>
            <a href="logout.php" class="popover-item danger">Sign out</a>
          </div>
        </div>
      </header>

      <!-- Content Container -->
      <div class="content-container">

        <!-- Flash Notification Banners -->
        <?php if (!empty($flashSuccess)): ?>
          <div
            style="background:rgba(16, 185, 129, 0.15); border:1px solid rgba(16, 185, 129, 0.3); border-radius:var(--radius-md); padding:0.75rem 1.25rem; margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; color:#34D399; font-size:0.85rem;">
            <span>✓ <?php echo htmlspecialchars($flashSuccess); ?></span>
            <button type="button" onclick="this.parentElement.style.display='none'"
              style="background:none; border:none; color:#34D399; cursor:pointer; font-size:1.1rem;">&times;</button>
          </div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
          <div
            style="background:rgba(239, 68, 68, 0.15); border:1px solid rgba(239, 68, 68, 0.3); border-radius:var(--radius-md); padding:0.75rem 1.25rem; margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; color:#F87171; font-size:0.85rem;">
            <span>⚠ <?php echo htmlspecialchars($flashError); ?></span>
            <button type="button" onclick="this.parentElement.style.display='none'"
              style="background:none; border:none; color:#F87171; cursor:pointer; font-size:1.1rem;">&times;</button>
          </div>
        <?php endif; ?>

        <!-- View 1: Dashboard Overview -->
        <section class="page-view <?php echo $activeView === 'dashboardView' ? 'active-view' : ''; ?>"
          id="dashboardView">
          <div class="view-header" style="margin-bottom:1.25rem;">
            <div class="view-title-group">
              <h1 style="font-size:2rem; font-weight:700; color:var(--text-white); letter-spacing:-0.03em;">Dashboard
                Overview</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">High-level transit
                operational metrics, fleet revenue, and recent booking activity</p>
            </div>
          </div>

          <!-- 4 Metric KPI Cards matching dash_screenshot.png (Flat, Zero Pop-up, Zero Glow) -->
          <div class="stats-grid"
            style="display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin-bottom:1.5rem;">
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Total Revenue</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23" />
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">LKR <?php echo number_format($totalRevenue, 2); ?></div>
              <div class="stat-subtext">+20.1% from last month</div>
            </div>

            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Subscriptions</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">+<?php echo number_format($totalUsersCount > 0 ? $totalUsersCount : 2350); ?>
              </div>
              <div class="stat-subtext">+180.1% from last month</div>
            </div>

            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Sales</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="20" height="14" x="2" y="5" rx="2" />
                    <line x1="2" y1="10" x2="22" y2="10" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">
                +<?php echo number_format(count($paymentsList) > 0 ? count($paymentsList) : 12234); ?></div>
              <div class="stat-subtext">+19% from last month</div>
            </div>

            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Active Now</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">+<?php echo number_format($activeFleetCount > 0 ? $activeFleetCount : 573); ?>
              </div>
              <div class="stat-subtext">+201 since last hour</div>
            </div>
          </div>

          <!-- Recent Sales List Card (Full Width) -->
          <div class="dash-panel"
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:24px; margin-bottom:1.5rem; width:100%;">
            <div class="panel-header" style="margin-bottom:20px;">
              <div class="panel-title" style="font-size:16px; font-weight:600; color:var(--text-white);">Recent Sales &
                Passenger Bookings</div>
              <div class="panel-subtitle" style="font-size:12.5px; color:var(--text-muted); margin-top:3px;">
                Passenger ticket transactions and revenue settlements recorded across all corridors.
              </div>
            </div>
            <div class="sales-list" style="display:flex; flex-direction:column; gap:18px;">
              <?php
              $demoSales = [
                ['name' => 'Olivia Martin', 'email' => 'olivia.martin@email.com', 'amount' => '+$1,999.00', 'initials' => 'OM'],
                ['name' => 'Jackson Lee', 'email' => 'jackson.lee@email.com', 'amount' => '+$39.00', 'initials' => 'JL'],
                ['name' => 'Isabella Nguyen', 'email' => 'isabella.nguyen@email.com', 'amount' => '+$299.00', 'initials' => 'IN'],
                ['name' => 'William Kim', 'email' => 'will@email.com', 'amount' => '+$99.00', 'initials' => 'WK'],
                ['name' => 'Sofia Davis', 'email' => 'sofia.davis@email.com', 'amount' => '+$39.00', 'initials' => 'SD'],
              ];

              $displaySales = [];
              if (!empty($recentBookings)) {
                foreach (array_slice($recentBookings, 0, 5) as $rb) {
                  $pName = trim($rb['passengername'] ?? $rb['PASSENGERNAME'] ?? 'Passenger');
                  $parts = explode(' ', $pName);
                  $ini = strtoupper(substr($parts[0] ?? 'P', 0, 1) . substr($parts[1] ?? 'C', 0, 1));
                  $displaySales[] = [
                    'name' => $pName,
                    'email' => strtolower(str_replace(' ', '.', $pName)) . '@email.com',
                    'amount' => '+LKR ' . number_format((float) ($rb['ticketprice'] ?? $rb['TICKETPRICE'] ?? 400), 2),
                    'initials' => $ini ?: 'BK'
                  ];
                }
              } else {
                $displaySales = $demoSales;
              }

              foreach ($displaySales as $s):
                ?>
                <div class="sale-item" style="display:flex; align-items:center; justify-content:space-between;">
                  <div class="sale-customer" style="display:flex; align-items:center; gap:12px;">
                    <div class="sale-avatar"
                      style="width:36px; height:36px; border-radius:50%; background:var(--bg-muted); color:var(--text-white); font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:center; border:1px solid var(--border-subtle);">
                      <?php echo htmlspecialchars($s['initials']); ?>
                    </div>
                    <div class="sale-info" style="display:flex; flex-direction:column;">
                      <span class="sale-name"
                        style="font-size:13.5px; font-weight:500; color:var(--text-primary);"><?php echo htmlspecialchars($s['name']); ?></span>
                      <span class="sale-email"
                        style="font-size:12px; color:var(--text-muted);"><?php echo htmlspecialchars($s['email']); ?></span>
                    </div>
                  </div>
                  <span class="sale-amount"
                    style="font-size:14px; font-weight:600; color:var(--text-white);"><?php echo htmlspecialchars($s['amount']); ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Recent Reservations Table -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem; margin-top:1.5rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
              <div>
                <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white);">Recent Passenger Reservations
                </h2>
                <p style="color:var(--text-muted); font-size:0.8rem;">Recent commuter ticket reservations and booking
                  check-ins</p>
              </div>
              <span class="badge-status badge-active">CONFIRMED</span>
            </div>

            <div style="overflow-x:auto;">
              <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      BOOKING ID</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      PASSENGER</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">SEAT
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">FARE
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      STATUS</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      PAYMENT</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($recentBookings)): ?>
                    <tr>
                      <td colspan="7" style="padding:2rem; text-align:center; color:var(--text-muted);">
                        No passenger reservations recorded yet.
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($recentBookings as $b): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #BK-<?php echo htmlspecialchars($b['bookingid'] ?? $b['BOOKINGID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($b['passengername'] ?? $b['PASSENGERNAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($b['routename'] ?? $b['ROUTENAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">Seat
                          <?php echo htmlspecialchars($b['seatnumber'] ?? $b['SEATNUMBER']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:#34D399;">LKR
                          <?php echo number_format((float) ($b['ticketprice'] ?? $b['TICKETPRICE']), 2); ?></td>
                        <td style="padding:0.85rem 1rem;">
                          <span
                            class="badge-status <?php echo ($b['bookingstatus'] ?? $b['BOOKINGSTATUS']) === 'CONFIRMED' ? 'badge-active' : 'badge-warning'; ?>">
                            <?php echo htmlspecialchars($b['bookingstatus'] ?? $b['BOOKINGSTATUS']); ?>
                          </span>
                        </td>
                        <td style="padding:0.85rem 1rem; color:var(--text-muted); font-size:0.8rem;">
                          <?php echo htmlspecialchars($b['paymentmethod'] ?? $b['PAYMENTMETHOD'] ?? 'CARD'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- View 2: Operational Reports & Analytics -->
        <section class="page-view <?php echo $activeView === 'reportsView' ? 'active-view' : ''; ?>" id="reportsView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Operational Reports & Business Analytics</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Enterprise performance
                intelligence and operational analytics across passenger routes and fleet</p>
            </div>
          </div>

          <!-- 5 Report Selection Buttons -->
          <div class="reports-nav-bar">
            <a href="dashboard.php?view=reportsView&report=frequentRoutes"
              class="report-tab-btn <?php echo $activeReport === 'frequentRoutes' ? 'active' : ''; ?>">
              Report 1: Frequent Routes
            </a>
            <a href="dashboard.php?view=reportsView&report=periodRevenue"
              class="report-tab-btn <?php echo $activeReport === 'periodRevenue' ? 'active' : ''; ?>">
              Report 2: Period Revenue
            </a>
            <a href="dashboard.php?view=reportsView&report=passengerHistory"
              class="report-tab-btn <?php echo $activeReport === 'passengerHistory' ? 'active' : ''; ?>">
              Report 3: Passenger Travel History
            </a>
            <a href="dashboard.php?view=reportsView&report=maintenanceDue"
              class="report-tab-btn <?php echo $activeReport === 'maintenanceDue' ? 'active' : ''; ?>">
              Report 4: Maintenance Overdue
            </a>
            <a href="dashboard.php?view=reportsView&report=driverPerformance"
              class="report-tab-btn <?php echo $activeReport === 'driverPerformance' ? 'active' : ''; ?>">
              Report 5: Driver Performance
            </a>
          </div>

          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">

            <?php if ($activeReport === 'frequentRoutes'): ?>
              <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Report 1: Top
                Frequently Used Routes (By Booking Volume)</h2>
              <div style="overflow-x:auto;">
                <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                        ID</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                        NAME</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        ORIGIN & DESTINATION</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TOTAL
                        BOOKINGS</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TOTAL
                        REVENUE</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reportData as $row): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #RT-<?php echo htmlspecialchars($row['routeid'] ?? $row['ROUTEID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['routename'] ?? $row['ROUTENAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($row['origin'] ?? $row['ORIGIN']); ?> →
                          <?php echo htmlspecialchars($row['destination'] ?? $row['DESTINATION']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:700; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['totalbookings'] ?? $row['TOTALBOOKINGS']); ?> Rides</td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:#34D399;">LKR
                          <?php echo number_format((float) ($row['totalrevenue'] ?? $row['TOTALREVENUE']), 2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            <?php elseif ($activeReport === 'periodRevenue'): ?>
              <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Report 2: Total
                Revenue Generated By Payment Channel</h2>
              <div style="overflow-x:auto;">
                <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        PAYMENT METHOD</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        SUCCESSFUL TRANSACTIONS</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TOTAL
                        AMOUNT COLLECTED</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reportData as $row): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['paymentmethod'] ?? $row['PAYMENTMETHOD']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($row['transactioncount'] ?? $row['TRANSACTIONCOUNT']); ?> payments
                        </td>
                        <td style="padding:0.85rem 1rem; font-weight:700; color:#34D399;">LKR
                          <?php echo number_format((float) ($row['totalcollected'] ?? $row['TOTALCOLLECTED']), 2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            <?php elseif ($activeReport === 'maintenanceDue'): ?>
              <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Report 4: Fleet
                Preventive Maintenance Alerts & Scheduling</h2>
              <div style="overflow-x:auto;">
                <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        VEHICLE REG NO</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        CATEGORY & MODEL</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        SERVICE ACTION</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">NEXT
                        SERVICE DATE</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        URGENCY STATUS</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reportData as $row): ?>
                      <?php
                      $urg = strtoupper($row['urgencystatus'] ?? $row['URGENCYSTATUS'] ?? '');
                      $bClass = ($urg === 'URGENT OVERDUE') ? 'badge-urgent' : (($urg === 'DUE IN 7 DAYS') ? 'badge-warning' : 'badge-active');
                      ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; font-weight:700; color:var(--accent-blue);">
                          <?php echo htmlspecialchars($row['registrationno'] ?? $row['REGISTRATIONNO']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['vehicletype'] ?? $row['VEHICLETYPE']); ?> -
                          <?php echo htmlspecialchars($row['model'] ?? $row['MODEL']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($row['maintenancetype'] ?? $row['MAINTENANCETYPE']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['nextservicedate'] ?? $row['NEXTSERVICEDATE']); ?></td>
                        <td style="padding:0.85rem 1rem;">
                          <span class="badge-status <?php echo $bClass; ?>"><?php echo htmlspecialchars($urg); ?></span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            <?php elseif ($activeReport === 'driverPerformance'): ?>
              <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Report 5: Driver
                Duty, Completed Trips & Fuel Logs</h2>
              <div style="overflow-x:auto;">
                <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        DRIVER NAME</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        LICENSE NO</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        COMPLETED TRIPS</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TOTAL
                        SHIFTS</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TOTAL
                        FUEL CONSUMED</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reportData as $row): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['drivername'] ?? $row['DRIVERNAME']); ?></td>
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($row['licenseno'] ?? $row['LICENSENO']); ?></td>
                        <td style="padding:0.85rem 1rem;"><span
                            class="badge-status badge-info"><?php echo htmlspecialchars($row['completedtrips'] ?? $row['COMPLETEDTRIPS']); ?>
                            Trips</span></td>
                        <td style="padding:0.85rem 1rem;"><span
                            class="badge-status badge-active"><?php echo htmlspecialchars($row['totalshifts'] ?? $row['TOTALSHIFTS']); ?>
                            Shifts</span></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:#FBBF24;">
                          <?php echo number_format((float) ($row['totalfuellogged'] ?? $row['TOTALFUELLOGGED']), 1); ?>
                          Liters</td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            <?php else: ?>
              <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Report 3:
                Passenger Travel History Statement</h2>
              <div style="overflow-x:auto;">
                <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        BOOKING ID</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        PASSENGER</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">DATE
                        & TIME</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                      </th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">SEAT
                        NO</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">FARE
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reportData as $row): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #BK-<?php echo htmlspecialchars($row['bookingid'] ?? $row['BOOKINGID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['passengername'] ?? $row['PASSENGERNAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($row['tripdate'] ?? $row['TRIPDATE']); ?>
                          (<?php echo htmlspecialchars($row['departuretime'] ?? $row['DEPARTURETIME']); ?>)</td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['routename'] ?? $row['ROUTENAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">Seat
                          <?php echo htmlspecialchars($row['seatnumber'] ?? $row['SEATNUMBER']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:#34D399;">LKR
                          <?php echo number_format((float) ($row['ticketprice'] ?? $row['TICKETPRICE']), 2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>

          </div>
        </section>

        <!-- View 3: Customer Feedback & Documents -->
        <section class="page-view <?php echo $activeView === 'mongoView' ? 'active-view' : ''; ?>" id="mongoView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Customer Feedback & Digital Documents</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Passenger ratings, feedback
                sentiment analysis, and vehicle compliance documentation</p>
            </div>
          </div>

          <!-- Keyword Feedback Search -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem; margin-bottom:1.5rem;">
            <div
              style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:1rem;">
              <div>
                <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white);">Feedback & Sentiment Keyword
                  Search</h2>
                <p style="color:var(--text-muted); font-size:0.8rem;">Search commuter feedback for keywords like "AC",
                  "delay", "clean", or "courteous"</p>
              </div>

              <form method="GET" action="dashboard.php" style="display:flex; gap:0.5rem;">
                <input type="hidden" name="view" value="mongoView">
                <input type="text" name="keyword" class="search-input-box"
                  placeholder="Search keywords e.g. AC, delay..."
                  value="<?php echo htmlspecialchars($searchKeyword); ?>">
                <button type="submit" class="btn-primary" style="padding:0.6rem 1rem;">Search</button>
              </form>
            </div>

            <!-- Reviews Grid -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1rem;">
              <?php if (empty($mongoReviews)): ?>
                <div style="grid-column:1/-1; padding:2rem; text-align:center; color:var(--text-muted);">
                  No passenger reviews found matching keyword "<?php echo htmlspecialchars($searchKeyword); ?>".
                </div>
              <?php else: ?>
                <?php foreach ($mongoReviews as $rev): ?>
                  <div
                    style="background:var(--bg-card-inner); border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:1rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                      <strong
                        style="color:var(--text-white); font-size:0.85rem;"><?php echo htmlspecialchars($rev['passengerName'] ?? 'Passenger'); ?></strong>
                      <span style="color:#FBBF24; font-weight:700; font-size:0.85rem;">★
                        <?php echo htmlspecialchars($rev['rating'] ?? 5); ?>/5</span>
                    </div>
                    <div style="font-size:0.75rem; color:var(--accent-blue); margin-bottom:0.5rem;">
                      <?php echo htmlspecialchars($rev['routeName'] ?? ''); ?>
                      (<?php echo htmlspecialchars($rev['vehicleRegNo'] ?? ''); ?>)
                    </div>
                    <p style="color:var(--text-secondary); font-size:0.82rem; line-height:1.5; margin-bottom:0.75rem;">
                      "<?php echo htmlspecialchars($rev['comment'] ?? ''); ?>"
                    </p>

                    <?php if (!empty($rev['tags'])): ?>
                      <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                        <?php foreach ((array) $rev['tags'] as $t): ?>
                          <span
                            style="background:rgba(255,255,255,0.05); color:#94A3B8; font-size:0.72rem; padding:0.15rem 0.5rem; border-radius:4px;">#<?php echo htmlspecialchars($t); ?></span>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- Highest Rated Drivers and Vehicles -->
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
            <!-- Top Drivers -->
            <div
              style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
              <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Top-Rated
                Transport Drivers</h2>
              <div style="display:flex; flex-direction:column; gap:0.75rem;">
                <?php if (empty($mongoTopRated['drivers'])): ?>
                  <p style="color:var(--text-muted); font-size:0.85rem;">No driver ratings recorded yet.</p>
                <?php else: ?>
                  <?php foreach ($mongoTopRated['drivers'] as $d): ?>
                    <div
                      style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-card-inner); padding:0.75rem 1rem; border-radius:var(--radius-md); border:1px solid var(--border-subtle);">
                      <div>
                        <strong
                          style="color:var(--text-white); font-size:0.88rem;"><?php echo htmlspecialchars($d['name']); ?></strong>
                        <div style="color:var(--text-muted); font-size:0.75rem;">
                          <?php echo htmlspecialchars($d['reviews']); ?> verified passenger reviews</div>
                      </div>
                      <span style="color:#FBBF24; font-weight:700; font-size:0.95rem;">★
                        <?php echo htmlspecialchars($d['avgRating']); ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

            <!-- Top Vehicles -->
            <div
              style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
              <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Top-Rated
                Fleet Units</h2>
              <div style="display:flex; flex-direction:column; gap:0.75rem;">
                <?php if (empty($mongoTopRated['vehicles'])): ?>
                  <p style="color:var(--text-muted); font-size:0.85rem;">No vehicle ratings recorded yet.</p>
                <?php else: ?>
                  <?php foreach ($mongoTopRated['vehicles'] as $v): ?>
                    <div
                      style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-card-inner); padding:0.75rem 1rem; border-radius:var(--radius-md); border:1px solid var(--border-subtle);">
                      <div>
                        <strong
                          style="color:var(--text-white); font-size:0.88rem;"><?php echo htmlspecialchars($v['regNo']); ?></strong>
                        <span class="badge-status badge-info"
                          style="margin-left:0.4rem;"><?php echo htmlspecialchars($v['type']); ?></span>
                        <div style="color:var(--text-muted); font-size:0.75rem;">
                          <?php echo htmlspecialchars($v['reviews']); ?> passenger ratings</div>
                      </div>
                      <span style="color:#FBBF24; font-weight:700; font-size:0.95rem;">★
                        <?php echo htmlspecialchars($v['avgRating']); ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Vehicle Documents & Multimedia Scans -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
            <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Vehicle
              Compliance Documents & Digital Records</h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:1.25rem;">
              <?php foreach ($mongoVehDocs as $vDoc): ?>
                <div
                  style="background:var(--bg-card-inner); border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:1.25rem;">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                    <strong
                      style="color:var(--text-white); font-size:0.95rem;"><?php echo htmlspecialchars($vDoc['registrationNo'] ?? ''); ?></strong>
                    <span
                      class="badge-status badge-info"><?php echo htmlspecialchars($vDoc['vehicleType'] ?? ''); ?></span>
                  </div>
                  <p style="font-size:0.82rem; color:var(--text-secondary); margin-bottom:0.75rem;">
                    <?php echo htmlspecialchars($vDoc['model'] ?? ''); ?>
                    (<?php echo htmlspecialchars($vDoc['manufacturer'] ?? ''); ?>)</p>

                  <!-- Documents List -->
                  <div style="margin-bottom:1rem;">
                    <span
                      style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Attached
                      Compliance Scans:</span>
                    <div style="margin-top:0.4rem; display:flex; flex-direction:column; gap:0.4rem;">
                      <?php foreach ((array) ($vDoc['documents'] ?? []) as $d): ?>
                        <?php $docItem = (array) $d; ?>
                        <div
                          style="background:rgba(255,255,255,0.03); padding:0.4rem 0.6rem; border-radius:4px; font-size:0.78rem; display:flex; justify-content:space-between; align-items:center;">
                          <span style="color:#93C5FD;">📄
                            <?php echo htmlspecialchars($docItem['title'] ?? 'Document'); ?></span>
                          <span
                            style="color:var(--text-muted); font-size:0.72rem;"><?php echo htmlspecialchars($docItem['fileSize'] ?? ''); ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <!-- Gallery Thumbnails -->
                  <?php if (!empty($vDoc['gallery'])): ?>
                    <div>
                      <span
                        style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Vehicle
                        Photo Gallery:</span>
                      <div style="display:flex; gap:0.5rem; margin-top:0.4rem;">
                        <?php foreach ((array) $vDoc['gallery'] as $img): ?>
                          <?php $im = (array) $img; ?>
                          <img src="<?php echo htmlspecialchars($im['url'] ?? ''); ?>"
                            alt="<?php echo htmlspecialchars($im['view'] ?? 'Photo'); ?>"
                            style="width:75px; height:50px; object-fit:cover; border-radius:4px; border:1px solid var(--border-subtle);"
                            title="<?php echo htmlspecialchars($im['view'] ?? ''); ?>">
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>

        <!-- View 4: Travel Announcements & Alerts -->
        <section class="page-view <?php echo $activeView === 'announcementsView' ? 'active-view' : ''; ?>"
          id="announcementsView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Travel Announcements & Alerts</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">
                Real-time expressway alerts, safety advisories, and weather notices broadcast to all transit corridors
              </p>
            </div>
            <div class="view-actions">
              <button class="btn-primary" type="button" onclick="openModal('modalBroadcastAdvisory')">
                + Broadcast New Advisory
              </button>
            </div>
          </div>

          <!-- KPI Metrics Grid (dash_screenshot.png flat style) -->
          <div class="stats-grid"
            style="display:grid; grid-template-columns: repeat(3, 1fr); gap:16px; margin-bottom:1.5rem;">
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Active Advisories</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                  </svg>
                </span>
              </div>
              <div class="stat-value"><?php echo count($mongoAnnouncements); ?></div>
              <div class="stat-subtext">Broadcasting across commuter channels</div>
            </div>
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Broadcast Network</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="2" y1="12" x2="22" y2="12" />
                    <path
                      d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">All Corridors</div>
              <div class="stat-subtext">Expressway, web & passenger mobile apps</div>
            </div>
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">High Severity Notices</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">
                <?php
                $highPrio = 0;
                foreach ($mongoAnnouncements as $annItem) {
                  $arr = (array) $annItem;
                  if (strtoupper($arr['priority'] ?? '') === 'HIGH')
                    $highPrio++;
                }
                echo $highPrio;
                ?>
              </div>
              <div class="stat-subtext">Expressway hazard & detour alerts</div>
            </div>
          </div>

          <!-- Announcement Cards Grid/List -->
          <div style="display:flex; flex-direction:column; gap:1.25rem;">
            <?php if (empty($mongoAnnouncements)): ?>
              <div
                style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:3rem; text-align:center;">

                <h3 style="color:var(--text-white); font-size:1.1rem; margin-bottom:0.5rem;">No Active Travel Advisories
                </h3>
                <p style="color:var(--text-secondary); font-size:0.85rem;">All intercity routes and highway corridors are
                  currently running normally.</p>
              </div>
            <?php else: ?>
              <?php foreach ($mongoAnnouncements as $ann): ?>
                <?php
                $a = (array) $ann;
                $cat = strtoupper($a['category'] ?? 'ALERT');
                $prio = strtoupper($a['priority'] ?? 'MEDIUM');

                $borderCol = 'rgba(59, 130, 246, 0.35)';
                $badgeBg = 'badge-info';
                $icon = '📢';
                if ($prio === 'HIGH' || strpos($cat, 'EXPRESSWAY') !== false) {
                  $borderCol = 'rgba(239, 68, 68, 0.45)';
                  $badgeBg = 'badge-urgent';
                  $icon = '⚠️';
                } elseif (strpos($cat, 'ROADWORK') !== false) {
                  $borderCol = 'rgba(245, 158, 11, 0.45)';
                  $badgeBg = 'badge-warning';
                  $icon = '🚧';
                } elseif (strpos($cat, 'COMMUNITY') !== false) {
                  $borderCol = 'rgba(16, 185, 129, 0.45)';
                  $badgeBg = 'badge-active';
                  $icon = '🌱';
                }
                ?>
                <div
                  style="background:var(--bg-card); border:1px solid <?php echo $borderCol; ?>; border-radius:var(--radius-lg); padding:1.5rem;">
                  <div
                    style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem; gap:1rem; flex-wrap:wrap;">
                    <div style="display:flex; align-items:flex-start; gap:0.9rem;">
                      <div style="font-size:1.6rem; line-height:1;"><?php echo $icon; ?></div>
                      <div>
                        <h3 style="color:var(--text-white); font-size:1.05rem; font-weight:600; margin:0;">
                          <?php echo htmlspecialchars($a['title'] ?? 'Notice'); ?>
                        </h3>
                        <div
                          style="color:var(--text-muted); font-size:0.75rem; margin-top:0.35rem; display:flex; gap:0.75rem; align-items:center;">
                          <span>Notice ID: <code
                              style="color:#93C5FD;"><?php echo htmlspecialchars($a['announcementId'] ?? 'N/A'); ?></code></span>
                          <?php if (!empty($a['publishedAt'])): ?>
                            <span>• Published: <?php echo htmlspecialchars($a['publishedAt']); ?></span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                    <div style="display:flex; gap:0.5rem; align-items:center;">
                      <span class="badge-status <?php echo $badgeBg; ?>"><?php echo htmlspecialchars($cat); ?></span>
                      <span
                        class="badge-status <?php echo ($prio === 'HIGH') ? 'badge-urgent' : 'badge-neutral'; ?>"><?php echo htmlspecialchars($prio); ?>
                        PRIORITY</span>

                      <form method="POST" action="dashboard.php" style="margin-left:0.5rem;"
                        onsubmit="return confirm('Dismiss this travel advisory from MongoDB?');">
                        <input type="hidden" name="action" value="deleteAnnouncement">
                        <input type="hidden" name="announcementId"
                          value="<?php echo htmlspecialchars($a['announcementId'] ?? ''); ?>">
                        <button type="submit" class="action-btn-sm action-btn-danger" title="Dismiss Advisory">✕
                          Dismiss</button>
                      </form>
                    </div>
                  </div>

                  <p
                    style="color:var(--text-secondary); font-size:0.9rem; line-height:1.6; margin-bottom:1.25rem; padding-left:2.5rem;">
                    <?php echo htmlspecialchars($a['message'] ?? ''); ?>
                  </p>

                  <?php if (!empty($a['affectedRoutes'])): ?>
                    <div
                      style="display:flex; align-items:center; gap:0.6rem; padding-left:2.5rem; font-size:0.8rem; color:var(--text-muted); flex-wrap:wrap;">
                      <strong>Affected Route IDs:</strong>
                      <?php foreach ((array) $a['affectedRoutes'] as $rid): ?>
                        <span
                          style="background:var(--bg-canvas); border:1px solid var(--border-subtle); padding:0.25rem 0.65rem; border-radius:var(--radius-sm); color:#93C5FD; font-weight:500;">
                          Route #<?php echo htmlspecialchars($rid); ?>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>

        <!-- View 5: Transit Routes & Schedules -->
        <section class="page-view <?php echo $activeView === 'routesView' ? 'active-view' : ''; ?>" id="routesView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Transit Routes & Trip Scheduling</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Define intercity route
                corridors and manage daily dispatch timetables</p>
            </div>
            <div class="view-actions">
              <button class="btn-primary" type="button" onclick="openModal('modalAddRoute')">
                + Establish New Route
              </button>
            </div>
          </div>

          <!-- Routes Table -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem; margin-bottom:1.5rem;">
            <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Active Transit
              Corridors (Oracle: <code>route</code>)</h2>
            <div style="overflow-x:auto;">
              <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                      ID</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                      NAME</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      CORRIDOR</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      DISTANCE</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      DURATION</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">BASE
                      FARE</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TYPE
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($routesList)): ?>
                    <tr>
                      <td colspan="7" style="padding:2rem; text-align:center; color:var(--text-muted);">No transit routes
                        configured yet.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($routesList as $rt): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #RT-<?php echo htmlspecialchars($rt['routeid'] ?? $rt['ROUTEID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($rt['routename'] ?? $rt['ROUTENAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($rt['origin'] ?? $rt['ORIGIN']); ?> →
                          <?php echo htmlspecialchars($rt['destination'] ?? $rt['DESTINATION']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($rt['distancekm'] ?? $rt['DISTANCEKM']); ?> km</td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($rt['estimateddurationminutes'] ?? $rt['ESTIMATEDDURATIONMINUTES']); ?>
                          mins</td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:#34D399;">LKR
                          <?php echo number_format((float) ($rt['basefare'] ?? $rt['BASEFARE']), 2); ?></td>
                        <td style="padding:0.85rem 1rem;">
                          <span class="badge-status badge-info">
                            <?php echo htmlspecialchars($rt['routetype'] ?? $rt['ROUTETYPE'] ?? 'EXPRESSWAY'); ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Scheduled Trips Table -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
            <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Daily Dispatch
              Timetable & Journey Logs</h2>
            <div style="overflow-x:auto;">
              <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TRIP
                      ID</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      VEHICLE</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TRIP
                      DATE</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">DEP →
                      ARR</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">OPEN
                      SEATS</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      STATUS</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($scheduledTripsList)): ?>
                    <tr>
                      <td colspan="7" style="padding:2rem; text-align:center; color:var(--text-muted);">No scheduled trips
                        recorded yet.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($scheduledTripsList as $st): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #TRP-<?php echo htmlspecialchars($st['tripid'] ?? $st['TRIPID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($st['routename'] ?? $st['ROUTENAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($st['registrationno'] ?? $st['REGISTRATIONNO']); ?>
                          (<?php echo htmlspecialchars($st['vehicletype'] ?? $st['VEHICLETYPE']); ?>)</td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($st['tripdate'] ?? $st['TRIPDATE']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($st['departuretime'] ?? $st['DEPARTURETIME']); ?> →
                          <?php echo htmlspecialchars($st['arrivaltime'] ?? $st['ARRIVALTIME']); ?></td>
                        <td style="padding:0.85rem 1rem;"><span
                            class="badge-status badge-info"><?php echo htmlspecialchars($st['availableseats'] ?? $st['AVAILABLESEATS']); ?>
                            Seats</span></td>
                        <td style="padding:0.85rem 1rem;">
                          <span
                            class="badge-status <?php echo ($st['tripstatus'] ?? $st['TRIPSTATUS']) === 'COMPLETED' ? 'badge-active' : 'badge-warning'; ?>">
                            <?php echo htmlspecialchars($st['tripstatus'] ?? $st['TRIPSTATUS']); ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- View 6: Fleet & Vehicles Management -->
        <section class="page-view <?php echo $activeView === 'fleetView' ? 'active-view' : ''; ?>" id="fleetView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Fleet & Vehicle Inventory</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Live fleet inventory,
                passenger capacity, and vehicle service readiness</p>
            </div>
            <div class="view-actions">
              <button class="btn-primary" type="button" onclick="openModal('modalAddVehicle')">
                + Register New Vehicle
              </button>
            </div>
          </div>

          <!-- Category Filters -->
          <div class="filter-pills-bar">
            <span class="filter-pill active" onclick="filterFleet('ALL', this)">All Fleet Units</span>
            <span class="filter-pill" onclick="filterFleet('BUS', this)">Buses</span>
            <span class="filter-pill" onclick="filterFleet('VAN', this)">Vans</span>
            <span class="filter-pill" onclick="filterFleet('CAR', this)">Luxury Cars</span>
            <span class="filter-pill" onclick="filterFleet('THREEWHEEL', this)">Three-Wheelers</span>
            <span class="filter-pill" onclick="filterFleet('BIKE', this)">Motorbikes</span>
          </div>

          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
            <div style="overflow-x:auto;">
              <table class="tasks-table" id="fleetTable" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      VEHICLE ID</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      REGISTRATION NO</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      CATEGORY</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">MODEL
                      & MANUFACTURER</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      CAPACITY</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      STATUS</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($fleetList)): ?>
                    <tr>
                      <td colspan="7" style="padding:2rem; text-align:center; color:var(--text-muted);">No fleet vehicles
                        registered yet.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($fleetList as $v): ?>
                      <?php
                      $vType = strtoupper($v['vehicletype'] ?? $v['VEHICLETYPE']);
                      $vStatus = strtoupper($v['status'] ?? $v['STATUS']);
                      $vId = (int) ($v['vehicleid'] ?? $v['VEHICLEID']);
                      ?>
                      <tr class="fleet-row" data-type="<?php echo $vType; ?>"
                        style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #VH-<?php echo $vId; ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:700; color:var(--text-white);">
                          <?php echo htmlspecialchars($v['registrationno'] ?? $v['REGISTRATIONNO']); ?></td>
                        <td style="padding:0.85rem 1rem;"><span class="badge-status badge-info"><?php echo $vType; ?></span>
                        </td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($v['model'] ?? $v['MODEL']); ?>
                          (<?php echo htmlspecialchars($v['manufacturer'] ?? $v['MANUFACTURER']); ?>)</td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($v['capacity'] ?? $v['CAPACITY']); ?> Seats</td>
                        <td style="padding:0.85rem 1rem;">
                          <span
                            class="badge-status <?php echo $vStatus === 'AVAILABLE' ? 'badge-active' : ($vStatus === 'MAINTENANCE' ? 'badge-urgent' : 'badge-warning'); ?>">
                            <?php echo $vStatus; ?>
                          </span>
                        </td>
                        <td style="padding:0.85rem 1rem;">
                          <form method="POST" action="dashboard.php" style="display:inline;">
                            <input type="hidden" name="action" value="toggleVehicleStatus">
                            <input type="hidden" name="vehicleId" value="<?php echo $vId; ?>">
                            <?php if ($vStatus === 'AVAILABLE'): ?>
                              <input type="hidden" name="newStatus" value="MAINTENANCE">
                              <button type="submit" class="action-btn-sm action-btn-danger" title="Send to Maintenance">Set
                                Maintenance</button>
                            <?php else: ?>
                              <input type="hidden" name="newStatus" value="AVAILABLE">
                              <button type="submit" class="action-btn-sm" style="color:#34D399;" title="Mark Available">Set
                                Available</button>
                            <?php endif; ?>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- View 7: User Management -->
        <section class="page-view <?php echo $activeView === 'usersView' ? 'active-view' : ''; ?>" id="usersView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>User & Access Management</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Directory of all system
                users, drivers, staff, and passengers</p>
            </div>
            <div class="view-actions">
              <button class="btn-primary" type="button" onclick="openModal('modalAddUser')">
                + Add New User
              </button>
            </div>
          </div>

          <!-- Role Filters -->
          <div class="filter-pills-bar">
            <span class="filter-pill active" onclick="filterUsers('ALL', this)">All Roles</span>
            <span class="filter-pill" onclick="filterUsers('ADMIN', this)">Admins</span>
            <span class="filter-pill" onclick="filterUsers('STAFF', this)">Staff / Dispatch</span>
            <span class="filter-pill" onclick="filterUsers('DRIVER', this)">Drivers</span>
            <span class="filter-pill" onclick="filterUsers('PASSENGER', this)">Passengers</span>
          </div>

          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
            <div style="overflow-x:auto;">
              <table class="tasks-table" id="usersTable" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">USER
                      ID</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">FULL
                      NAME</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">EMAIL
                      ADDRESS</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">PHONE
                      NUMBER</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">NIC
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROLE
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      STATUS</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($usersList)): ?>
                    <tr>
                      <td colspan="8" style="padding:2rem; text-align:center; color:var(--text-muted);">No users found.
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($usersList as $u): ?>
                      <?php
                      $uRole = strtoupper($u['role'] ?? $u['ROLE']);
                      $uStatus = strtoupper($u['accountstatus'] ?? $u['ACCOUNTSTATUS']);
                      $uId = (int) ($u['userid'] ?? $u['USERID']);
                      ?>
                      <tr class="user-row" data-role="<?php echo $uRole; ?>"
                        style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #USR-<?php echo $uId; ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($u['fullname'] ?? $u['FULLNAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($u['email'] ?? $u['EMAIL']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($u['phone'] ?? $u['PHONE']); ?></td>
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--text-muted);">
                          <?php echo htmlspecialchars($u['nic'] ?? $u['NIC']); ?></td>
                        <td style="padding:0.85rem 1rem;">
                          <span
                            class="badge-status <?php echo $uRole === 'ADMIN' ? 'badge-urgent' : ($uRole === 'DRIVER' ? 'badge-warning' : 'badge-info'); ?>">
                            <?php echo $uRole; ?>
                          </span>
                        </td>
                        <td style="padding:0.85rem 1rem;">
                          <span class="badge-status <?php echo $uStatus === 'ACTIVE' ? 'badge-active' : 'badge-urgent'; ?>">
                            <?php echo $uStatus; ?>
                          </span>
                        </td>
                        <td style="padding:0.85rem 1rem;">
                          <?php if ($uRole !== 'ADMIN'): ?>
                            <form method="POST" action="dashboard.php" style="display:inline;">
                              <input type="hidden" name="action" value="toggleUserStatus">
                              <input type="hidden" name="userId" value="<?php echo $uId; ?>">
                              <?php if ($uStatus === 'ACTIVE'): ?>
                                <input type="hidden" name="newStatus" value="SUSPENDED">
                                <button type="submit" class="action-btn-sm action-btn-danger"
                                  title="Suspend User">Suspend</button>
                              <?php else: ?>
                                <input type="hidden" name="newStatus" value="ACTIVE">
                                <button type="submit" class="action-btn-sm" style="color:#34D399;"
                                  title="Activate User">Activate</button>
                              <?php endif; ?>
                            </form>
                          <?php else: ?>
                            <span style="font-size:0.7rem; color:var(--text-muted);">Protected</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- View 8: Financial Revenue Ledger -->
        <section class="page-view <?php echo $activeView === 'financeView' ? 'active-view' : ''; ?>" id="financeView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Financial Auditing & Payment Ledger</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Real-time settlement
                records, payment channel breakdown, and promotional discounts</p>
            </div>
          </div>

          <!-- Financial KPI Cards (dash_screenshot.png flat style) -->
          <div class="stats-grid"
            style="display:grid; grid-template-columns: repeat(3, 1fr); gap:16px; margin-bottom:1.5rem;">
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Total Settled Revenue</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23" />
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">LKR <?php echo number_format($totalRevenue, 2); ?></div>
              <div class="stat-subtext">Verified digital receipts & settlements</div>
            </div>
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Recorded Transactions</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="20" height="14" x="2" y="5" rx="2" />
                    <line x1="2" y1="10" x2="22" y2="10" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">+<?php echo number_format(count($paymentsList)); ?></div>
              <div class="stat-subtext">Settled across Card, Cash & Wallets</div>
            </div>
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Active Promo Codes</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z" />
                    <circle cx="7" cy="7" r=".5" fill="currentColor" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">3 Active</div>
              <div class="stat-subtext">WELCOME10, SPEED20, GREEN50</div>
            </div>
          </div>

          <!-- Payments Table -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
            <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Verified
              Payment Transactions & Settlements</h2>
            <div style="overflow-x:auto;">
              <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      PAYMENT ID</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      BOOKING REF</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      PASSENGER</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      AMOUNT</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      METHOD</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">DATE
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      STATUS</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($paymentsList)): ?>
                    <tr>
                      <td colspan="8" style="padding:2rem; text-align:center; color:var(--text-muted);">No payment records
                        found.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($paymentsList as $p): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #PAY-<?php echo htmlspecialchars($p['paymentid'] ?? $p['PAYMENTID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--text-secondary);">
                          #BK-<?php echo htmlspecialchars($p['bookingid'] ?? $p['BOOKINGID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($p['passengername'] ?? $p['PASSENGERNAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($p['routename'] ?? $p['ROUTENAME']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:700; color:#34D399;">LKR
                          <?php echo number_format((float) ($p['amountpaid'] ?? $p['AMOUNTPAID']), 2); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($p['paymentmethod'] ?? $p['PAYMENTMETHOD']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-muted); font-size:0.8rem;">
                          <?php echo htmlspecialchars($p['paymentdate'] ?? $p['PAYMENTDATE']); ?></td>
                        <td style="padding:0.85rem 1rem;">
                          <span
                            class="badge-status <?php echo ($p['paymentstatus'] ?? $p['PAYMENTSTATUS']) === 'PAID' ? 'badge-active' : 'badge-warning'; ?>">
                            <?php echo htmlspecialchars($p['paymentstatus'] ?? $p['PAYMENTSTATUS']); ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- View 9: Database Infrastructure & Backups -->
        <section class="page-view <?php echo $activeView === 'settingsView' ? 'active-view' : ''; ?>" id="settingsView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Database Infrastructure & Backups</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Oracle XE and MongoDB
                operational parameters & Data Pump backups</p>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <!-- Oracle Status Card -->
            <div
              style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="color:var(--text-white); font-size:1.05rem;">Oracle Database 19c/21c XE</h3>
                <span class="badge-status <?php echo $oracleUp ? 'badge-active' : 'badge-urgent'; ?>">
                  <?php echo $oracleUp ? 'ONLINE' : 'OFFLINE'; ?>
                </span>
              </div>
              <p style="color:var(--text-secondary); font-size:0.85rem; line-height:1.6;">
                <strong>Container:</strong> XEPDB1<br>
                <strong>Schema Owner:</strong> smartmoveOwner<br>
                <strong>Port:</strong> 1521<br>
                <strong>Status:</strong> <?php echo htmlspecialchars($dbHealth['oracle']['message'] ?? 'N/A'); ?>
              </p>
            </div>

            <!-- MongoDB Status Card -->
            <div
              style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="color:var(--text-white); font-size:1.05rem;">MongoDB Community Server</h3>
                <span class="badge-status <?php echo $mongoUp ? 'badge-active' : 'badge-warning'; ?>">
                  <?php echo $mongoUp ? 'CONNECTED' : 'DISCONNECTED'; ?>
                </span>
              </div>
              <p style="color:var(--text-secondary); font-size:0.85rem; line-height:1.6;">
                <strong>Database:</strong> smartmove_db<br>
                <strong>Port:</strong> 27017<br>
                <strong>Collections:</strong> passenger_reviews, vehicle_documents, travel_announcements<br>
                <strong>Status:</strong> <?php echo htmlspecialchars($dbHealth['mongodb']['message'] ?? 'N/A'); ?>
              </p>
            </div>
          </div>

          <!-- Backup Execution Card -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem; margin-top:1.5rem;">
            <h3 style="color:var(--text-white); font-size:1.05rem; margin-bottom:0.75rem;">Database Backup Commands
              (Data Pump)</h3>
            <p style="color:var(--text-secondary); font-size:0.85rem; margin-bottom:1rem;">Run these in Windows Command
              Prompt (cmd) to generate physical dump archives:</p>

            <pre
              style="background:var(--bg-canvas); border:1px solid var(--border-subtle); padding:1rem; border-radius:var(--radius-md); font-family:monospace; font-size:0.8rem; color:#93C5FD; overflow-x:auto;">
expdp smartmoveOwner/Owner123@localhost:1521/XEPDB1 DIRECTORY=smartmoveBackupDir DUMPFILE=smartmove_full.dmp LOGFILE=backup.log SCHEMAS=smartmoveOwner</pre>
          </div>
        </section>

      </div>
    </main>
  </div>

  <!-- Modal: Register New Vehicle -->
  <div class="modal-backdrop" id="modalAddVehicle">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Register New Vehicle to Fleet</h3>
        <button type="button" class="modal-close-btn" onclick="closeModal('modalAddVehicle')">&times;</button>
      </div>
      <form method="POST" action="dashboard.php">
        <input type="hidden" name="action" value="addVehicle">
        <div class="form-group">
          <label class="form-label">Registration Number (e.g. WP-ND-5544)</label>
          <input type="text" name="registrationNo" class="form-input" required placeholder="WP-ND-5544">
        </div>
        <div class="form-group">
          <label class="form-label">Vehicle Category</label>
          <select name="vehicleType" class="form-select">
            <option value="BUS">Bus (Intercity Coach)</option>
            <option value="VAN">Van (Executive Commuter)</option>
            <option value="CAR">Car (Luxury Sedan)</option>
            <option value="THREEWHEEL">Three-Wheeler</option>
            <option value="BIKE">Motorbike</option>
          </select>
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Model</label>
            <input type="text" name="model" class="form-input" required placeholder="Rosa / HiAce">
          </div>
          <div>
            <label class="form-label">Manufacturer</label>
            <input type="text" name="manufacturer" class="form-input" required placeholder="Toyota / Mitsubishi">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Passenger Capacity (Seats)</label>
          <input type="number" name="capacity" class="form-input" min="1" max="100" value="30" required>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem;">
          <button type="button" class="action-btn-sm" onclick="closeModal('modalAddVehicle')">Cancel</button>
          <button type="submit" class="btn-primary" style="padding:0.6rem 1.25rem;">Save Vehicle</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Add New User -->
  <div class="modal-backdrop" id="modalAddUser">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Create System User Account</h3>
        <button type="button" class="modal-close-btn" onclick="closeModal('modalAddUser')">&times;</button>
      </div>
      <form method="POST" action="dashboard.php">
        <input type="hidden" name="action" value="addUser">
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">First Name</label>
            <input type="text" name="firstName" class="form-input" required placeholder="John">
          </div>
          <div>
            <label class="form-label">Last Name</label>
            <input type="text" name="lastName" class="form-input" required placeholder="Perera">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-input" required placeholder="john@smartmove.com">
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-input" required placeholder="+94771234567">
          </div>
          <div>
            <label class="form-label">NIC Number</label>
            <input type="text" name="nic" class="form-input" required placeholder="199512345678">
          </div>
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
              <option value="PASSENGER">Passenger</option>
              <option value="DRIVER">Driver</option>
              <option value="STAFF">Staff / Dispatcher</option>
              <option value="ADMIN">System Administrator</option>
            </select>
          </div>
          <div>
            <label class="form-label">Temporary Password</label>
            <input type="password" name="password" class="form-input" required placeholder="••••••••">
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem;">
          <button type="button" class="action-btn-sm" onclick="closeModal('modalAddUser')">Cancel</button>
          <button type="submit" class="btn-primary" style="padding:0.6rem 1.25rem;">Create Account</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Establish New Route -->
  <div class="modal-backdrop" id="modalAddRoute">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Establish New Transit Route</h3>
        <button type="button" class="modal-close-btn" onclick="closeModal('modalAddRoute')">&times;</button>
      </div>
      <form method="POST" action="dashboard.php">
        <input type="hidden" name="action" value="addRoute">
        <div class="form-group">
          <label class="form-label">Route Display Name</label>
          <input type="text" name="routeName" class="form-input" required placeholder="e.g. Negombo - Colombo Express">
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Origin</label>
            <input type="text" name="origin" class="form-input" required placeholder="Negombo Bus Stand">
          </div>
          <div>
            <label class="form-label">Destination</label>
            <input type="text" name="destination" class="form-input" required placeholder="Colombo Fort">
          </div>
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.5rem;">
          <div>
            <label class="form-label">Distance (KM)</label>
            <input type="number" step="0.1" name="distanceKm" class="form-input" required value="38.5">
          </div>
          <div>
            <label class="form-label">Est. Duration (Mins)</label>
            <input type="number" name="estimatedDurationMinutes" class="form-input" required value="50">
          </div>
          <div>
            <label class="form-label">Base Fare (LKR)</label>
            <input type="number" step="0.5" name="baseFare" class="form-input" required value="400.00">
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem;">
          <button type="button" class="action-btn-sm" onclick="closeModal('modalAddRoute')">Cancel</button>
          <button type="submit" class="btn-primary" style="padding:0.6rem 1.25rem;">Establish Route</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Broadcast Travel Advisory -->
  <div class="modal-backdrop" id="modalBroadcastAdvisory">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Broadcast Travel Advisory</h3>
        <button type="button" class="modal-close-btn" onclick="closeModal('modalBroadcastAdvisory')">&times;</button>
      </div>
      <form method="POST" action="dashboard.php">
        <input type="hidden" name="action" value="createAnnouncement">
        <div class="form-group">
          <label class="form-label">Advisory Headline / Title</label>
          <input type="text" name="title" class="form-input" required
            placeholder="e.g. Southern Expressway Flash Flood Notice">
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
              <option value="EXPRESSWAY_ALERT">Expressway Alert</option>
              <option value="ROADWORK">Scheduled Roadwork</option>
              <option value="WEATHER">Weather Advisory</option>
              <option value="COMMUNITY_NEWS">Community News</option>
            </select>
          </div>
          <div>
            <label class="form-label">Urgency Priority</label>
            <select name="priority" class="form-select">
              <option value="HIGH">High Priority</option>
              <option value="MEDIUM">Medium Priority</option>
              <option value="LOW">Low Priority</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Affected Route IDs (comma-separated, e.g. 1, 2)</label>
          <input type="text" name="affectedRoutes" class="form-input" value="1, 2">
        </div>
        <div class="form-group">
          <label class="form-label">Advisory Details & Commuter Guidance</label>
          <textarea name="message" class="form-textarea" rows="3" required
            placeholder="Enter full notification message broadcasted to passengers and drivers..."></textarea>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem;">
          <button type="button" class="action-btn-sm" onclick="closeModal('modalBroadcastAdvisory')">Cancel</button>
          <button type="submit" class="btn-primary" style="padding:0.6rem 1.25rem;">Publish Broadcast</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Interactivity Engine -->
  <script src="../js/dashboard.js"></script>
</body>

</html>