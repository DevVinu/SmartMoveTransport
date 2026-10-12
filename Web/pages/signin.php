<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';

// Logout
if (isset($_GET['logout'])) {
  Auth::logout();
  header('Location: signin.php?logged_out=1');
  exit;
}

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

if (isset($_GET['registered'])) {
  $successMessage = 'Account created successfully! You can now sign in.';
} elseif (isset($_GET['logged_out'])) {
  $successMessage = 'You have been signed out successfully.';
} elseif (isset($_GET['error'])) {
  if ($_GET['error'] === 'unauthorized') {
    $errorMessage = 'Please sign in to access that page.';
  } elseif ($_GET['error'] === 'forbidden') {
    $errorMessage = 'Access denied: Your account does not have permission for that portal.';
  }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if (empty($email) || empty($password)) {
    $errorMessage = 'Please enter both your email and password.';
  } else {
    $user = User::authenticate($email, $password);

    if ($user) {
      $status = strtoupper($user['accountstatus'] ?? $user['accountStatus'] ?? 'ACTIVE');
      if ($status === 'SUSPENDED') {
        $errorMessage = 'This account has been suspended. Please contact fleet administration.';
      } elseif ($status === 'INACTIVE') {
        $errorMessage = 'This account is inactive. Please verify your email or contact support.';
      } else {
        Auth::login($user);

        $userRole = strtoupper($user['role'] ?? 'PASSENGER');
        if ($userRole === 'ADMIN') {
          header('Location: dashboard.php');
        } elseif ($userRole === 'STAFF') {
          header('Location: staff.php');
        } elseif ($userRole === 'DRIVER') {
          header('Location: driver.php');
        } else {
          header('Location: passenger.php');
        }
        exit;
      }
    } else {
      $errorMessage = 'Invalid email or password. Please verify your credentials.';
    }
  }
}

$pageTitle = "Sign In | SmartMove Transport";
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
  <aside class="auth-image-col signin-bg">
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
        <h2 class="auth-image-headline">Move with comfort, speed &amp; precision.</h2>
        <p class="auth-image-subtext">
          Access Sri Lanka's premiere unified transport platform for executive city travel, intercity coaches, and 24/7
          on-demand courier dispatch.
        </p>


      </div>
    </div>
  </aside>

  <!-- Sign in form -->
  <main class="auth-form-col">
    <!-- Top nav -->
    <header class="auth-top-nav-bar">
      <a href="../index.php" class="btn-ghost-light">← Back to Home</a>
      <a href="signup.php" class="btn-pill-primary">Sign Up</a>
    </header>

    <!-- Form container -->
    <div class="auth-form-box">
      <h1 class="auth-title">Welcome back</h1>
      <p class="auth-subtitle">Sign in to access your SmartMove portal.</p>

      <?php if (!empty($errorMessage)): ?>
        <div class="alert-box alert-error">
          ⚠️ <?php echo htmlspecialchars($errorMessage); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($successMessage)): ?>
        <div class="alert-box alert-success">
          ✓ <?php echo htmlspecialchars($successMessage); ?>
        </div>
      <?php endif; ?>

      <form class="auth-form" action="signin.php" method="POST">
        <div class="form-group">
          <label for="loginEmail" class="form-label">Email Address</label>
          <input type="email" name="email" id="loginEmail" class="form-control" placeholder="name@smartmove.lk" required
            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <label for="loginPassword" class="form-label">Password</label>
            <a href="#" onclick="alert('Password reset instructions dispatched to your email.'); return false;"
              style="font-size: 0.82rem; color: var(--blue-light); text-decoration:none;">Forgot password?</a>
          </div>
          <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••"
            required>
        </div>

        <div
          style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-sub); margin-top: 0.2rem;">
          <input type="checkbox" id="rememberMe" name="remember"
            style="accent-color: var(--blue-primary); cursor:pointer;">
          <label for="rememberMe" style="cursor:pointer;">Keep me signed in on this device</label>
        </div>

        <button type="submit" class="btn-auth-submit">Sign In to Account</button>
      </form>

      <div class="auth-footer-text">
        Don't have an account yet? <a href="signup.php">Create an account</a>
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