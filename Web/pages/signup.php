<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';

// If already logged in, redirect straight to dashboard
if (Auth::check()) {
  header('Location: dashboard.php');
  exit;
}

$errorMessage = '';
$successMessage = '';

// Handle Registration Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $role = strtoupper(trim($_POST['role'] ?? 'PASSENGER'));
  $firstName = trim($_POST['firstName'] ?? '');
  $lastName = trim($_POST['lastName'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $nic = trim($_POST['nic'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $confirmPassword = trim($_POST['confirmPassword'] ?? '');

  // Basic Validation
  if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
    $errorMessage = 'Please fill in all required fields.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errorMessage = 'Please enter a valid email address.';
  } elseif ($password !== $confirmPassword) {
    $errorMessage = 'Passwords do not match. Please verify and re-enter.';
  } elseif (strlen($password) < 6) {
    $errorMessage = 'Password must be at least 6 characters long.';
  } else {
    // Check if email already registered
    $existing = User::findByEmail($email);
    if ($existing) {
      $errorMessage = 'An account with this email address already exists. Please sign in instead.';
    } else {
      $userData = [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'email' => $email,
        'phone' => $phone,
        'nic' => $nic,
        'password' => $password
      ];

      if ($role === 'DRIVER') {
        $userData['licenseNumber'] = trim($_POST['licenseNo'] ?? '');
        $userData['vehicleType'] = trim($_POST['vehicleType'] ?? 'LUXURYCAR');
        $success = User::createDriver($userData);
      } else {
        $userData['preferredPaymentMethod'] = 'CARD';
        $success = User::createPassenger($userData);
      }

      if ($success) {
        header('Location: signin.php?registered=1');
        exit;
      } else {
        // If Oracle is not connected, provide a helpful note
        if (Database::getOracle() === null) {
          $errorMessage = 'Database connection error. Please ensure Oracle Database is running and try again.';
        } else {
          $errorMessage = 'Unable to register account at this time. Please check your details and try again.';
        }
      }
    }
  }
}

$pageTitle = "Sign Up | SmartMove Transport";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>

  <link rel="stylesheet" href="../css/variables.css">
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/auth.css">
</head>

<body class="auth-split-page">

  <!-- ========================================================================
       LEFT COLUMN: IMAGE SHOWCASE BANNER & BRAND OVERLAY
       ======================================================================== -->
  <aside class="auth-image-col signup-bg">
    <div class="auth-image-overlay"></div>

    <div class="auth-image-content">
      <!-- Top Brand Logo -->
      <div>
        <a href="../index.php" class="auth-brand-badge-link" title="SmartMove Home">
          <span class="auth-brand-sq-box">S</span>
          <span>SmartMove<span style="color:var(--blue-light);">.</span></span>
        </a>
      </div>

      <!-- Bottom Caption & Features -->
      <div class="auth-image-caption-area">
        <h2 class="auth-image-headline">Join the new era of Sri Lankan mobility.</h2>
        <p class="auth-image-subtext">
          Whether you want to ride in premium comfort or partner with us to earn on your own flexible schedule,
          SmartMove provides the ultimate transport network.
        </p>


      </div>
    </div>
  </aside>

  <!-- ========================================================================
       RIGHT COLUMN: INTERACTIVE SIGN UP FORM
       ======================================================================== -->
  <main class="auth-form-col">
    <!-- Top Bar Navigation -->
    <header class="auth-top-nav-bar">
      <a href="../index.php" class="btn-ghost-light">← Back to Home</a>
      <a href="signin.php" class="btn-ghost-light">Sign In</a>
    </header>

    <!-- Centered Form Container -->
    <div class="auth-form-box wide">
      <h1 class="auth-title">Create your account</h1>
      <p class="auth-subtitle">Join SmartMove to book instant rides or start earning as a driver partner.</p>

      <?php if (!empty($errorMessage)): ?>
        <div class="alert-box alert-error">
          ⚠️ <?php echo htmlspecialchars($errorMessage); ?>
        </div>
      <?php endif; ?>

      <div class="role-tabs">
        <button type="button"
          class="role-btn <?php echo (!isset($_POST['role']) || $_POST['role'] === 'PASSENGER') ? 'active' : ''; ?>"
          data-role="PASSENGER">Passenger Account</button>
        <button type="button"
          class="role-btn <?php echo (isset($_POST['role']) && $_POST['role'] === 'DRIVER') ? 'active' : ''; ?>"
          data-role="DRIVER">Driver Application</button>
      </div>

      <form class="auth-form" action="signup.php" method="POST">
        <input type="hidden" name="role" id="userRoleInput"
          value="<?php echo htmlspecialchars($_POST['role'] ?? 'PASSENGER'); ?>">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="firstName" class="form-label">First Name</label>
            <input type="text" name="firstName" id="firstName" class="form-control" placeholder="John" required
              value="<?php echo htmlspecialchars($_POST['firstName'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label for="lastName" class="form-label">Last Name</label>
            <input type="text" name="lastName" id="lastName" class="form-control" placeholder="Doe" required
              value="<?php echo htmlspecialchars($_POST['lastName'] ?? ''); ?>">
          </div>
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" name="email" id="email" class="form-control" placeholder="name@smartmove.lk" required
            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="tel" name="phone" id="phone" class="form-control" placeholder="+94 77 123 4567" required
              value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label for="nic" class="form-label">National ID (NIC)</label>
            <input type="text" name="nic" id="nic" class="form-control" placeholder="199512345678" required
              value="<?php echo htmlspecialchars($_POST['nic'] ?? ''); ?>">
          </div>
        </div>

        <!-- Extra fields for Driver Application -->
        <div class="driver-fields <?php echo (isset($_POST['role']) && $_POST['role'] === 'DRIVER') ? 'show' : ''; ?>"
          id="driverFields">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label for="licenseNo" class="form-label">Driving License No.</label>
              <input type="text" name="licenseNo" id="licenseNo" class="form-control" placeholder="B1234567"
                value="<?php echo htmlspecialchars($_POST['licenseNo'] ?? ''); ?>">
            </div>
            <div class="form-group">
              <label for="vehicleType" class="form-label">Vehicle Category</label>
              <select name="vehicleType" id="vehicleType" class="form-control"
                style="background-color: var(--bg-card-inner); color: #fff;">
                <option value="LUXURYCAR">Executive Sedan / Car</option>
                <option value="VAN">Van / Chauffeur</option>
                <option value="THREEWHEEL">Three-Wheel Tuk-Tuk</option>
                <option value="BUS">Bus / Coach</option>
                <option value="BIKE">Express Courier Bike</option>
              </select>
            </div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
          </div>
          <div class="form-group">
            <label for="confirmPassword" class="form-label">Confirm Password</label>
            <input type="password" name="confirmPassword" id="confirmPassword" class="form-control"
              placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn-auth-submit">Create SmartMove Account</button>
      </form>

      <div class="auth-footer-text">
        Already have an account? <a href="signin.php">Sign in here</a>
      </div>
    </div>

    <!-- Column Footer -->
    <footer class="auth-col-footer">
      © 2026 SmartMove Transport Technologies. All rights reserved.
    </footer>
  </main>

  <!-- External Clean Script -->
  <script src="../js/auth.js"></script>
</body>

</html>