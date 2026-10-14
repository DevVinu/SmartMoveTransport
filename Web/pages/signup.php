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
  $role = strtoupper(trim($_POST['role'] ?? ''));
  $firstName = trim($_POST['firstName'] ?? '');
  $lastName = trim($_POST['lastName'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $nic = trim($_POST['nic'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $confirmPassword = trim($_POST['confirmPassword'] ?? '');

  if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
    $errorMessage = 'Please fill in all required fields.';
  } elseif (empty($role) || !in_array($role, ['PASSENGER', 'DRIVER'])) {
    $errorMessage = 'Please select whether you want to join as a Passenger or Driver.';
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
      } else {
        $userData['address'] = trim($_POST['address'] ?? 'Colombo, Sri Lanka');
        $userData['preferredPaymentMethod'] = 'CARD';
      }

      $createdId = User::createUserWithRole($userData);
      if ($createdId) {
        // Upload driver license PDF if provided
        if ($role === 'DRIVER' && !empty($_FILES['licensePdf']['tmp_name'])) {
          User::uploadLicenseDocument($createdId, $_FILES['licensePdf']);
        }
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

$selectedRole = strtoupper(trim($_POST['role'] ?? ''));
$pageTitle = "Sign Up | SmartMove Transport";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <link rel="icon" type="image/jpeg" href="../img/logo.jpg">

  <link rel="stylesheet" href="../css/variables.css">
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/auth.css?v=<?php echo filemtime(__DIR__ . '/../css/auth.css'); ?>">
</head>

<body class="auth-split-page">

  <!-- Left banner -->
  <aside class="auth-image-col signup-bg">
    <div class="auth-image-overlay"></div>

    <div class="auth-image-content">
      <!-- Brand logo -->
      <div>
        <a href="../index.php" class="auth-brand-badge-link" title="SmartMove Home" style="display:inline-flex; align-items:center; gap:0.6rem;">
          <img src="../img/logo.jpg" alt="SmartMove Logo" style="width:36px; height:36px; border-radius:8px; object-fit:cover;">
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
      <div style="margin-bottom:1.25rem;">
        <img src="../img/logo.jpg" alt="SmartMove Logo" style="width:58px; height:58px; border-radius:12px; object-fit:cover;">
      </div>
      <h1 class="auth-title">Create your account</h1>
      <p class="auth-subtitle">Join SmartMove to book instant rides, partner as a driver, or manage transit.</p>

      <?php if (!empty($errorMessage)): ?>
        <div class="alert-box alert-error">
           <?php echo htmlspecialchars($errorMessage); ?>
        </div>
      <?php endif; ?>

      <form class="auth-form" action="signup.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="role" id="userRoleInput" value="<?php echo htmlspecialchars($selectedRole); ?>">

        <!-- Basic user details (first) -->
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

        <!-- Account category selection -->
        <div class="form-group" style="margin-top: 0.25rem;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.35rem;">
            <label class="form-label" style="font-weight: 700; color: var(--text-white, #FFFFFF); margin: 0;">
              Choose Account Category
            </label>
            <span style="font-size: 0.75rem; color: var(--text-muted, #94A3B8);">Select your role to view options</span>
          </div>

          <div class="role-tabs" style="margin-bottom: 0.5rem;">
            <button type="button"
              class="role-btn <?php echo ($selectedRole === 'PASSENGER') ? 'active' : ''; ?>"
              data-role="PASSENGER">Passenger</button>
            <button type="button"
              class="role-btn <?php echo ($selectedRole === 'DRIVER') ? 'active' : ''; ?>"
              data-role="DRIVER">Driver</button>
          </div>
        </div>

        <!-- Helper prompt when no category is selected -->
        <div id="noRoleSelectedHint" class="category-prompt-box"
          style="<?php echo empty($selectedRole) ? 'display:block;' : 'display:none;'; ?>">
          Select whether you want to join as a <strong>Passenger</strong> or <strong>Driver</strong> to configure your specific details.
        </div>

        <!-- Passenger fields -->
        <div class="actor-signup-fields <?php echo ($selectedRole === 'PASSENGER') ? 'show' : ''; ?>"
          id="passengerFields"
          style="<?php echo ($selectedRole === 'PASSENGER') ? 'display:flex;' : 'display:none;'; ?>">
          <div style="background: rgba(37, 99, 235, 0.08); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: var(--radius-sm, 6px); padding: 0.65rem 0.85rem; font-size: 0.8rem; color: #93C5FD;">
            Passenger Account: Configure your residential address.
          </div>
          <div class="form-group">
            <label for="address" class="form-label">Residential Address</label>
            <input type="text" name="address" id="address" class="form-control" placeholder="No. 45, Galle Road, Colombo 03"
              value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
          </div>
        </div>

        <!-- Driver fields -->
        <div class="actor-signup-fields <?php echo ($selectedRole === 'DRIVER') ? 'show' : ''; ?>"
          id="driverFields"
          style="<?php echo ($selectedRole === 'DRIVER') ? 'display:flex;' : 'display:none;'; ?>">
          <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: var(--radius-sm, 6px); padding: 0.65rem 0.85rem; font-size: 0.8rem; color: #FCD34D;">
            Driver Partner: Enter your driving license credentials for verification.
          </div>
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
                <option value="HEAVY_COACH" <?php echo (($_POST['licenseType'] ?? '') === 'HEAVY_COACH') ? 'selected' : ''; ?>>Heavy Coach (Buses)</option>
                <option value="COMMERCIAL_VAN" <?php echo (($_POST['licenseType'] ?? '') === 'COMMERCIAL_VAN') ? 'selected' : ''; ?>>Commercial Van</option>
                <option value="EXECUTIVE_SEDAN" <?php echo (($_POST['licenseType'] ?? '') === 'EXECUTIVE_SEDAN') ? 'selected' : ''; ?>>Executive Sedan</option>
                <option value="THREE_WHEELER" <?php echo (($_POST['licenseType'] ?? '') === 'THREE_WHEELER') ? 'selected' : ''; ?>>Three-Wheel Tuk-Tuk</option>
                <option value="MOTORBIKE" <?php echo (($_POST['licenseType'] ?? '') === 'MOTORBIKE') ? 'selected' : ''; ?>>Express Courier Bike</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="licenseExpiryDate" class="form-label">License Expiry Date</label>
            <input type="date" name="licenseExpiryDate" id="licenseExpiryDate" class="form-control"
              value="<?php echo htmlspecialchars($_POST['licenseExpiryDate'] ?? date('Y-m-d', strtotime('+3 years'))); ?>">
          </div>
          <div class="form-group" style="margin-top:0.5rem;">
            <label for="licensePdf" class="form-label" style="display:flex; justify-content:space-between;">
              <span>Driving License Document (PDF / Scan)</span>
              <span style="font-size:0.75rem; color:var(--text-muted);">PDF, JPG, PNG (Max 5MB)</span>
            </label>
            <input type="file" name="licensePdf" id="licensePdf" class="form-control" accept=".pdf,image/png,image/jpeg"
              style="padding:0.4rem; font-size:0.8rem; background-color:var(--bg-card-inner); color:#fff; border:1px dashed var(--border-subtle); cursor:pointer;">
            <small style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">
              Upload your official driving license copy (stored securely in MongoDB document archive).
            </small>
          </div>
        </div>



        <button type="submit" class="btn-auth-submit" id="submitBtn">Create SmartMove Account</button>
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
  <script src="../js/auth.js?v=<?php echo filemtime(__DIR__ . '/../js/auth.js'); ?>"></script>
</body>

</html>