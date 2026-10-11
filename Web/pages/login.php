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

if (isset($_GET['registered'])) {
    $successMessage = 'Account created successfully! You can now log in.';
} elseif (isset($_GET['logged_out'])) {
    $successMessage = 'You have been signed out successfully.';
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] === 'unauthorized') {
        $errorMessage = 'Please sign in to access that page.';
    } elseif ($_GET['error'] === 'forbidden') {
        $errorMessage = 'Access denied: Your account does not have permission for that portal.';
    }
}

// Handle Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $roleTab  = strtoupper(trim($_POST['role'] ?? 'PASSENGER'));

    if (empty($email) || empty($password)) {
        $errorMessage = 'Please enter both your email and password.';
    } else {
        $user = User::authenticate($email, $password);

        if ($user) {
            // Check account status
            $status = strtoupper($user['accountstatus'] ?? $user['accountStatus'] ?? 'ACTIVE');
            if ($status === 'SUSPENDED') {
                $errorMessage = 'This account has been suspended. Please contact fleet administration.';
            } elseif ($status === 'INACTIVE') {
                $errorMessage = 'This account is inactive. Please verify your email or contact support.';
            } else {
                // Successful Login
                Auth::login($user);
                
                // Redirect to dashboard with role
                $userRole = strtolower($user['role'] ?? 'passenger');
                header('Location: dashboard.php?role=' . urlencode($userRole));
                exit;
            }
        } else {
            $errorMessage = 'Invalid email or password. Please verify your credentials.';
        }
    }
}

$pageTitle = "Log In | SmartMove Transport";
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
    .alert-success {
      background-color: rgba(16, 185, 129, 0.15);
      border: 1px solid rgba(16, 185, 129, 0.35);
      color: #6EE7B7;
    }
  </style>
</head>
<body class="auth-page">

  <header class="auth-header">
    <a href="../index.php" class="brand-logo">SmartMove<span class="blue-dot">.</span></a>
    <div style="display: flex; gap: 1rem; align-items: center;">
      <a href="../index.php" class="btn-ghost-light">← Back to Home</a>
      <a href="signup.php" class="btn-pill-primary">Sign Up</a>
    </div>
  </header>

  <main class="auth-container">
    <div class="auth-card">
      <h1 class="auth-title">Welcome back</h1>
      <p class="auth-subtitle">Log in to access your SmartMove portal.</p>

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

      <!-- Role Selection Tabs -->
      <div class="role-tabs">
        <button type="button" class="role-btn active" data-role="PASSENGER">Passenger</button>
        <button type="button" class="role-btn" data-role="DRIVER">Driver</button>
        <button type="button" class="role-btn" data-role="STAFF">Staff</button>
        <button type="button" class="role-btn" data-role="ADMIN">Admin</button>
      </div>

      <form class="auth-form" action="login.php" method="POST">
        <input type="hidden" name="role" id="userRoleInput" value="PASSENGER">

        <div class="form-group">
          <label for="loginEmail" class="form-label">Email Address</label>
          <input type="email" name="email" id="loginEmail" class="form-control" placeholder="name@smartmove.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <label for="loginPassword" class="form-label">Password</label>
            <a href="#" onclick="alert('Password reset link has been dispatched to your email.'); return false;" style="font-size: 0.8rem; color: var(--blue-light);">Forgot password?</a>
          </div>
          <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-sub); margin-top: 0.3rem;">
          <input type="checkbox" id="rememberMe" name="remember" style="accent-color: var(--blue-primary);">
          <label for="rememberMe">Keep me signed in on this device</label>
        </div>

        <button type="submit" class="btn-auth-submit">Sign In to Account</button>
      </form>

      <div class="auth-footer-text">
        Don't have an account yet? <a href="signup.php">Create an account</a>
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
        document.getElementById('userRoleInput').value = btn.dataset.role;
      });
    });
  </script>
</body>
</html>
