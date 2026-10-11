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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role            = strtoupper(trim($_POST['role'] ?? 'PASSENGER'));
    $firstName       = trim($_POST['firstName'] ?? '');
    $lastName        = trim($_POST['lastName'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $phone           = trim($_POST['phone'] ?? '');
    $nic             = trim($_POST['nic'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Driver specific
    $licenseNo       = trim($_POST['licenseNo'] ?? '');
    $vehicleType     = trim($_POST['vehicleType'] ?? 'LUXURYCAR');

    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($nic) || empty($password)) {
        $errorMessage = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match.';
    } elseif ($role === 'DRIVER' && empty($licenseNo)) {
        $errorMessage = 'Driver License Number is mandatory for Driver Partner applications.';
    } else {
        // Uniqueness checks in Oracle if DB is active
        if (User::emailExists($email)) {
            $errorMessage = 'An account with this email address already exists. Please log in.';
        } elseif (User::nicExists($nic)) {
            $errorMessage = 'An account with this National ID (NIC) already exists.';
        } else {
            $userData = [
                'firstName' => $firstName,
                'lastName'  => $lastName,
                'email'     => $email,
                'phone'     => $phone,
                'nic'       => $nic,
                'password'  => $password
            ];

            $success = false;

            if ($role === 'DRIVER') {
                $userData['licenseNo'] = $licenseNo;
                $userData['licenseType'] = $vehicleType;
                $success = User::createDriver($userData);
            } else {
                $userData['address'] = 'Colombo, Western Province';
                $userData['preferredPaymentMethod'] = 'CARD';
                $success = User::createPassenger($userData);
            }

            if ($success) {
                header('Location: login.php?registered=1');
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
  <link rel="stylesheet" href="../css/navbar.css">
  <link rel="stylesheet" href="../css/auth.css">
  <link rel="stylesheet" href="../css/footer.css">
  <style>
    .alert-box {
      padding: 0.85rem 1rem;
      border-radius: 8px;
      font-size: 0.88rem;
      margin-bottom: 1.25rem;
      line-height: 1.4;
    }
    .alert-error {
      background-color: rgba(239, 68, 68, 0.15);
      border: 1px solid rgba(239, 68, 68, 0.35);
      color: #FCA5A5;
    }
    .driver-fields {
      display: none;
    }
    .driver-fields.show {
      display: block;
    }
  </style>
</head>
<body class="auth-page">

  <header class="auth-header">
    <a href="../index.php" class="brand-logo">SmartMove<span class="blue-dot">.</span></a>
    <div style="display: flex; gap: 1rem; align-items: center;">
      <a href="../index.php" class="btn-ghost-light">← Back to Home</a>
      <a href="login.php" class="btn-ghost-light">Log In</a>
    </div>
  </header>

  <main class="auth-container" style="max-width: 540px;">
    <div class="auth-card">
      <h1 class="auth-title">Create your account</h1>
      <p class="auth-subtitle">Join SmartMove to book instant rides or start earning as a driver.</p>

      <?php if (!empty($errorMessage)): ?>
        <div class="alert-box alert-error">
          ⚠️ <?php echo htmlspecialchars($errorMessage); ?>
        </div>
      <?php endif; ?>

      <div class="role-tabs">
        <button type="button" class="role-btn <?php echo (!isset($_POST['role']) || $_POST['role'] === 'PASSENGER') ? 'active' : ''; ?>" data-role="PASSENGER">Passenger Account</button>
        <button type="button" class="role-btn <?php echo (isset($_POST['role']) && $_POST['role'] === 'DRIVER') ? 'active' : ''; ?>" data-role="DRIVER">Driver Partner Application</button>
      </div>

      <form class="auth-form" action="signup.php" method="POST">
        <input type="hidden" name="role" id="userRoleInput" value="<?php echo htmlspecialchars($_POST['role'] ?? 'PASSENGER'); ?>">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="firstName" class="form-label">First Name</label>
            <input type="text" name="firstName" id="firstName" class="form-control" placeholder="John" required value="<?php echo htmlspecialchars($_POST['firstName'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label for="lastName" class="form-label">Last Name</label>
            <input type="text" name="lastName" id="lastName" class="form-control" placeholder="Doe" required value="<?php echo htmlspecialchars($_POST['lastName'] ?? ''); ?>">
          </div>
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="tel" name="phone" id="phone" class="form-control" placeholder="+94 77 123 4567" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label for="nic" class="form-label">National ID (NIC)</label>
            <input type="text" name="nic" id="nic" class="form-control" placeholder="199512345678" required value="<?php echo htmlspecialchars($_POST['nic'] ?? ''); ?>">
          </div>
        </div>

        <!-- Extra fields for Driver Application -->
        <div class="driver-fields <?php echo (isset($_POST['role']) && $_POST['role'] === 'DRIVER') ? 'show' : ''; ?>" id="driverFields">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label for="licenseNo" class="form-label">Driving License No.</label>
              <input type="text" name="licenseNo" id="licenseNo" class="form-control" placeholder="B1234567" value="<?php echo htmlspecialchars($_POST['licenseNo'] ?? ''); ?>">
            </div>
            <div class="form-group">
              <label for="vehicleType" class="form-label">Vehicle Category</label>
              <select name="vehicleType" id="vehicleType" class="form-control" style="background-color: var(--bg-surface-dark); color: #fff;">
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
            <input type="password" name="confirmPassword" id="confirmPassword" class="form-control" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn-auth-submit">Create SmartMove Account</button>
      </form>

      <div class="auth-footer-text">
        Already have an account? <a href="login.php">Log in here</a>
      </div>
    </div>
  </main>

  <footer style="text-align: center; padding: 1.5rem; font-size: 0.85rem; color: var(--text-sub); border-top: 1px solid rgba(255,255,255,0.08);">
    © 2026 SmartMove Transport. All rights reserved.
  </footer>

  <script>
    document.querySelectorAll('.role-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const role = btn.dataset.role;
        document.getElementById('userRoleInput').value = role;
        
        const driverFields = document.getElementById('driverFields');
        if (role === 'DRIVER') {
          driverFields.classList.add('show');
        } else {
          driverFields.classList.remove('show');
        }
      });
    });
  </script>
</body>
</html>
