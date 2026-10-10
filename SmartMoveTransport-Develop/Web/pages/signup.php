<?php
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
</head>
<body class="auth-page">

  <header class="auth-header">
    <a href="../index.php" class="brand-logo">SmartMove<span class="blue-dot">.</span></a>
    <div style="display: flex; gap: 1rem; align-items: center;">
      <a href="../index.php" class="btn-ghost-light">← Back to Home</a>
      <a href="login.php" class="btn-ghost-light">Log In</a>
    </div>
  </header>

  <main class="auth-container" style="max-width: 520px;">
    <div class="auth-card">
      <h1 class="auth-title">Create your account</h1>
      <p class="auth-subtitle">Join SmartMove to book instant rides or start earning as a driver.</p>

      <div class="role-tabs">
        <button type="button" class="role-btn active" data-role="PASSENGER">Passenger Account</button>
        <button type="button" class="role-btn" data-role="DRIVER">Driver Partner Application</button>
      </div>

      <form class="auth-form" action="login.php" method="GET" onsubmit="alert('Registration submitted! Please log in.');">
        <input type="hidden" name="role" id="userRoleInput" value="PASSENGER">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="firstName" class="form-label">First Name</label>
            <input type="text" id="firstName" class="form-control" placeholder="John" required>
          </div>
          <div class="form-group">
            <label for="lastName" class="form-label">Last Name</label>
            <input type="text" id="lastName" class="form-control" placeholder="Doe" required>
          </div>
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" id="email" class="form-control" placeholder="name@example.com" required>
        </div>

        <div class="form-group">
          <label for="phone" class="form-label">Phone Number</label>
          <input type="tel" id="phone" class="form-control" placeholder="+94 77 123 4567" required>
        </div>

        <div class="driver-fields" id="driverFields">
          <div class="form-group">
            <label for="licenseNo" class="form-label">Driver License No.</label>
            <input type="text" id="licenseNo" class="form-control" placeholder="B1234567">
          </div>
          <div class="form-group">
            <label for="vehicleType" class="form-label">Vehicle Type</label>
            <input type="text" id="vehicleType" class="form-control" placeholder="Car / Van / Three-Wheel">
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" class="form-control" placeholder="••••••••" required>
          </div>
          <div class="form-group">
            <label for="confirmPassword" class="form-label">Confirm Password</label>
            <input type="password" id="confirmPassword" class="form-control" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn-auth-submit">Create Account</button>
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
