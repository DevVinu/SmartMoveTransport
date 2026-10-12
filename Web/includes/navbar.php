<?php
require_once __DIR__ . '/auth.php';
$isNavLoggedIn = Auth::check();
$navUser = $isNavLoggedIn ? Auth::user() : null;
$navRole = $navUser ? strtoupper($navUser['role'] ?? 'PASSENGER') : '';
$navPortalLink = 'pages/passenger.php';
if ($navRole === 'ADMIN') {
    $navPortalLink = 'pages/dashboard.php';
} elseif ($navRole === 'STAFF') {
    $navPortalLink = 'pages/staff.php';
} elseif ($navRole === 'DRIVER') {
    $navPortalLink = 'pages/driver.php';
}
$navFirstName = htmlspecialchars($navUser['firstName'] ?? $navUser['firstname'] ?? 'Account');
?>
<header class="uber-header">
  <div class="uber-nav-container">
    <div class="nav-left-group">
      <a href="index.php" class="brand-logo">SmartMove<span class="blue-dot">.</span></a>
      <ul class="nav-menu-links">
        <li><a href="#ride">Ride</a></li>
        <li><a href="#fleet">Fleet</a></li>
        <li><a href="#earn">Drive</a></li>
        <li><a href="#business">Business</a></li>
        <li><a href="<?php echo $isNavLoggedIn ? $navPortalLink : 'pages/signin.php'; ?>">Dashboard</a></li>
        <li><a href="#about">About ▾</a></li>
      </ul>
    </div>

    <div class="nav-right-group">
      <?php if ($isNavLoggedIn): ?>
        <a href="<?php echo $navPortalLink; ?>" class="nav-link-item" style="color:var(--blue-light);font-weight:600;">
           <?php echo $navFirstName; ?> (<?php echo $navRole; ?>)
        </a>
        <a href="pages/logout.php" class="btn-signup-pill" style="background:rgba(239,68,68,0.15);color:#F87171;border:1px solid rgba(239,68,68,0.4);">
          Sign out
        </a>
      <?php else: ?>
        <a href="pages/signin.php" class="nav-link-item">Sign in</a>
        <a href="pages/signup.php" class="btn-signup-pill">Sign up</a>
      <?php endif; ?>
      <div class="mobile-toggle" id="mobileNavToggle" aria-label="Toggle mobile menu"></div>
    </div>
  </div>
</header>
