<?php
// Passenger portal
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../components/dashboard/profile_controller.php';

// Auth check
Auth::requireRole(['PASSENGER', 'ADMIN']);

$currentUser = Auth::user();
$userId      = (int)($currentUser['userId'] ?? $currentUser['userid'] ?? 10);
$firstName   = htmlspecialchars($currentUser['firstName'] ?? $currentUser['firstname'] ?? 'Passenger');
$lastName    = htmlspecialchars($currentUser['lastName'] ?? $currentUser['lastname'] ?? '');
$fullName    = trim($firstName . ' ' . $lastName);
$email       = htmlspecialchars($currentUser['email'] ?? 'passenger@smartmove.lk');
$role        = strtoupper($currentUser['role'] ?? 'PASSENGER');
$initials    = strtoupper(substr($firstName, 0, 1) . (strlen($lastName) > 0 ? substr($lastName, 0, 1) : 'P'));

// Ensure passenger record
$passCheck = Database::queryOracle("SELECT passengerId, address, preferredPaymentMethod FROM passenger WHERE passengerId = :id", [':id' => $userId]);
if (empty($passCheck)) {
    Database::executeOracle("INSERT INTO passenger (passengerId, address, preferredPaymentMethod) VALUES (:id, 'Colombo, Sri Lanka', 'CARD')", [':id' => $userId]);
    $passengerInfo = ['address' => 'Colombo, Sri Lanka', 'preferredPaymentMethod' => 'CARD'];
} else {
    $passengerInfo = $passCheck[0];
}

