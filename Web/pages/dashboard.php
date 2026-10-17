<?php
// Admin Dashboard

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/MongoService.php';

// Auth check
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

// Require admin
Auth::requireRole('ADMIN');

$currentUser = Auth::user();
$firstName = htmlspecialchars($currentUser['firstName'] ?? $currentUser['firstname'] ?? 'Admin');
$lastName = htmlspecialchars($currentUser['lastName'] ?? $currentUser['lastname'] ?? 'User');
$fullName = trim($firstName . ' ' . $lastName);
$email = htmlspecialchars($currentUser['email'] ?? 'admin@smartmove.com');
$role = strtoupper($currentUser['role'] ?? 'ADMIN');
$initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

require_once __DIR__ . '/../components/dashboard/profile_controller.php';

// View routing
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
  'settingsView',
  'profileView'
];
if (!in_array($activeView, $validViews)) {
  $activeView = 'dashboardView';
}

// Health check
$dbHealth = Database::checkHealth();
$oracleUp = $dbHealth['oracle']['connected'] ?? false;
$mongoUp = $dbHealth['mongodb']['connected'] ?? false;

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Handle post actions
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $action = $_POST['action'] ?? '';

  // Toggle user status
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

  // Confirm payment PL/SQL
  if ($action === 'confirmPayment') {
    $payId = (int)($_POST['paymentId'] ?? 0);
    if ($payId > 0) {
      $res = Database::callConfirmPayment($payId, $userId, 'Settled and confirmed by Administrator');
      if ($res['status'] === 'SUCCESS') {
        $_SESSION['flash_success'] = $res['message'];
      } else {
        $_SESSION['flash_error'] = $res['message'];
      }
    }
    header("Location: dashboard.php?view=financeView");
    exit;
  }

  // Add user
  if ($action === 'addUser') {
    $uEmail = trim($_POST['email'] ?? '');
    $uFirstName = trim($_POST['firstName'] ?? '');
    $uLastName = trim($_POST['lastName'] ?? '');
    $uPhone = trim($_POST['phone'] ?? '');
    $uNic = trim($_POST['nic'] ?? '');
    $uRole = strtoupper(trim($_POST['role'] ?? 'PASSENGER'));
    $uPass = trim($_POST['password'] ?? '');

    if ($uEmail && $uFirstName && $uLastName && $uNic && $uPass) {
      if (User::emailExists($uEmail)) {
        $_SESSION['flash_error'] = "A user with email '$uEmail' already exists.";
      } elseif (User::nicExists($uNic)) {
        $_SESSION['flash_error'] = "A user with NIC '$uNic' already exists.";
      } else {
        $createdId = User::createUserWithRole($_POST);
        if ($createdId) {
          if ($uRole === 'DRIVER' && !empty($_FILES['licensePdf']['tmp_name'])) {
            User::uploadLicenseDocument($createdId, $_FILES['licensePdf']);
          }
          $_SESSION['flash_success'] = "User account ($uRole) for $uFirstName $uLastName created successfully (ID #$createdId).";
        } else {
          $_SESSION['flash_error'] = "Failed to create user. Please verify all fields.";
        }
      }
    } else {
      $_SESSION['flash_error'] = "Please fill in all required user fields.";
    }
    header("Location: dashboard.php?view=usersView");
    exit;
  }

  // Edit user
  if ($action === 'editUser') {
    $uId = (int) ($_POST['userId'] ?? 0);
    $uFirstName = trim($_POST['firstName'] ?? '');
    $uLastName = trim($_POST['lastName'] ?? '');
    $uEmail = trim($_POST['email'] ?? '');

    if ($uId > 0 && $uFirstName && $uLastName && $uEmail) {
      $ok = User::updateUserWithRole($uId, $_POST);
      if ($ok) {
        $uRole = strtoupper(trim($_POST['role'] ?? ''));
        if ($uRole === 'DRIVER' && !empty($_FILES['licensePdf']['tmp_name'])) {
          User::uploadLicenseDocument($uId, $_FILES['licensePdf']);
        }
        $_SESSION['flash_success'] = "User #$uId profile and actor details updated successfully.";
      } else {
        $_SESSION['flash_error'] = "Failed to update user details.";
      }
    } else {
      $_SESSION['flash_error'] = "Please provide valid user details for update.";
    }
    header("Location: dashboard.php?view=usersView");
    exit;
  }

  // Delete user
  if ($action === 'deleteUser') {
    $uId = (int) ($_POST['userId'] ?? 0);
    $myId = (int) ($currentUser['userId'] ?? $currentUser['userid'] ?? 0);

    if ($uId > 0) {
      if ($uId === $myId) {
        $_SESSION['flash_error'] = "Action prevented: You cannot delete your own logged-in administrator account.";
      } else {
        $ok = User::deleteUser($uId);
        if ($ok) {
          $_SESSION['flash_success'] = "User account #$uId and associated records deleted permanently.";
        } else {
          $_SESSION['flash_error'] = "Could not delete user #$uId due to active foreign key constraints.";
        }
      }
    }
    header("Location: dashboard.php?view=usersView");
    exit;
  }

  // Toggle vehicle status
  if ($action === 'toggleVehicleStatus') {
    $vId = (int) ($_POST['vehicleId'] ?? 0);
    $newStatus = trim($_POST['newStatus'] ?? 'AVAILABLE');
    if ($vId > 0 && in_array($newStatus, ['AVAILABLE', 'ON_TRIP', 'MAINTENANCE', 'DECOMMISSIONED'])) {
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

  // Register vehicle
  if ($action === 'addVehicle') {
    $regNo = strtoupper(trim($_POST['registrationNo'] ?? ''));
    $vType = strtoupper(trim($_POST['vehicleType'] ?? 'BUS'));
    if ($vType === 'CAR') {
      $vType = 'LUXURYCAR';
    }
    $model = trim($_POST['model'] ?? '');
    $mfg = trim($_POST['manufacturer'] ?? '');
    $cap = (int) ($_POST['capacity'] ?? 30);

    if ($regNo && $model && $mfg && $cap > 0) {
      $nextIdRow = Database::queryOracle("SELECT NVL(MAX(vehicleId), 0) + 1 AS nextId FROM vehicle");
      $vId = (int) ($nextIdRow[0]['nextid'] ?? $nextIdRow[0]['NEXTID'] ?? 1);

      $ok = Database::executeOracle("
                INSERT INTO vehicle (vehicleId, registrationNo, vehicleType, model, manufacturer, capacity, status)
                VALUES (:vId, :reg, :vt, :md, :mf, :cap, 'AVAILABLE')
            ", [
        ':vId' => $vId,
        ':reg' => $regNo,
        ':vt' => $vType,
        ':md' => $model,
        ':mf' => $mfg,
        ':cap' => $cap
      ]);

      if ($ok) {
        // Insert subtype record
        if ($vType === 'BUS') {
          $busType = strtoupper(trim($_POST['busType'] ?? 'AC'));
          if (!in_array($busType, ['AC', 'NON_AC', 'LUXURY'])) $busType = 'AC';
          $standingCapacity = max(0, (int) ($_POST['standingCapacity'] ?? 0));
          $routePermitNo = trim($_POST['routePermitNo'] ?? '');
          if (empty($routePermitNo)) {
            $routePermitNo = 'WP-EX-' . str_pad($vId, 4, '0', STR_PAD_LEFT);
          }
          Database::executeOracle("
                    INSERT INTO bus (busId, busType, standingCapacity, routePermitNo)
                    VALUES (:id, :bt, :sc, :rp)
                ", [
            ':id' => $vId,
            ':bt' => $busType,
            ':sc' => $standingCapacity,
            ':rp' => $routePermitNo
          ]);
        } elseif ($vType === 'VAN') {
          $vanCategory = strtoupper(trim($_POST['vanCategory'] ?? 'STANDARD'));
          if (!in_array($vanCategory, ['STANDARD', 'HIGH_ROOF', 'VIP'])) $vanCategory = 'STANDARD';
          $cargoCapacity = max(0.0, (float) ($_POST['cargoCapacity'] ?? 450.0));
          Database::executeOracle("
                    INSERT INTO van (vanId, vanCategory, cargoCapacity)
                    VALUES (:id, :vc, :cc)
                ", [
            ':id' => $vId,
            ':vc' => $vanCategory,
            ':cc' => $cargoCapacity
          ]);
        } elseif ($vType === 'LUXURYCAR') {
          $luxuryCategory = strtoupper(trim($_POST['luxuryCategory'] ?? 'SEDAN'));
          if (!in_array($luxuryCategory, ['SEDAN', 'SUV', 'PRESTIGE'])) $luxuryCategory = 'SEDAN';
          $wifiAvailable = strtoupper(trim($_POST['wifiAvailable'] ?? 'Y'));
          if (!in_array($wifiAvailable, ['Y', 'N'])) $wifiAvailable = 'Y';
          Database::executeOracle("
                    INSERT INTO luxuryCar (luxuryCarId, luxuryCategory, wifiAvailable)
                    VALUES (:id, :lc, :wf)
                ", [
            ':id' => $vId,
            ':lc' => $luxuryCategory,
            ':wf' => $wifiAvailable
          ]);
        } elseif ($vType === 'THREEWHEEL') {
          $meteredStatus = strtoupper(trim($_POST['meteredStatus'] ?? 'Y'));
          if (!in_array($meteredStatus, ['Y', 'N'])) $meteredStatus = 'Y';
          $canCarryLuggage = strtoupper(trim($_POST['canCarryLuggage'] ?? 'Y'));
          if (!in_array($canCarryLuggage, ['Y', 'N'])) $canCarryLuggage = 'Y';
          Database::executeOracle("
                    INSERT INTO threeWheel (threeWheelId, meteredStatus, canCarryLuggage)
                    VALUES (:id, :ms, :lg)
                ", [
            ':id' => $vId,
            ':ms' => $meteredStatus,
            ':lg' => $canCarryLuggage
          ]);
        } elseif ($vType === 'BIKE') {
          $helmetProvided = strtoupper(trim($_POST['helmetProvided'] ?? 'Y'));
          if (!in_array($helmetProvided, ['Y', 'N'])) $helmetProvided = 'Y';
          $engineCc = max(50, (int) ($_POST['engineCc'] ?? 150));
          Database::executeOracle("
                    INSERT INTO bike (bikeId, helmetProvided, engineCc)
                    VALUES (:id, :hp, :cc)
                ", [
            ':id' => $vId,
            ':hp' => $helmetProvided,
            ':cc' => $engineCc
          ]);
        }

        $_SESSION['flash_success'] = "Vehicle $regNo ($model - $vType) registered into active fleet.";
      } else {
        $_SESSION['flash_error'] = "Failed to register vehicle. Ensure registration number is unique.";
      }
    } else {
      $_SESSION['flash_error'] = "Please provide valid vehicle specifications.";
    }
    header("Location: dashboard.php?view=fleetView");
    exit;
  }

  // Add route
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

  // Create announcement
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

  // Delete announcement
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

