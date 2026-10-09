<?php
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
      <p class="auth-subtitle">Select your account type to access your SmartMove portal.</p>

      <div class="role-tabs">
        <button type="button" class="role-btn active" data-role="PASSENGER">Passenger</button>
        <button type="button" class="role-btn" data-role="DRIVER">Driver Partner</button>
        <button type="button" class="role-btn" data-role="STAFF">Staff / Admin</button>
      </div>

      <form class="auth-form" action="dashboard.php" method="GET">
        <input type="hidden" name="role" id="userRoleInput" value="PASSENGER">

        <div class="form-group">
          <label for="loginEmail" class="form-label">Email or Username</label>
          <input type="text" id="loginEmail" class="form-control" placeholder="name@example.com or username" required>
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <label for="loginPassword" class="form-label">Password</label>
            <a href="#" style="font-size: 0.8rem; color: var(--blue-light);">Forgot password?</a>
          </div>
          <input type="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-sub); margin-top: 0.3rem;">
          <input type="checkbox" id="rememberMe" style="accent-color: var(--blue-primary);">
          <label for="rememberMe">Keep me signed in on this device</label>
        </div>

        <button type="submit" class="btn-auth-submit">Log In to Account</button>
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