// View routing
$activeView = $_GET['view'] ?? 'bookingsView';
$validViews = ['bookingsView', 'ridesView', 'announcementsView', 'profileView'];
if (!in_array($activeView, $validViews)) {
    $activeView = 'bookingsView';
}

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Post actions
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';

    // Update preferred payment method
    if ($action === 'updatePaymentPreference') {
        $newPref = strtoupper(trim($_POST['preferredPaymentMethod'] ?? 'CARD'));
        if (in_array($newPref, ['CARD', 'CASH', 'WALLET', 'ONLINE'])) {
            Database::executeOracle("UPDATE passenger SET preferredPaymentMethod = :pay WHERE passengerId = :id", [
                ':pay' => $newPref,
                ':id'  => $userId
            ]);
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['preferredPaymentMethod'] = $newPref;
            }
            $_SESSION['flash_success'] = "Preferred payment method updated to " . htmlspecialchars($newPref) . ".";
        } else {
            $_SESSION['flash_error'] = "Invalid payment method selected.";
        }

        header("Location: passenger.php?view=" . urlencode($activeView));
        exit;
    }
    // ticket booking procedure (prcBookingTicket)
    // Book trip via atomic PL/SQL stored procedure
    if ($action === 'bookTrip') {
        $tripId     = (int)($_POST['tripId'] ?? 0);
        $promoCode  = trim($_POST['promoCode'] ?? '');
        $seatNumber = !empty($_POST['seatNumber']) ? (int)$_POST['seatNumber'] : null;
        $payMethod  = strtoupper(trim($_POST['paymentMethod'] ?? 'CARD'));
        if (!in_array($payMethod, ['CARD', 'CASH', 'ONLINE', 'WALLET'])) {
            $payMethod = 'CARD';
        }

        $res = Database::callBookTicket($userId, $tripId, $promoCode, $seatNumber, $payMethod);
        if ($res['status'] === 'SUCCESS') {
            $_SESSION['flash_success'] = $res['message'] . " (Ticket #" . $res['bookingId'] . ", Paid: LKR " . number_format($res['amountPaid'], 2) . ").";
        } else {
            $_SESSION['flash_error'] = $res['message'];
        }

        header("Location: passenger.php?view=bookingsView");
        exit;
    }

    // Cancel booking 
    if ($action === 'cancelBooking') {
        $bookingId = (int)($_POST['bookingId'] ?? 0);
        $res = Database::callProcessRefund($bookingId, 2, 'Passenger online cancellation');
        if ($res['status'] === 'SUCCESS') {
            $_SESSION['flash_success'] = "Reservation #$bookingId cancelled. Refund of LKR " . number_format($res['refundAmount'], 2) . " processed.";
        } else {
            $_SESSION['flash_error'] = $res['message'];
        }

        header("Location: passenger.php?view=bookingsView");
        exit;
    }

    // Submit review and rating 
    if ($action === 'submitReview') {
        $bookingId = (int)($_POST['bookingId'] ?? 0);
        $tripId    = (int)($_POST['tripId'] ?? 0);
        $rating    = max(1, min(5, (float)($_POST['rating'] ?? 5)));
        $comment   = trim($_POST['comment'] ?? '');

        // Generate feedback ID 
        $fIdRow     = Database::queryOracle("SELECT NVL(MAX(feedbackId), 0) + 1 AS nextId FROM feedback");
        $feedbackId = (int)($fIdRow[0]['nextId'] ?? 1);
        Database::executeOracle("
            INSERT INTO feedback (feedbackId, passengerId, tripId, rating, comments, feedbackDate)
            VALUES (:fId, :pId, :tId, :rating, :comments, SYSDATE)
        ", [
            ':fId'      => $feedbackId,
            ':pId'      => $userId,
            ':tId'      => $tripId,
            ':rating'   => $rating,
            ':comments' => $comment
        ]);

        // Fetch journey context for review record
        $tripInfo = Database::queryOracle("
            SELECT st.tripId, r.routeId, r.routeName, v.vehicleId, v.registrationNo, v.vehicleType,
                   d.driverId, (u.firstName || ' ' || u.lastName) AS driverName
            FROM scheduledTrip st
            JOIN route r ON st.routeId = r.routeId
            JOIN vehicle v ON st.vehicleId = v.vehicleId
            LEFT JOIN driver d ON st.driverId = d.driverId
            LEFT JOIN users u ON d.driverId = u.userId
            WHERE st.tripId = :tId
        ", [':tId' => $tripId]);

        $routeId     = !empty($tripInfo) ? (int)$tripInfo[0]['routeId'] : 1;
        $routeName   = !empty($tripInfo) ? $tripInfo[0]['routeName'] : 'Express Route';
        $vehicleId   = !empty($tripInfo) ? (int)$tripInfo[0]['vehicleId'] : 1;
        $vehicleReg  = !empty($tripInfo) ? $tripInfo[0]['registrationNo'] : 'WP-BUS-01';
        $vehicleType = !empty($tripInfo) ? $tripInfo[0]['vehicleType'] : 'BUS';
        $driverId    = !empty($tripInfo) ? (int)$tripInfo[0]['driverId'] : 1;
        $driverName  = !empty($tripInfo) ? $tripInfo[0]['driverName'] : 'Kamal Perera';

        $reviewDoc = [
            'reviewId'      => 'REV-' . date('Y') . '-' . sprintf('%04d', $feedbackId),
            'bookingId'     => $bookingId,
            'passengerId'   => $userId,
            'passengerName' => $fullName,
            'routeId'       => $routeId,
            'routeName'     => $routeName,
            'vehicleId'     => $vehicleId,
            'vehicleRegNo'  => $vehicleReg,
            'vehicleType'   => $vehicleType,
            'driverId'      => $driverId,
            'driverName'    => $driverName,
            'rating'        => $rating,
            'comment'       => $comment,
            'sentiment'     => ($rating >= 4 ? 'POSITIVE' : ($rating === 3 ? 'NEUTRAL' : 'NEGATIVE')),
            'createdAt'     => date('Y-m-d H:i:s')
        ];
        Database::insertMongo('passenger_reviews', $reviewDoc);

        $_SESSION['flash_success'] = "Thank you! Your journey rating and review have been recorded.";
        header("Location: passenger.php?view=bookingsView");
        exit;
    }
}

