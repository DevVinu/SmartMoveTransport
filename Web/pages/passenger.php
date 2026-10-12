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
$validViews = ['bookingsView', 'ridesView', 'profileView'];
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

    // Book trip
    if ($action === 'bookTrip') {
        $tripId = (int)($_POST['tripId'] ?? 0);
        $payMethod = strtoupper(trim($_POST['paymentMethod'] ?? 'CARD'));
        if (!in_array($payMethod, ['CARD', 'CASH', 'ONLINE', 'WALLET'])) {
            $payMethod = 'CARD';
        }

        // Verify trip availability
        $tripRow = Database::queryOracle("SELECT tripId, fareAmount, availableSeats, tripStatus FROM scheduledTrip WHERE tripId = :tId", [':tId' => $tripId]);
        if (!empty($tripRow) && (int)$tripRow[0]['availableSeats'] > 0) {
            $fare = (float)$tripRow[0]['fareAmount'];

            // Next booking ID
            $nextBIdRow = Database::queryOracle("SELECT NVL(MAX(bookingId), 0) + 1 AS nextId FROM booking");
            $bookingId = (int)($nextBIdRow[0]['nextid'] ?? rand(100, 999));
            $seatNumber = rand(1, 40);

            // Insert booking
            $bSql = "INSERT INTO booking (bookingId, passengerId, tripId, seatNumber, ticketPrice, bookingStatus) 
                     VALUES (:bId, :pId, :tId, :seat, :fare, 'CONFIRMED')";
            $bOk = Database::executeOracle($bSql, [
                ':bId'  => $bookingId,
                ':pId'  => $userId,
                ':tId'  => $tripId,
                ':seat' => $seatNumber,
                ':fare' => $fare
            ]);

            if ($bOk) {
                // Next payment ID
                $nextPIdRow = Database::queryOracle("SELECT NVL(MAX(paymentId), 0) + 1 AS nextId FROM payment");
                $paymentId = (int)($nextPIdRow[0]['nextid'] ?? rand(100, 999));
                $txRef = 'TX-' . strtoupper(bin2hex(random_bytes(4)));

                // Insert payment
                $pSql = "INSERT INTO payment (paymentId, bookingId, amountPaid, paymentMethod, transactionReference, paymentStatus)
                         VALUES (:pId, :bId, :amt, :method, :ref, 'PAID')";
                Database::executeOracle($pSql, [
                    ':pId'    => $paymentId,
                    ':bId'    => $bookingId,
                    ':amt'    => $fare,
                    ':method' => $payMethod,
                    ':ref'    => $txRef
                ]);

                // Update seats
                Database::executeOracle("UPDATE scheduledTrip SET availableSeats = availableSeats - 1 WHERE tripId = :tId", [':tId' => $tripId]);

                $_SESSION['flash_success'] = "Booking confirmed successfully! Ticket reference #$bookingId (Seat $seatNumber).";
            } else {
                $_SESSION['flash_error'] = "Unable to complete booking. Please try again.";
            }
        } else {
            $_SESSION['flash_error'] = "Selected trip is sold out or unavailable.";
        }

        header("Location: passenger.php?view=bookingsView");
        exit;
    }

    // Cancel booking
    if ($action === 'cancelBooking') {
        $bookingId = (int)($_POST['bookingId'] ?? 0);
        $bRow = Database::queryOracle("SELECT bookingId, tripId, bookingStatus FROM booking WHERE bookingId = :bId AND passengerId = :pId", [
            ':bId' => $bookingId,
            ':pId' => $userId
        ]);

        if (!empty($bRow) && $bRow[0]['bookingStatus'] !== 'CANCELLED') {
            $tripId = (int)$bRow[0]['tripId'];
            Database::executeOracle("UPDATE booking SET bookingStatus = 'CANCELLED' WHERE bookingId = :bId", [':bId' => $bookingId]);
            Database::executeOracle("UPDATE scheduledTrip SET availableSeats = availableSeats + 1 WHERE tripId = :tId", [':tId' => $tripId]);
            $_SESSION['flash_success'] = "Reservation #$bookingId has been cancelled.";
        } else {
            $_SESSION['flash_error'] = "Booking not eligible for cancellation.";
        }

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
           r.routeName, r.routeType, r.origin, r.destination,
           v.registrationNo, v.vehicleType
    FROM scheduledTrip t
    JOIN route r ON t.routeId = r.routeId
    JOIN vehicle v ON t.vehicleId = v.vehicleId
    WHERE t.tripStatus IN ('SCHEDULED', 'IN_TRANSIT') AND t.availableSeats > 0
    ORDER BY t.tripDate ASC, t.departureTime ASC
");

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
  
  <link rel="stylesheet" href="../css/variables.css">
  <link rel="stylesheet" href="../css/dashboard.css">
  <style>
    .passenger-nav-tabs {
      display: flex;
      gap: 0.5rem;
      border-bottom: 1px solid var(--border-subtle);
      margin-bottom: 1.5rem;
    }
    .passenger-tab-btn {
      padding: 0.75rem 1.25rem;
      background: transparent;
      border: none;
      color: var(--text-secondary);
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      border-bottom: 2px solid transparent;
      transition: all 0.2s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    .passenger-tab-btn:hover {
      color: var(--text-white);
    }
    .passenger-tab-btn.active {
      color: var(--blue-light);
      border-bottom-color: var(--blue-light);
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
      transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .trip-booking-card:hover {
      border-color: rgba(56, 189, 248, 0.4);
      transform: translateY(-2px);
    }
  </style>
</head>
<body class="dashboard-body">

<div class="app-wrapper">

  <!-- Main content -->
  <main class="main-content" style="margin-left:0; width:100%;">
    
    <!-- Topbar -->
    <header class="topbar" style="border-radius:0; padding:1rem 2rem;">
      <div class="topbar-left">
        <a href="../index.php" class="brand-logo" style="text-decoration:none; font-size:1.3rem; font-weight:800; color:#fff;">
          SmartMove<span style="color:var(--blue-light);">.</span>
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

        <div class="stat-card">
          <div class="stat-header-row">
            <span class="stat-label">Preferred Payment</span>
            <span class="stat-icon">PAY</span>
          </div>
          <div class="stat-value" style="font-size:1.3rem;"><?php echo htmlspecialchars($passengerInfo['preferredPaymentMethod'] ?? 'CARD'); ?></div>
          <div class="stat-subtext"><?php echo htmlspecialchars($passengerInfo['address'] ?? 'Sri Lanka'); ?></div>
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
                          <div><?php echo htmlspecialchars($b['tripDateFormatted'] ?? ''); ?></div>
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
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel Ticket #<?php echo $b['bookingId']; ?>?');">
                              <input type="hidden" name="action" value="cancelBooking">
                              <input type="hidden" name="bookingId" value="<?php echo $b['bookingId']; ?>">
                              <button type="submit" style="background:transparent; border:1px solid rgba(239,68,68,0.5); color:#F87171; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.75rem; cursor:pointer;">
                                Cancel
                              </button>
                            </form>
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
            <?php foreach ($availableTrips as $t): ?>
              <div class="trip-booking-card">
                <div>
                  <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:0.75rem;">
                    <span style="background:rgba(56,189,248,0.15); color:#38BDF8; border:1px solid rgba(56,189,248,0.3); font-size:0.7rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:4px;">
                      <?php echo htmlspecialchars($t['routeType']); ?>
                    </span>
                    <span style="font-size:0.8rem; color:#34D399; font-weight:600;">
                      <?php echo $t['availableSeats']; ?> seats left
                    </span>
                  </div>

                  <h3 style="font-size:1.1rem; font-weight:700; color:#fff; margin-bottom:0.35rem;">
                    <?php echo htmlspecialchars($t['routeName']); ?>
                  </h3>
                  <div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:1rem;">
                    <?php echo htmlspecialchars($t['origin']); ?> → <?php echo htmlspecialchars($t['destination']); ?>
                  </div>

                  <div style="display:flex; justify-content:space-between; font-size:0.85rem; background:var(--bg-card-inner); padding:0.6rem 0.75rem; border-radius:6px; margin-bottom:1rem;">
                    <div>
                      <div style="color:var(--text-muted); font-size:0.7rem;">DEPARTURE</div>
                      <div style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($t['departureTime']); ?></div>
                    </div>
                    <div style="text-align:right;">
                      <div style="color:var(--text-muted); font-size:0.7rem;">DATE</div>
                      <div style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($t['tripDateFormatted']); ?></div>
                    </div>
                  </div>
                </div>

                <div style="border-top:1px solid var(--border-subtle); padding-top:1rem; display:flex; justify-content:space-between; align-items:center;">
                  <div>
                    <span style="font-size:0.75rem; color:var(--text-muted);">Standard Fare</span>
                    <div style="font-size:1.15rem; font-weight:800; color:#fff;">LKR <?php echo number_format((float)$t['fareAmount'], 2); ?></div>
                  </div>

                  <form method="POST">
                    <input type="hidden" name="action" value="bookTrip">
                    <input type="hidden" name="tripId" value="<?php echo $t['tripId']; ?>">
                    <input type="hidden" name="paymentMethod" value="CARD">
                    <button type="submit" class="btn-primary" style="padding:0.5rem 1rem; font-size:0.85rem;">
                      Book Ticket
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
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
