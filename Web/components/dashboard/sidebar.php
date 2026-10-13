<?php

$currentRole = strtoupper($role ?? 'ADMIN');
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header" title="SmartMove <?php echo ucfirst(strtolower($currentRole)); ?> Portal">
    <div class="sidebar-brand-wrapper">
      <div class="brand-icon-box" style="padding:0; overflow:hidden;">
        <img src="../img/logo.jpg" alt="SmartMove Logo" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
      </div>
      <div class="sidebar-header-text">
        <span class="brand-name">SmartMove</span>
        <span class="brand-subtext"><?php echo $currentRole; ?> PORTAL</span>
      </div>
    </div>
    <div class="brand-arrows">⌘</div>
  </div>

  <!-- Navigation -->
  <nav class="sidebar-nav">
    <?php if ($currentRole === 'ADMIN'): ?>
      <div class="sidebar-group">
        <div class="sidebar-group-title">MAIN NAVIGATION</div>

        <a class="nav-item <?php echo $activeView === 'dashboardView' ? 'active' : ''; ?>" data-view="dashboardView"
          title="Dashboard Overview">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24">
              <rect width="7" height="9" x="3" y="3" rx="1" />
              <rect width="7" height="5" x="14" y="3" rx="1" />
              <rect width="7" height="9" x="14" y="12" rx="1" />
              <rect width="7" height="5" x="3" y="16" rx="1" />
            </svg>
          </div>
          <span>Overview</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'reportsView' ? 'active' : ''; ?>" data-view="reportsView"
          title="Operational & Analytics Reports">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <polyline points="14 2 14 8 20 8" />
              <line x1="16" y1="13" x2="8" y2="13" />
              <line x1="16" y1="17" x2="8" y2="17" />
              <polyline points="10 9 9 9 8 9" />
            </svg>
          </div>
          <span>Reports & Analytics</span>
          <span class="nav-badge" style="background:var(--accent-blue);">5</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'mongoView' ? 'active' : ''; ?>" data-view="mongoView"
          title="Customer Reviews & Fleet Documents">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24">
              <path
                d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
            </svg>
          </div>
          <span>Reviews & Documents</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'announcementsView' ? 'active' : ''; ?>"
          data-view="announcementsView" title="Travel Announcements & Alerts">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
              <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>
          </div>
          <span>Travel Alerts</span>
          <span class="nav-badge"
            style="background:#F59E0B; font-weight:700;"><?php echo isset($mongoAnnouncements) ? count($mongoAnnouncements) : 0; ?></span>
        </a>

        <a class="nav-item <?php echo $activeView === 'routesView' ? 'active' : ''; ?>" data-view="routesView"
          title="Transit Routes & Schedules">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24">
              <polygon points="3 11 22 2 13 21 11 13 3 11" />
            </svg>
          </div>
          <span>Routes & Schedules</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'fleetView' ? 'active' : ''; ?>" data-view="fleetView"
          title="Fleet & Vehicles">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24">
              <rect width="7" height="7" x="3" y="3" rx="1" />
              <rect width="7" height="7" x="14" y="3" rx="1" />
              <rect width="7" height="7" x="14" y="14" rx="1" />
              <rect width="7" height="7" x="3" y="14" rx="1" />
            </svg>
          </div>
          <span>Fleet & Vehicles</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'usersView' ? 'active' : ''; ?>" data-view="usersView"
          title="User Management">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
          </div>
          <span>Users & Roles</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'financeView' ? 'active' : ''; ?>" data-view="financeView"
          title="Finance & Revenue Ledger">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24">
              <line x1="12" y1="1" x2="12" y2="23" />
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
          </div>
          <span>Finance & Ledger</span>
        </a>
      </div>

      <div class="sidebar-group">
        <div class="sidebar-group-title">INFRASTRUCTURE</div>

        <a class="nav-item <?php echo $activeView === 'settingsView' ? 'active' : ''; ?>" data-view="settingsView"
          title="System Settings & Database Backups">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24">
              <path
                d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
          </div>
          <span>Settings & Backup</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'profileView' ? 'active' : ''; ?>" data-view="profileView"
          title="Account Profile & Settings">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
          </div>
          <span>My Profile</span>
        </a>
      </div>

    <?php elseif ($currentRole === 'STAFF'): ?>
      <div class="sidebar-group">
        <div class="sidebar-group-title">OPERATIONS CONTROL</div>
        
        <a class="nav-item <?php echo $activeView === 'dispatchView' ? 'active' : ''; ?>" data-view="dispatchView" title="Trip Dispatching & Schedules">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
          </div>
          <span>Dispatch & Trips</span>
          <?php if (isset($activeTripsCount)): ?>
            <span class="nav-badge" style="background:var(--blue-primary);"><?php echo $activeTripsCount; ?></span>
          <?php endif; ?>
        </a>

        <a class="nav-item <?php echo $activeView === 'shiftsView' ? 'active' : ''; ?>" data-view="shiftsView" title="Driver Shift Rostering">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <span>Driver Shifts</span>
          <?php if (isset($onDutyDrivers)): ?>
            <span class="nav-badge" style="background:#10B981;"><?php echo $onDutyDrivers; ?></span>
          <?php endif; ?>
        </a>

        <a class="nav-item <?php echo $activeView === 'maintenanceView' ? 'active' : ''; ?>" data-view="maintenanceView" title="Fleet Maintenance Logging">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <span>Fleet Maintenance</span>
          <?php if (!empty($maintVehCount)): ?>
            <span class="nav-badge" style="background:#F59E0B;"><?php echo $maintVehCount; ?></span>
          <?php endif; ?>
        </a>

        <a class="nav-item <?php echo $activeView === 'ticketingView' ? 'active' : ''; ?>" data-view="ticketingView" title="Counter Ticketing & Bookings">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
          </div>
          <span>Counter Ticketing</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'refundsView' ? 'active' : ''; ?>" data-view="refundsView" title="Cancellations & Refunds">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <span>Refund Processing</span>
        </a>
        <a class="nav-item <?php echo $activeView === 'announcementsView' ? 'active' : ''; ?>" data-view="announcementsView" title="MongoDB Travel Advisories">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </div>
          <span>Travel Alerts</span>
        </a>
        <a class="nav-item <?php echo $activeView === 'feedbackView' ? 'active' : ''; ?>" data-view="feedbackView" title="Passenger Reviews & Ratings">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          </div>
          <span>Passenger Reviews</span>
        </a>

        <a class="nav-item <?php echo ($activeView ?? '') === 'profileView' ? 'active' : ''; ?>" data-view="profileView" title="Account Profile & Settings">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <span>My Profile</span>
        </a>
      </div>

    <?php elseif ($currentRole === 'DRIVER'): ?>
      <div class="sidebar-group">
        <div class="sidebar-group-title">CAPTAIN DASHBOARD</div>

        <a class="nav-item <?php echo $activeView === 'cockpitView' ? 'active' : ''; ?>" data-view="cockpitView" title="Driver Cockpit & Run Manifest">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
          </div>
          <span>Active Cockpit</span>
          <?php if (!empty($activeTrip) && ($activeTrip['tripStatus'] ?? '') === 'IN_TRANSIT'): ?>
            <span class="nav-badge" style="background:#38BDF8;">LIVE</span>
          <?php endif; ?>
        </a>

        <a class="nav-item <?php echo $activeView === 'tripsView' ? 'active' : ''; ?>" data-view="tripsView" title="My Assigned Runs">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1 .4-1 1v7c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
          </div>
          <span>My Assigned Trips</span>
          <?php if (!empty($assignedTrips)): ?>
            <span class="nav-badge" style="background:var(--blue-primary);"><?php echo count($assignedTrips); ?></span>
          <?php endif; ?>
        </a>

        <a class="nav-item <?php echo $activeView === 'shiftsView' ? 'active' : ''; ?>" data-view="shiftsView" title="Duty Shifts & Roster">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <span>My Shifts</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'fuelView' ? 'active' : ''; ?>" data-view="fuelView" title="Vehicle Health & Refueling">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M3 22v-8a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v8"/><path d="M15 10a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v12"/><path d="M7 12V2"/><path d="M19 8V2"/></svg>
          </div>
          <span>Fuel &amp; Vehicle Logs</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'docsView' ? 'active' : ''; ?>" data-view="docsView" title="Vehicle Compliance Documents">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          </div>
          <span>Vehicle Documents</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'announcementsView' ? 'active' : ''; ?>" data-view="announcementsView" title="Travel &amp; Route Alerts">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </div>
          <span>Travel Alerts</span>
        </a>

        <a class="nav-item <?php echo $activeView === 'feedbackView' ? 'active' : ''; ?>" data-view="feedbackView" title="Ratings & Passenger Feedback">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          </div>
          <span>Reviews &amp; Ratings</span>
          <?php if (!empty($avgRating)): ?>
            <span class="nav-badge" style="background:#F59E0B;"><?php echo $avgRating; ?></span>
          <?php endif; ?>
        </a>

        <a class="nav-item <?php echo ($activeView ?? '') === 'profileView' ? 'active' : ''; ?>" data-view="profileView" title="Driver Profile & Settings">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <span>My Profile</span>
        </a>
      </div>
    <?php endif; ?>
  </nav>

  <!-- Footer profile -->
  <div class="sidebar-footer">
    <button class="user-profile-btn" id="sidebarUserBtn" type="button" aria-haspopup="true" aria-expanded="false">
      <div class="user-avatar"><?php echo $initials; ?></div>
      <div class="user-details">
        <span class="user-name"><?php echo htmlspecialchars($fullName); ?></span>
        <span class="user-email"><?php echo htmlspecialchars($email); ?></span>
      </div>
      <div class="user-arrows">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="m7 15 5 5 5-5" />
          <path d="m7 9 5-5 5 5" />
        </svg>
      </div>
    </button>

    <!-- User menu -->
    <div class="popover-menu" id="sidebarUserMenu" role="menu">
      <div class="popover-user-header">
        <div class="user-avatar"><?php echo $initials; ?></div>
        <div class="user-details">
          <span class="user-name"><?php echo htmlspecialchars($fullName); ?></span>
          <span class="user-email"><?php echo htmlspecialchars($email); ?></span>
        </div>
      </div>

      <div class="popover-divider"></div>

      <a class="popover-item" data-view="profileView" href="#profileView" role="menuitem">
        <svg class="popover-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        <span class="popover-item-text">Manage Profile</span>
      </a>

      <a href="../index.php" class="popover-item" role="menuitem">
        <svg class="popover-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
          <polyline points="9 22 9 12 15 12 15 22" />
        </svg>
        <span class="popover-item-text">Main Website</span>
      </a>

      <?php if ($currentRole !== 'ADMIN' && strtoupper(Auth::user()['role'] ?? '') === 'ADMIN'): ?>
        <a href="dashboard.php" class="popover-item" role="menuitem">
          <svg class="popover-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          <span class="popover-item-text">Admin Command Portal</span>
        </a>
      <?php endif; ?>

      <div class="popover-divider"></div>

      <!-- Sign out -->
      <a class="popover-item danger" href="logout.php" role="menuitem">
        <svg class="popover-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
          <polyline points="16 17 21 12 16 7" />
          <line x1="21" y1="12" x2="9" y2="12" />
        </svg>
        <span class="popover-item-text">Sign out</span>
      </a>
    </div>
  </div>
</aside>
