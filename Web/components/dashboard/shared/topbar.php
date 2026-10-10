<?php
// Shared Dashboard Topbar Component
$role = isset($role) ? strtolower($role) : 'admin';
$initials = isset($profile['initials']) ? $profile['initials'] : 'VD';
$name = isset($profile['name']) ? $profile['name'] : 'User';
?>
<header class="dash-topbar">
  
  <div class="topbar-left">
    <button class="btn-hamburger" id="hamburgerBtn" title="Toggle Sidebar" aria-label="Toggle navigation">
      ☰
    </button>
    <div class="breadcrumb-path">
      <a href="../index.php">Home</a>
      <span>›</span>
      <a href="dashboard.php">Dashboard</a>
      <span>›</span>
      <span class="current"><?php echo ucfirst(htmlspecialchars($role)); ?> Portal</span>
    </div>
  </div>

  <!-- Search Input -->
  <div class="topbar-search-container">
    <span class="search-icon-svg">🔍</span>
    <input type="text" class="topbar-search-input" id="dashSearch" placeholder="Search rides, fleet ID, or accounts...">
    <span class="kbd-shortcut">⌘K</span>
  </div>

  <!-- Right Actions & Role Switcher -->
  <div class="topbar-right">
    
    <!-- 4-Actor Switcher Tabs -->
    <div class="chart-segmented-control" style="background-color: var(--dash-card-bg); border-radius: 999px; padding: 0.2rem;">
      <a href="dashboard.php?role=admin" class="segment-btn <?php echo $role === 'admin' ? 'active' : ''; ?>" style="text-decoration:none;">Admin</a>
      <a href="dashboard.php?role=staff" class="segment-btn <?php echo $role === 'staff' ? 'active' : ''; ?>" style="text-decoration:none;">Staff</a>
      <a href="dashboard.php?role=driver" class="segment-btn <?php echo $role === 'driver' ? 'active' : ''; ?>" style="text-decoration:none;">Driver</a>
      <a href="dashboard.php?role=passenger" class="segment-btn <?php echo $role === 'passenger' ? 'active' : ''; ?>" style="text-decoration:none;">Passenger</a>
    </div>

    <a href="../index.php" class="btn-docs-pill" title="Public Website">
      <span>🌐</span>
      <span>Live Site</span>
    </a>

    <button class="btn-topbar-icon" title="Notifications" aria-label="Notifications" style="position: relative;">
      🔔
      <span style="position: absolute; top: 7px; right: 7px; width: 6px; height: 6px; background-color: var(--dash-blue); border-radius: 50%;"></span>
    </button>

    <div class="topbar-user-avatar" title="<?php echo htmlspecialchars($name); ?> (<?php echo ucfirst(htmlspecialchars($role)); ?>)">
      <?php echo htmlspecialchars($initials); ?>
    </div>
  </div>

</header>
