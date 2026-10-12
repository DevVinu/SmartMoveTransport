<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';

// Redirect logged-in users
if (Auth::check()) {
  $currentUser = Auth::user();
  $role = strtoupper($currentUser['role'] ?? 'PASSENGER');
  if ($role === 'ADMIN') {
    header('Location: dashboard.php');
  } elseif ($role === 'STAFF') {
    header('Location: staff.php');
  } elseif ($role === 'DRIVER') {
    header('Location: driver.php');
  } else {
    header('Location: passenger.php');
  }
  exit;
}

$errorMessage = '';
$successMessage = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $role = strtoupper(trim($_POST['role'] ?? 'PASSENGER'));
  $firstName = trim($_POST['firstName'] ?? '');
  $lastName = trim($_POST['lastName'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $nic = trim($_POST['nic'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $confirmPassword = trim($_POST['confirmPassword'] ?? '');

  if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
    $errorMessage = 'Please fill in all required fields.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errorMessage = 'Please enter a valid email address.';
  } elseif ($password !== $confirmPassword) {
    $errorMessage = 'Passwords do not match. Please verify and re-enter.';
  } elseif (strlen($password) < 6) {
    $errorMessage = 'Password must be at least 6 characters long.';
  } else {
    if (User::findByEmail($email)) {
      $errorMessage = 'An account with this email address already exists. Please sign in instead.';
    } elseif (!empty($nic) && User::nicExists($nic)) {
      $errorMessage = 'An account with this National ID (NIC) already exists. Please check your details.';
    } else {
      $userData = [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'email' => $email,
        'phone' => $phone,
        'nic' => $nic,
        'password' => $password,
        'role' => $role,
        'accountStatus' => 'ACTIVE'
      ];

      if ($role === 'DRIVER') {
        $userData['licenseNo'] = trim($_POST['licenseNo'] ?? '');
        $userData['licenseNumber'] = trim($_POST['licenseNo'] ?? '');
        $userData['licenseType'] = trim($_POST['licenseType'] ?? 'HEAVY_COACH');
        $userData['licenseExpiryDate'] = trim($_POST['licenseExpiryDate'] ?? date('Y-m-d', strtotime('+3 years')));
        $userData['driverStatus'] = 'AVAILABLE';
      } elseif ($role === 'STAFF') {
        $userData['position'] = trim($_POST['position'] ?? 'Operations Officer');
        $userData['staffStatus'] = 'ACTIVE';
      } elseif ($role === 'ADMIN') {
        $userData['adminCode'] = trim($_POST['adminCode'] ?? ('ADM-' . rand(100, 999)));
        $userData['privilegeLevel'] = trim($_POST['privilegeLevel'] ?? 'OPS_ADMIN');
        $userData['department'] = trim($_POST['department'] ?? 'Operations HQ');
      } else {
        $userData['address'] = trim($_POST['address'] ?? 'Colombo, Sri Lanka');
        $userData['preferredPaymentMethod'] = trim($_POST['preferredPaymentMethod'] ?? 'CARD');
      }

      $createdId = User::createUserWithRole($userData);
      if ($createdId) {
        header('Location: signin.php?registered=1');
        exit;
      } else {
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

  <!-- Left banner -->
  <aside class="auth-image-col signup-bg">
    <div class="auth-image-overlay"></div>

    <div class="auth-image-content">
      <!-- Brand logo -->
      <div>
        <a href="../index.php" class="auth-brand-badge-link" title="SmartMove Home">
          <span class="auth-brand-sq-box">S</span>
          <span>SmartMove<span style="color:var(--blue-light);">.</span></span>
        </a>
      </div>

      <!-- Caption -->
      <div class="auth-image-caption-area">
        <h2 class="auth-image-headline">Join the new era of Sri Lankan mobility.</h2>
        <p class="auth-image-subtext">
          Whether you want to ride in premium comfort or partner with us to earn on your own flexible schedule,
          SmartMove provides the ultimate transport network.
        </p>


      </div>
    </div>
  </aside>

  <!-- Sign up form -->
  <main class="auth-form-col">
    <!-- Top nav -->
    <header class="auth-top-nav-bar">
      <a href="../index.php" class="btn-ghost-light">← Back to Home</a>
      <a href="signin.php" class="btn-ghost-light">Sign In</a>
    </header>

    <!-- Form container -->
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
          data-role="PASSENGER">Passenger</button>
        <button type="button"
          class="role-btn <?php echo (isset($_POST['role']) && $_POST['role'] === 'DRIVER') ? 'active' : ''; ?>"
          data-role="DRIVER">Driver</button>
        <button type="button"
          class="role-btn <?php echo (isset($_POST['role']) && $_POST['role'] === 'STAFF') ? 'active' : ''; ?>"
          data-role="STAFF">Staff</button>
        <button type="button"
          class="role-btn <?php echo (isset($_POST['role']) && $_POST['role'] === 'ADMIN') ? 'active' : ''; ?>"
          data-role="ADMIN">Admin</button>
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

        <!-- Passenger fields -->
        <div class="actor-signup-fields <?php echo (!isset($_POST['role']) || $_POST['role'] === 'PASSENGER') ? 'show' : ''; ?>"
          id="passengerFields">
          <div class="form-group">
            <label for="address" class="form-label">Residential Address</label>
            <input type="text" name="address" id="address" class="form-control" placeholder="No. 45, Galle Road, Colombo 03"
              value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label for="preferredPaymentMethod" class="form-label">Preferred Payment Method</label>
            <select name="preferredPaymentMethod" id="preferredPaymentMethod" class="form-control"
              style="background-color: var(--bg-card-inner); color: #fff;">
              <option value="CARD" <?php echo (($_POST['preferredPaymentMethod'] ?? '') === 'CARD') ? 'selected' : ''; ?>>Credit / Debit Card</option>
              <option value="CASH" <?php echo (($_POST['preferredPaymentMethod'] ?? '') === 'CASH') ? 'selected' : ''; ?>>Cash at Boarding</option>
              <option value="WALLET" <?php echo (($_POST['preferredPaymentMethod'] ?? '') === 'WALLET') ? 'selected' : ''; ?>>Digital Wallet</option>
              <option value="ONLINE" <?php echo (($_POST['preferredPaymentMethod'] ?? '') === 'ONLINE') ? 'selected' : ''; ?>>Online Net Banking</option>
            </select>
          </div>
        </div>

        <!-- Driver fields -->
        <div class="actor-signup-fields <?php echo (isset($_POST['role']) && $_POST['role'] === 'DRIVER') ? 'show' : ''; ?>"
          id="driverFields">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label for="licenseNo" class="form-label">Driving License No.</label>
              <input type="text" name="licenseNo" id="licenseNo" class="form-control" placeholder="B1234567"
                value="<?php echo htmlspecialchars($_POST['licenseNo'] ?? ''); ?>">
            </div>
            <div class="form-group">
              <label for="licenseType" class="form-label">License Class</label>
              <select name="licenseType" id="licenseType" class="form-control"
                style="background-color: var(--bg-card-inner); color: #fff;">
                <option value="HEAVY_COACH">Heavy Coach (Buses)</option>
                <option value="COMMERCIAL_VAN">Commercial Van</option>
                <option value="EXECUTIVE_SEDAN">Executive Sedan</option>
                <option value="THREE_WHEELER">Three-Wheel Tuk-Tuk</option>
                <option value="MOTORBIKE">Express Courier Bike</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="licenseExpiryDate" class="form-label">License Expiry Date</label>
            <input type="date" name="licenseExpiryDate" id="licenseExpiryDate" class="form-control"
              value="<?php echo htmlspecialchars($_POST['licenseExpiryDate'] ?? date('Y-m-d', strtotime('+3 years'))); ?>">
          </div>
        </div>

        <!-- Staff fields -->
        <div class="actor-signup-fields <?php echo (isset($_POST['role']) && $_POST['role'] === 'STAFF') ? 'show' : ''; ?>"
          id="staffFields">
          <div class="form-group">
            <label for="position" class="form-label">Staff Position</label>
            <select name="position" id="position" class="form-control"
              style="background-color: var(--bg-card-inner); color: #fff;">
              <option value="Dispatch Controller">Dispatch Controller</option>
              <option value="Fleet Maintenance Supervisor">Fleet Maintenance Supervisor</option>
              <option value="Ticketing Officer">Ticketing Officer</option>
              <option value="Customer Support Officer">Customer Support Officer</option>
              <option value="Operations Executive">Operations Executive</option>
            </select>
          </div>
        </div>

        <!-- Admin fields -->
        <div class="actor-signup-fields <?php echo (isset($_POST['role']) && $_POST['role'] === 'ADMIN') ? 'show' : ''; ?>"
          id="adminFields">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label for="adminCode" class="form-label">Admin Security Code</label>
              <input type="text" name="adminCode" id="adminCode" class="form-control" placeholder="ADM-101"
                value="<?php echo htmlspecialchars($_POST['adminCode'] ?? ''); ?>">
            </div>
            <div class="form-group">
              <label for="privilegeLevel" class="form-label">Privilege Level</label>
              <select name="privilegeLevel" id="privilegeLevel" class="form-control"
                style="background-color: var(--bg-card-inner); color: #fff;">
                <option value="OPS_ADMIN">Operations Admin</option>
                <option value="SUPER_ADMIN">Super Administrator</option>
                <option value="AUDITOR">Compliance Auditor</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="department" class="form-label">Department</label>
            <input type="text" name="department" id="department" class="form-control" placeholder="Central Operations Command"
              value="<?php echo htmlspecialchars($_POST['department'] ?? 'Operations HQ'); ?>">
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

    <!-- Footer -->
    <footer class="auth-col-footer">
      © 2026 SmartMove Transport Technologies. All rights reserved.
    </footer>
  </main>

  <!-- Script -->
  <script src="../js/auth.js"></script>
</body>

</html>