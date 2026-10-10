<?php
// Shared Dashboard Sidebar Component
// Respects rule: NO icons in sidebar menu items
$role = isset($role) ? strtolower($role) : 'admin';

// User metadata according to role
$userProfiles = [
    'admin' => [
        'initials' => 'VD',
        'name' => 'Vinu Dissanayake',
        'roleTitle' => 'System Director',
        'portalBadge' => 'ADMIN'
    ],
    'staff' => [
        'initials' => 'RS',
        'name' => 'Raveen Silva',
        'roleTitle' => 'Fleet Dispatcher',
        'portalBadge' => 'OPERATIONS'
    ],
    'driver' => [
        'initials' => 'KW',
        'name' => 'Kamal Wickrama',
        'roleTitle' => 'Executive Chauffeur',
        'portalBadge' => 'DRIVER'
    ],
    'passenger' => [
        'initials' => 'AS',
        'name' => 'Anura Samaraweera',
        'roleTitle' => 'Corporate Client',
        'portalBadge' => 'PASSENGER'
    ]
];

$profile = isset($userProfiles[$role]) ? $userProfiles[$role] : $userProfiles['admin'];
?>
<aside class="dash-sidebar" id="dashSidebar">
  
  <!-- Sidebar Header / Brand -->
  <div class="sidebar-header">
    <div class="sidebar-logo-badge">SM</div>
    <div class="sidebar-brand-text">
      <span>SmartMove<span class="blue-dot">.</span></span>
      <span class="sidebar-brand-version"><?php echo htmlspecialchars($profile['portalBadge']); ?></span>
    </div>
  </div>

  <!-- Scrollable Navigation Groups -->
  <div class="sidebar-scrollable">
    
    <!-- MAIN NAVIGATION -->
    <div>
      <div class="sidebar-group-title">MAIN NAVIGATION</div>
      <ul class="sidebar-nav-list">
        <li class="sidebar-nav-item active">
          <a href="dashboard.php?role=<?php echo urlencode($role); ?>">
            <span class="item-label">Overview</span>
            <span class="sidebar-chevron">›</span>
          </a>
        </li>

        <?php if ($role === 'admin'): ?>
          <li class="sidebar-nav-item">
            <a href="#analytics">
              <span class="item-label">Fleet Analytics</span>
              <span class="sidebar-badge-new">New</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#revenue">
              <span class="item-label">Revenue & Billing</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#users">
              <span class="item-label">User Management</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#database">
              <span class="item-label">Oracle & Mongo Sync</span>
              <span class="sidebar-badge-hot">Hot</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#reports">
              <span class="item-label">Compliance Reports</span>
            </a>
          </li>

        <?php elseif ($role === 'staff'): ?>
          <li class="sidebar-nav-item">
            <a href="#dispatch">
              <span class="item-label">Live Dispatch Queue</span>
              <span class="sidebar-badge-count">12</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#drivers">
              <span class="item-label">Driver Roster & Shifts</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#vehicles">
              <span class="item-label">Vehicle Maintenance</span>
              <span class="sidebar-badge-hot">Check</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#routes">
              <span class="item-label">Route Optimization</span>
            </a>
          </li>

        <?php elseif ($role === 'driver'): ?>
          <li class="sidebar-nav-item">
            <a href="#requests">
              <span class="item-label">Incoming Rides</span>
              <span class="sidebar-badge-count">2 New</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#earnings">
              <span class="item-label">Daily Earnings</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#shift">
              <span class="item-label">Shift Status (Online)</span>
              <span class="sidebar-badge-new">Active</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#ratings">
              <span class="item-label">Driver Rating & Tips</span>
            </a>
          </li>

        <?php elseif ($role === 'passenger'): ?>
          <li class="sidebar-nav-item">
            <a href="#book">
              <span class="item-label">Book a Ride</span>
              <span class="sidebar-badge-new">Fast</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#trips">
              <span class="item-label">My Trip History</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#wallet">
              <span class="item-label">Wallet & SmartMove Pass</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#saved">
              <span class="item-label">Saved Locations</span>
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>

    <!-- COMMUNICATION & TOOLS -->
    <div>
      <div class="sidebar-group-title">COMMUNICATIONS</div>
      <ul class="sidebar-nav-list">
        <li class="sidebar-nav-item">
          <a href="#chat">
            <span class="item-label">Support Chat</span>
            <span class="sidebar-badge-count">3</span>
          </a>
        </li>
        <li class="sidebar-nav-item">
          <a href="#notifications">
            <span class="item-label">Notifications</span>
          </a>
        </li>
        <li class="sidebar-nav-item">
          <a href="#settings">
            <span class="item-label">Settings</span>
          </a>
        </li>
      </ul>
    </div>

  </div>

  <!-- Sidebar Bottom User Profile -->
  <div class="sidebar-user-footer">
    <div class="user-avatar-circle"><?php echo htmlspecialchars($profile['initials']); ?></div>
    <div class="user-info-text">
      <div class="user-info-name"><?php echo htmlspecialchars($profile['name']); ?></div>
      <div class="user-info-role"><?php echo htmlspecialchars($profile['roleTitle']); ?></div>
    </div>
    <a href="../index.php" title="Exit to Public Site" style="color: var(--dash-text-muted); font-size: 1rem; text-decoration: none;">↗</a>
  </div>

</aside>