// Fetch bookings
$myBookings = Database::queryOracle("
    SELECT b.bookingId, b.seatNumber, b.ticketPrice, b.bookingStatus, 
           TO_CHAR(b.bookingDate, 'YYYY-MM-DD HH24:MI') AS bookingDateFormatted,
           t.tripId, TO_CHAR(t.tripDate, 'YYYY-MM-DD') AS tripDateFormatted,
           t.departureTime, t.arrivalTime, t.tripStatus,
           r.routeName, r.routeType, r.origin, r.destination,
           v.registrationNo, v.vehicleType,
           pay.paymentMethod, pay.paymentStatus
    FROM booking b
    JOIN scheduledTrip t ON b.tripId = t.tripId
    JOIN route r ON t.routeId = r.routeId
    JOIN vehicle v ON t.vehicleId = v.vehicleId
    LEFT JOIN payment pay ON b.bookingId = pay.bookingId
    WHERE b.passengerId = :pId
    ORDER BY b.bookingId DESC
", [':pId' => $userId]);

// Available trips
$availableTrips = Database::queryOracle("
    SELECT t.tripId, TO_CHAR(t.tripDate, 'YYYY-MM-DD') AS tripDateFormatted,
           t.departureTime, t.arrivalTime, t.availableSeats, t.fareAmount, t.tripStatus,
           r.routeId, r.routeName, r.routeType, r.origin, r.destination,
           v.registrationNo, v.vehicleType
    FROM scheduledTrip t
    JOIN route r ON t.routeId = r.routeId
    JOIN vehicle v ON t.vehicleId = v.vehicleId
    WHERE t.tripStatus IN ('SCHEDULED', 'IN_TRANSIT') AND t.availableSeats > 0
    ORDER BY t.tripDate ASC, t.departureTime ASC
");

// Route intermediate stops
$routeStopsData = Database::queryOracle("
    SELECT rs.routeId, rs.stopOrder, s.stopName, s.locationAddress, rs.arrivalTimeOffset
    FROM routeStop rs
    JOIN stop s ON rs.stopId = s.stopId
    ORDER BY rs.routeId, rs.stopOrder ASC
");
$stopsByRoute = [];
foreach ($routeStopsData as $rs) {
    $rId = (int)$rs['routeId'];
    $stopsByRoute[$rId][] = $rs;
}

// Travel announcements from Mongo
$travelAnnouncements = Database::queryMongo('travel_announcements', ['active' => true], ['sort' => ['publishedAt' => -1]]);

$totalBookingsCount = count($myBookings);
$activeBookingsCount = count(array_filter($myBookings, fn($b) => strtoupper($b['bookingStatus']) === 'CONFIRMED'));
$pageTitle = "SmartMove — Passenger Travel Portal";
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
  <style>
    .passenger-nav-tabs {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-bottom: 1.25rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--border-subtle);
    }
    .passenger-tab-btn {
      background: var(--bg-card-inner);
      border: 1px solid var(--border-subtle);
      color: var(--text-secondary);
      font-size: 0.8rem;
      font-weight: 500;
      padding: 0.5rem 1rem;
      border-radius: var(--radius-md);
      cursor: pointer;
      text-decoration: none;
      transition: all 0.15s ease;
      box-shadow: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    .passenger-tab-btn:hover {
      background: var(--bg-active);
      color: var(--text-white);
    }
    .passenger-tab-btn.active {
      background: var(--blue-primary);
      color: #FFFFFF;
      border-color: var(--blue-primary);
      box-shadow: none;
    }
    .trip-card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 1.25rem;
    }
    .trip-booking-card {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-lg);
      padding: 1.25rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: border-color 0.15s ease;
      box-shadow: none;
    }
    .trip-booking-card:hover {
      border-color: var(--border-subtle);
    }
  </style>
</head>
<body class="dashboard-body">

<div class="app-wrapper">

  <!-- Main content -->
  <main class="main-content" style="margin-left:0; width:100%;">
    
    <!-- Topbar -->
    <header class="topbar" style="border-radius:0; padding:1rem 2rem;">
      <div class="topbar-left" style="display:flex; align-items:center; gap:0.75rem;">
        <a href="../index.php" class="brand-logo" style="text-decoration:none; font-size:1.3rem; font-weight:800; color:#fff; display:inline-flex; align-items:center; gap:0.6rem;">
          <img src="../img/logo.jpg" alt="SmartMove Logo" style="width:34px; height:34px; border-radius:8px; object-fit:cover;">
          <span>SmartMove<span style="color:var(--blue-light);">.</span></span>
        </a>
        <span style="font-size:0.85rem; color:var(--text-muted); padding-left:0.75rem; border-left:1px solid var(--border-subtle);">
          Passenger Mobility Portal
        </span>
      </div>

      <div class="topbar-right">
        <div style="font-size:0.85rem; color:var(--text-secondary); text-align:right;">
          <div style="font-weight:700; color:#fff;"><?php echo $fullName; ?></div>
          <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo $email; ?></div>
        </div>

        <div class="user-avatar" style="background:#0284C7;"><?php echo $initials; ?></div>

        <a href="../index.php" class="btn-primary" style="background:var(--bg-card-inner); border:1px solid var(--border-subtle); color:#fff; text-decoration:none; font-size:0.85rem; padding:0.45rem 0.85rem;">
          Home
        </a>
        <a href="logout.php" class="btn-primary" style="background:rgba(239,68,68,0.2); border:1px solid rgba(239,68,68,0.4); color:#F87171; text-decoration:none; font-size:0.85rem; padding:0.45rem 0.85rem;">
          Sign Out
        </a>
      </div>
    </header>

    <!-- Content -->
    <div class="content-container" style="max-width:1200px; margin:0 auto; padding:2rem 1.5rem;">

      <!-- Flash messages -->
      <?php if (!empty($flashSuccess)): ?>
        <div class="alert-box alert-success" style="margin-bottom:1.5rem; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.4); color:#34D399; padding:1rem; border-radius:var(--radius-md);">
          <?php echo htmlspecialchars($flashSuccess); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($flashError)): ?>
        <div class="alert-box alert-error" style="margin-bottom:1.5rem; background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#F87171; padding:1rem; border-radius:var(--radius-md);">
          <?php echo htmlspecialchars($flashError); ?>
        </div>
      <?php endif; ?>

      <!-- KPIs -->
      <div class="stats-grid" style="margin-bottom:1.5rem;">
        <div class="stat-card">
          <div class="stat-header-row">
            <span class="stat-label">Total Reservations</span>
            <span class="stat-icon">TKT</span>
          </div>
          <div class="stat-value"><?php echo $totalBookingsCount; ?></div>
          <div class="stat-subtext">Lifetime trips booked</div>
        </div>

        <div class="stat-card">
          <div class="stat-header-row">
            <span class="stat-label">Active / Confirmed Trips</span>
            <span class="stat-icon">BUS</span>
          </div>
          <div class="stat-value" style="color:#38BDF8;"><?php echo $activeBookingsCount; ?></div>
          <div class="stat-subtext">Upcoming departures</div>
        </div>

        <div class="stat-card" style="position:relative;">
          <div class="stat-header-row">
            <span class="stat-label">Preferred Payment</span>
            <span class="stat-icon" title="Preferred Payment Method">PAY</span>
          </div>
          <form method="POST" action="passenger.php?view=<?php echo urlencode($activeView); ?>" style="margin-top:0.35rem; margin-bottom:0.15rem;">
            <input type="hidden" name="action" value="updatePaymentPreference">
            <div style="position:relative;">
              <select name="preferredPaymentMethod" onchange="this.form.submit()" class="form-select" style="font-size:1.25rem; font-weight:800; color:#38BDF8; background:rgba(15,23,42,0.6); border:1px solid rgba(56,189,248,0.3); border-radius:6px; padding:0.35rem 0.6rem; width:100%; cursor:pointer;" title="Click to change your preferred payment method">
                <option value="CARD" <?php echo ($passengerInfo['preferredPaymentMethod'] ?? '') === 'CARD' ? 'selected' : ''; ?>>CARD (Debit/Credit)</option>
                <option value="CASH" <?php echo ($passengerInfo['preferredPaymentMethod'] ?? '') === 'CASH' ? 'selected' : ''; ?>>CASH (At Boarding)</option>
                <option value="WALLET" <?php echo ($passengerInfo['preferredPaymentMethod'] ?? '') === 'WALLET' ? 'selected' : ''; ?>>WALLET (Digital)</option>
                <option value="ONLINE" <?php echo ($passengerInfo['preferredPaymentMethod'] ?? '') === 'ONLINE' ? 'selected' : ''; ?>>ONLINE (Net Banking)</option>
              </select>
            </div>
          </form>
          <div class="stat-subtext" style="display:flex; justify-content:space-between; align-items:center; margin-top:0.35rem;">
            <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px;" title="<?php echo htmlspecialchars($passengerInfo['address'] ?? 'Sri Lanka'); ?>">
              <?php echo htmlspecialchars($passengerInfo['address'] ?? 'Sri Lanka'); ?>
            </span>
            <span style="color:var(--text-muted); font-size:0.7rem;">Click to change</span>
          </div>
        </div>
      </div>

      <!-- Navigation tabs -->
      <nav class="passenger-nav-tabs">
        <a href="passenger.php?view=bookingsView" class="passenger-tab-btn <?php echo $activeView === 'bookingsView' ? 'active' : ''; ?>">
          My Bookings &amp; Tickets
        </a>
        <a href="passenger.php?view=ridesView" class="passenger-tab-btn <?php echo $activeView === 'ridesView' ? 'active' : ''; ?>">
          Book Scheduled Express Rides
        </a>
        <a href="passenger.php?view=announcementsView" class="passenger-tab-btn <?php echo $activeView === 'announcementsView' ? 'active' : ''; ?>">
          Travel Announcements &amp; Advisories
        </a>
        <a href="passenger.php?view=profileView" class="passenger-tab-btn <?php echo $activeView === 'profileView' ? 'active' : ''; ?>">
          Manage Profile &amp; Password
        </a>
      </nav>

      <!-- Bookings view -->
      <?php if ($activeView === 'bookingsView'): ?>
        <section class="page-view active-view">
          <div class="view-header" style="margin-bottom:1rem;">
            <div class="view-title-group">
              <h1 style="font-size:1.5rem; font-weight:700;">My Travel Reservations</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem;">View and manage your upcoming departures and completed trips.</p>
            </div>
            <div class="view-actions">
              <a href="passenger.php?view=ridesView" class="btn-primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem;">
                + Book New Ride
              </a>
            </div>
          </div>

          <?php if (empty($myBookings)): ?>
            <div class="dash-panel" style="text-align:center; padding:3rem 1rem; color:var(--text-secondary);">
              <div style="font-size:3rem; margin-bottom:1rem;"></div>
              <h3>No Travel Bookings Yet</h3>
              <p style="margin-top:0.5rem; font-size:0.9rem;">You haven't reserved any rides yet. Explore available scheduled departures across Sri Lanka.</p>
              <a href="passenger.php?view=ridesView" class="btn-primary" style="display:inline-block; margin-top:1rem; text-decoration:none;">
                Browse Available Trips
              </a>
            </div>
          <?php else: ?>
            <div class="dash-panel">
              <div class="data-table-container">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Ticket #</th>
                      <th>Route / Journey</th>
                      <th>Date &amp; Time</th>
                      <th>Seat</th>
                      <th>Fare</th>
                      <th>Payment</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($myBookings as $b): 
                      $bStatus = strtoupper($b['bookingStatus'] ?? 'CONFIRMED');
                      $badgeClass = ($bStatus === 'CONFIRMED') ? 'badge-active' : 'badge-inactive';
                    ?>
                      <tr>
                        <td><strong>#<?php echo $b['bookingId']; ?></strong></td>
                        <td>
                          <strong><?php echo htmlspecialchars($b['routeName']); ?></strong><br>
                          <span style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($b['origin'] . ' → ' . $b['destination']); ?></span>
                        </td>
                        <td>
                          <div><?php echo htmlspecialchars($b['tripDateFormatted'] ?? $b['tripdateformatted'] ?? $b['tripDate'] ?? ''); ?></div>
                          <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($b['departureTime'] . ' - ' . $b['arrivalTime']); ?></div>
                        </td>
                        <td><span style="background:var(--bg-card-inner); padding:0.25rem 0.5rem; border-radius:4px; font-weight:700;">Seat <?php echo $b['seatNumber']; ?></span></td>
                        <td><strong>LKR <?php echo number_format((float)$b['ticketPrice'], 2); ?></strong></td>
                        <td>
                          <span style="font-size:0.8rem;"><?php echo htmlspecialchars($b['paymentMethod'] ?? 'CARD'); ?></span>
                        </td>
                        <td>
                          <span class="badge-status <?php echo $badgeClass; ?>">
                            <?php echo $bStatus; ?>
                          </span>
                        </td>
                        <td>
                          <?php if ($bStatus === 'CONFIRMED'): ?>
                            <div style="display:flex; gap:0.4rem; align-items:center;">
                              <button type="button" onclick="document.getElementById('rateModal-<?php echo $b['bookingId']; ?>').style.display='block'" style="background:rgba(56,189,248,0.15); border:1px solid rgba(56,189,248,0.3); color:#38BDF8; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.75rem; cursor:pointer;">
                                Rate Trip
                              </button>
                              <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel Ticket #<?php echo $b['bookingId']; ?>?');">
                                <input type="hidden" name="action" value="cancelBooking">
                                <input type="hidden" name="bookingId" value="<?php echo $b['bookingId']; ?>">
                                <button type="submit" style="background:transparent; border:1px solid rgba(239,68,68,0.5); color:#F87171; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.75rem; cursor:pointer;">
                                  Cancel
                                </button>
                              </form>
                            </div>

                            <!-- Rating modal -->
                            <div id="rateModal-<?php echo $b['bookingId']; ?>" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px);">
                              <div style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); max-width:480px; margin:10% auto; padding:1.5rem; position:relative;">
                                <h3 style="color:#fff; margin-bottom:0.5rem; font-size:1.1rem;">Rate Your Journey</h3>
                                <p style="color:var(--text-secondary); font-size:0.85rem; margin-bottom:1rem;">Ticket #<?php echo $b['bookingId']; ?> &bull; <?php echo htmlspecialchars($b['routeName']); ?></p>
                                <form method="POST">
                                  <input type="hidden" name="action" value="submitReview">
                                  <input type="hidden" name="bookingId" value="<?php echo $b['bookingId']; ?>">
                                  <input type="hidden" name="tripId" value="<?php echo $b['tripId']; ?>">
                                  
                                  <div style="margin-bottom:1rem;">
                                    <label style="display:block; font-size:0.8rem; color:var(--text-muted); margin-bottom:0.35rem;">Rating (Stars)</label>
                                    <select name="rating" style="width:100%; padding:0.5rem; background:var(--bg-card-inner); border:1px solid var(--border-subtle); color:#fff; border-radius:6px;">
                                      <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; (5 - Excellent)</option>
                                      <option value="4">&#9733;&#9733;&#9733;&#9733;&#9734; (4 - Very Good)</option>
                                      <option value="3">&#9733;&#9733;&#9733;&#9734;&#9734; (3 - Average)</option>
                                      <option value="2">&#9733;&#9733;&#9734;&#9734;&#9734; (2 - Needs Improvement)</option>
                                      <option value="1">&#9733;&#9734;&#9734;&#9734;&#9734; (1 - Poor)</option>
                                    </select>
                                  </div>

                                  <div style="margin-bottom:1.25rem;">
                                    <label style="display:block; font-size:0.8rem; color:var(--text-muted); margin-bottom:0.35rem;">Comments &amp; Experience</label>
                                    <textarea name="comment" rows="3" required placeholder="Driver conduct, AC comfort, punctuality, hygiene..." style="width:100%; padding:0.5rem; background:var(--bg-card-inner); border:1px solid var(--border-subtle); color:#fff; border-radius:6px; font-size:0.85rem; resize:vertical;"></textarea>
                                  </div>

                                  <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                                    <button type="button" onclick="document.getElementById('rateModal-<?php echo $b['bookingId']; ?>').style.display='none'" style="padding:0.5rem 1rem; background:var(--bg-card-inner); border:1px solid var(--border-subtle); color:var(--text-secondary); border-radius:6px; cursor:pointer; font-size:0.85rem;">Cancel</button>
                                    <button type="submit" class="btn-primary" style="padding:0.5rem 1.25rem; font-size:0.85rem;">Submit Feedback</button>
                                  </div>
                                </form>
                              </div>
                            </div>
                          <?php else: ?>
                            <span style="color:var(--text-muted); font-size:0.8rem;">Cancelled</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?>
        </section>
      <?php endif; ?>

      <!-- Rides view -->
      <?php if ($activeView === 'ridesView'): ?>
        <section class="page-view active-view">
          <div class="view-header" style="margin-bottom:1.5rem;">
            <div class="view-title-group">
              <h1 style="font-size:1.5rem; font-weight:700;">Available Scheduled Departures</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem;">Reserve seats on modern highway coaches, luxury vans, and intercity express buses.</p>
            </div>
          </div>

          <div class="trip-card-grid">
            <?php foreach ($availableTrips as $t): 
              $rId = (int)($t['routeId'] ?? $t['routeid'] ?? 0);
              $stops = $stopsByRoute[$rId] ?? [];
            ?>
              <div class="trip-booking-card">
                <div>
                  <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:0.75rem;">
                    <span style="background:rgba(56,189,248,0.15); color:#38BDF8; border:1px solid rgba(56,189,248,0.3); font-size:0.7rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:4px;">
                      <?php echo htmlspecialchars($t['routeType']); ?>
                    </span>
                    <span style="font-size:0.8rem; color:#34D399; font-weight:600;">
                      <?php echo $t['availableSeats'] ?? $t['availableseats'] ?? 0; ?> seats left
                    </span>
                  </div>

                  <h3 style="font-size:1.1rem; font-weight:700; color:#fff; margin-bottom:0.35rem;">
                    <?php echo htmlspecialchars($t['routeName'] ?? $t['routename'] ?? ''); ?>
                  </h3>
                  <div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:0.75rem;">
                    <?php echo htmlspecialchars($t['origin'] ?? ''); ?> → <?php echo htmlspecialchars($t['destination'] ?? ''); ?>
                  </div>

                  <div style="display:flex; justify-content:space-between; font-size:0.85rem; background:var(--bg-card-inner); padding:0.6rem 0.75rem; border-radius:6px; margin-bottom:0.75rem;">
                    <div>
                      <div style="color:var(--text-muted); font-size:0.7rem;">DEPARTURE</div>
                      <div style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($t['departureTime'] ?? $t['departuretime'] ?? ''); ?></div>
                    </div>
                    <div style="text-align:right;">
                      <div style="color:var(--text-muted); font-size:0.7rem;">DATE</div>
                      <div style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($t['tripDateFormatted'] ?? $t['tripdateformatted'] ?? $t['tripDate'] ?? ''); ?></div>
                    </div>
                  </div>

                  <?php if (!empty($stops)): ?>
                    <div style="background:rgba(255,255,255,0.03); border:1px solid var(--border-subtle); border-radius:6px; padding:0.5rem 0.75rem; margin-bottom:0.75rem; font-size:0.75rem;">
                      <div style="color:var(--text-muted); font-weight:600; margin-bottom:0.25rem;">INTERMEDIATE STOPS (ROUTE #<?php echo $rId; ?>)</div>
                      <div style="color:var(--text-secondary); line-height:1.4;">
                        <?php 
                          $stopNames = array_map(fn($s) => htmlspecialchars($s['stopName'] ?? $s['stopname'] ?? ''), $stops);
                          echo implode(' &rarr; ', $stopNames);
                        ?>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>

                <div style="border-top:1px solid var(--border-subtle); padding-top:1rem;">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span style="font-size:0.75rem; color:var(--text-muted);">Standard Fare</span>
                    <div style="font-size:1.15rem; font-weight:800; color:#fff;">LKR <?php echo number_format((float)($t['fareAmount'] ?? $t['fareamount'] ?? 0), 2); ?></div>
                  </div>

                  <form method="POST">
                    <input type="hidden" name="action" value="bookTrip">
                    <input type="hidden" name="tripId" value="<?php echo $t['tripId'] ?? $t['tripid'] ?? 0; ?>">

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; margin-bottom:0.5rem;">
                      <div>
                        <label style="display:block; font-size:0.7rem; color:var(--text-muted); margin-bottom:0.2rem;">Promo Code</label>
                        <input type="text" name="promoCode" placeholder="e.g. SMARTMOVE10" style="width:100%; padding:0.4rem; font-size:0.75rem; background:var(--bg-card-inner); border:1px solid var(--border-subtle); color:#fff; border-radius:4px;">
                      </div>
                      <div>
                        <label style="display:block; font-size:0.7rem; color:var(--text-muted); margin-bottom:0.2rem;">Seat Preference</label>
                        <input type="number" name="seatNumber" min="1" max="45" placeholder="Auto-assign" style="width:100%; padding:0.4rem; font-size:0.75rem; background:var(--bg-card-inner); border:1px solid var(--border-subtle); color:#fff; border-radius:4px;">
                      </div>
                    </div>

                    <div style="display:flex; gap:0.5rem; align-items:center;">
                      <?php $prefPayMethod = strtoupper($passengerInfo['preferredPaymentMethod'] ?? 'CARD'); ?>
                      <select name="paymentMethod" style="flex:1; padding:0.45rem; font-size:0.8rem; background:var(--bg-card-inner); border:1px solid var(--border-subtle); color:#fff; border-radius:4px;">
                        <option value="CARD" <?php echo $prefPayMethod === 'CARD' ? 'selected' : ''; ?>>Debit / Credit Card</option>
                        <option value="ONLINE" <?php echo $prefPayMethod === 'ONLINE' ? 'selected' : ''; ?>>Online Banking</option>
                        <option value="WALLET" <?php echo $prefPayMethod === 'WALLET' ? 'selected' : ''; ?>>Mobile Wallet</option>
                        <option value="CASH" <?php echo $prefPayMethod === 'CASH' ? 'selected' : ''; ?>>Cash at Boarding</option>
                      </select>
                      <button type="submit" class="btn-primary" style="padding:0.45rem 1rem; font-size:0.85rem; white-space:nowrap;">
                        Book Ticket
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <!-- Announcements view -->
      <?php if ($activeView === 'announcementsView'): ?>
        <section class="page-view active-view">
          <div class="view-header" style="margin-bottom:1.5rem;">
            <div class="view-title-group">
              <h1 style="font-size:1.5rem; font-weight:700;">Travel Announcements &amp; Advisories</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem;">Live expressway alerts, weather advisories, and transit schedules across Sri Lanka.</p>
            </div>
          </div>

          <?php if (empty($travelAnnouncements)): ?>
            <div class="dash-panel" style="text-align:center; padding:3rem 1rem; color:var(--text-secondary);">
              <h3>No Active Travel Advisories</h3>
              <p style="margin-top:0.5rem; font-size:0.9rem;">All highway corridors and express services are operating under normal schedule.</p>
            </div>
          <?php else: ?>
            <div style="display:grid; gap:1rem;">
              <?php foreach ($travelAnnouncements as $ann): 
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
      <?php endif; ?>

      <!-- Profile view -->
      <?php if ($activeView === 'profileView'): ?>
        <?php include __DIR__ . '/../components/dashboard/views/profile_view.php'; ?>
      <?php endif; ?>

    </div>
  </main>
</div>

</body>
</html>