// Oracle metrics
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
  // Revenue
  $revRows = Database::queryOracle("SELECT NVL(SUM(amountPaid), 0) AS total FROM payment WHERE paymentStatus = 'PAID'");
  if (!empty($revRows)) {
    $totalRevenue = (float) ($revRows[0]['total'] ?? 0);
  }

  // Users count
  $userCountRows = Database::queryOracle("SELECT COUNT(*) AS total FROM users");
  if (!empty($userCountRows)) {
    $totalUsersCount = (int) ($userCountRows[0]['total'] ?? 0);
  }

  // Active fleet count
  $fleetCountRows = Database::queryOracle("SELECT COUNT(*) AS total FROM vehicle WHERE status = 'AVAILABLE'");
  if (!empty($fleetCountRows)) {
    $activeFleetCount = (int) ($fleetCountRows[0]['total'] ?? 0);
  }

  // Completed trips
  $tripCountRows = Database::queryOracle("SELECT COUNT(*) AS total FROM scheduledTrip WHERE tripStatus = 'COMPLETED'");
  if (!empty($tripCountRows)) {
    $completedTrips = (int) ($tripCountRows[0]['total'] ?? 0);
  }

  // Recent bookings
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

  // Fleet list with subtype attributes
  $fleetList = Database::queryOracle("
        SELECT v.vehicleId, v.registrationNo, v.vehicleType, v.model, v.manufacturer, v.capacity, v.status,
               b.busType, b.standingCapacity, b.routePermitNo,
               vn.vanCategory, vn.cargoCapacity,
               lc.luxuryCategory, lc.wifiAvailable,
               tw.meteredStatus, tw.canCarryLuggage,
               bk.helmetProvided, bk.engineCc
        FROM vehicle v
        LEFT JOIN bus b ON v.vehicleId = b.busId
        LEFT JOIN van vn ON v.vehicleId = vn.vanId
        LEFT JOIN luxuryCar lc ON v.vehicleId = lc.luxuryCarId
        LEFT JOIN threeWheel tw ON v.vehicleId = tw.threeWheelId
        LEFT JOIN bike bk ON v.vehicleId = bk.bikeId
        ORDER BY v.vehicleId ASC
    ");

  
  // Users list
  $usersList = User::getAllWithDetails();

  // Routes list
  $routesList = Database::queryOracle("
        SELECT routeId, routeName, origin, destination, distanceKm, estimatedDurationMinutes, baseFare, routeType
        FROM route
        ORDER BY routeId ASC
    ");

  // Scheduled trips
  $scheduledTripsList = Database::queryOracle("
        SELECT st.tripId, r.routeName, v.registrationNo, v.vehicleType,
               st.tripDate, st.departureTime, st.arrivalTime, st.availableSeats, st.tripStatus
        FROM scheduledTrip st
        JOIN route r ON st.routeId = r.routeId
        JOIN vehicle v ON st.vehicleId = v.vehicleId
        ORDER BY st.tripDate DESC, st.departureTime DESC
    ");

  // Payments list
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

  // Promo codes list
  $promoCodesList = Database::queryOracle("
        SELECT promoId, promoCode, discountPercentage, validFrom, validUntil, maxUsage, currentUsage
        FROM promoCode
        ORDER BY promoId ASC
    ");

  // Refunds list
  $refundsList = Database::queryOracle("
        SELECT ref.refundId, ref.bookingId, ref.refundAmount, ref.refundReason, ref.refundDate, ref.refundStatus,
               (u.firstName || ' ' || u.lastName) AS processedByStaff
        FROM refund ref
        LEFT JOIN staff s ON ref.processedByStaffId = s.staffId
        LEFT JOIN users u ON s.staffId = u.userId
        ORDER BY ref.refundId DESC
    ");

  // Route intermediate stops
  $routeStopsList = Database::queryOracle("
        SELECT rs.routeId, r.routeName, rs.stopOrder, s.stopName, s.locationAddress, rs.arrivalTimeOffset, rs.departureTimeOffset
        FROM routeStop rs
        JOIN route r ON rs.routeId = r.routeId
        JOIN stop s ON rs.stopId = s.stopId
        ORDER BY rs.routeId, rs.stopOrder ASC
    ");
}

// MongoDB queries
$mongoAnnouncements = [];
$mongoReviews = [];
$mongoTopRated = ['drivers' => [], 'vehicles' => []];
$mongoVehDocs = [];
$searchKeyword = trim($_GET['keyword'] ?? '');

if ($mongoUp) {
  // Announcements
  $mongoAnnouncements = MongoService::getActiveAnnouncements();
  // Feedback search
  $mongoReviews = MongoService::searchFeedbackByKeyword($searchKeyword);
  // Top rated
  $mongoTopRated = MongoService::getTopRatedDriversAndVehicles();
  // Vehicle documents
  $mongoVehDocs = MongoService::getVehicleDocumentsAndMedia();
}

// Report queries PL/SQL
$activeReport = $_GET['report'] ?? 'frequentRoutes';
$reportData = [];

if ($oracleUp) {
  switch ($activeReport) {
    case 'frequentRoutes':
      $reportData = Database::callCursorProcedure("BEGIN pkgSmartMoveReports.getFrequentRoutes(:pCursor); END;");
      break;

    case 'periodRevenue':
      $reportData = Database::callCursorProcedure("BEGIN pkgSmartMoveReports.getPeriodRevenue(:pCursor); END;");
      break;

    case 'passengerHistory':
      $reportData = Database::callCursorProcedure("BEGIN pkgSmartMoveReports.getPassengerHistory(:pCursor); END;");
      break;

    case 'maintenanceDue':
      $reportData = Database::callCursorProcedure("BEGIN pkgSmartMoveReports.getMaintenanceDue(:pCursor); END;");
      break;

    case 'driverPerformance':
      $reportData = Database::callCursorProcedure("BEGIN pkgSmartMoveReports.getDriverPerformance(:pCursor); END;");
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
  <link rel="icon" type="image/jpeg" href="../img/logo.jpg">

  <link rel="stylesheet" href="../css/variables.css">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body class="dashboard-body">

  <div class="app-wrapper">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../components/dashboard/sidebar.php'; ?>

    <!-- Main content -->
    <main class="main-content">

      <!-- Topbar -->
      <?php include __DIR__ . '/../components/dashboard/topbar.php'; ?>

      <!-- Content -->
      <div class="content-container">

        <!-- Flash messages -->
        <?php include __DIR__ . '/../components/dashboard/flash_messages.php'; ?>

        <!-- Overview -->
        <?php include __DIR__ . '/../components/dashboard/views/overview_view.php'; ?>

        <!-- Reports -->
        <?php include __DIR__ . '/../components/dashboard/views/reports_view.php'; ?>

        <!-- Feedback and docs -->
        <?php include __DIR__ . '/../components/dashboard/views/mongo_view.php'; ?>

        <!-- Announcements -->
        <?php include __DIR__ . '/../components/dashboard/views/announcements_view.php'; ?>

        <!-- Routes -->
        <?php include __DIR__ . '/../components/dashboard/views/routes_view.php'; ?>

        <!-- Fleet -->
        <?php include __DIR__ . '/../components/dashboard/views/fleet_view.php'; ?>

        <!-- Users -->
        <?php include __DIR__ . '/../components/dashboard/views/users_view.php'; ?>

        <!-- Finance -->
        <?php include __DIR__ . '/../components/dashboard/views/finance_view.php'; ?>

        <!-- Settings -->
        <?php include __DIR__ . '/../components/dashboard/views/settings_view.php'; ?>

        <!-- Profile -->
        <?php include __DIR__ . '/../components/dashboard/views/profile_view.php'; ?>

      </div>
    </main>
  </div>

  <!-- Modals -->
  <?php include __DIR__ . '/../components/dashboard/modals.php'; ?>

  <!-- Script -->
  <script src="../js/dashboard.js"></script>
</body>

</html>
