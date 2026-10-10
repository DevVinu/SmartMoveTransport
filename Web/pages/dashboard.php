<?php
/**
 * SmartMove Transport — Shadcn Admin (Vite + ShadcnUI Replica)
 * Complete Dark Slate Dashboard Implementation
 */
$pageTitle = "Shadcn Admin — Vite + ShadcnUI";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body class="dashboard-body">

<div class="app-wrapper">

  <!-- =========================================================================
       SIDEBAR NAVIGATION
       ========================================================================= -->
  <aside class="sidebar" id="sidebar">
    <!-- Brand / Workspace Switcher -->
    <div class="sidebar-header" title="Switch workspace">
      <div class="sidebar-brand-wrapper">
        <div class="brand-icon-box">⌘</div>
        <div class="sidebar-header-text">
          <span class="brand-name">Shadcn Admin</span>
          <span class="brand-subtext">Vite + ShadcnUI</span>
        </div>
      </div>
      <div class="brand-arrows">⇅</div>
    </div>

    <!-- Nav List -->
    <nav class="sidebar-nav">
      <!-- General Section -->
      <div class="sidebar-group">
        <div class="sidebar-group-title">General</div>
        
        <a class="nav-item active" data-view="dashboardView" title="Dashboard">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
          </div>
          <span>Dashboard</span>
        </a>

        <a class="nav-item" data-view="tasksView" title="Tasks">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1"/><path d="m9 14 2 2 4-4"/></svg>
          </div>
          <span>Tasks</span>
        </a>

        <a class="nav-item" data-view="appsView" title="Apps">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
          </div>
          <span>Apps</span>
        </a>

        <a class="nav-item" data-view="dashboardView" title="Chats">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
          </div>
          <span>Chats</span>
          <span class="nav-badge">3</span>
        </a>

        <a class="nav-item" data-view="usersView" title="Users">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <span>Users</span>
        </a>

        <a class="nav-item has-submenu" title="Secured by Clerk">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </div>
          <span>Secured by Clerk</span>
          <div class="nav-chevron">›</div>
        </a>
        <div class="sidebar-submenu">
          <a class="submenu-item">Sign In</a>
          <a class="submenu-item">Sign Up</a>
          <a class="submenu-item">User Management</a>
        </div>
      </div>

      <!-- Pages Section -->
      <div class="sidebar-group">
        <div class="sidebar-group-title">Pages</div>

        <a class="nav-item has-submenu" title="Auth">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
          </div>
          <span>Auth</span>
          <div class="nav-chevron">›</div>
        </a>
        <div class="sidebar-submenu">
          <a class="submenu-item">Login</a>
          <a class="submenu-item">Register</a>
          <a class="submenu-item">Forgot Password</a>
        </div>

        <a class="nav-item has-submenu" title="Errors">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="m8 2 1.88 1.88"/><path d="M14.12 3.88 16 2"/><path d="M9 7.13v-1a3.003 3.003 0 1 1 6 0v1"/><path d="M12 20c-3.3 0-6-2.7-6-6v-3a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v3c0 3.3-2.7 6-6 6"/><path d="M12 20v-9"/><path d="M6.53 9C4.6 8.8 3 7.1 3 5"/><path d="M6 13H2"/><path d="M3 21c0-2.1 1.7-3.9 3.8-4"/><path d="M20.97 5c0 2.1-1.6 3.8-3.5 4"/><path d="M22 13h-4"/><path d="M17.2 17c2.1.1 3.8 1.9 3.8 4"/></svg>
          </div>
          <span>Errors</span>
          <div class="nav-chevron">›</div>
        </a>
        <div class="sidebar-submenu">
          <a class="submenu-item">404 Not Found</a>
          <a class="submenu-item">500 Server Error</a>
          <a class="submenu-item">Maintenance</a>
        </div>
      </div>

      <!-- Other Section -->
      <div class="sidebar-group">
        <div class="sidebar-group-title">Other</div>

        <a class="nav-item" data-view="settingsView" title="Settings">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
          </div>
          <span>Settings</span>
          <div class="nav-chevron">›</div>
        </a>

        <a class="nav-item" title="Help Center">
          <div class="nav-item-icon">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
          </div>
          <span>Help Center</span>
        </a>
      </div>
    </nav>

    <!-- Sidebar Footer / User Profile -->
    <div class="sidebar-footer">
      <button class="user-profile-btn" id="sidebarUserBtn" type="button">
        <div class="user-avatar">SN</div>
        <div class="user-details">
          <span class="user-name">satnaing</span>
          <span class="user-email">satnaingdev@gmail.com</span>
        </div>
        <div class="user-arrows">⇅</div>
      </button>

      <!-- Sidebar Footer User Popover Menu -->
      <div class="popover-menu" id="sidebarUserMenu">
        <div class="popover-header">
          <span class="pop-title">satnaing</span>
          <span class="pop-sub">satnaingdev@gmail.com</span>
        </div>
        <a class="popover-item">
          <span>⚡ Upgrade to Pro</span>
        </a>
        <div class="popover-divider"></div>
        <a class="popover-item">
          <span>Account</span>
          <span class="popover-shortcut">⇧⌘P</span>
        </a>
        <a class="popover-item">
          <span>Billing</span>
          <span class="popover-shortcut">⌘B</span>
        </a>
        <a class="popover-item">
          <span>Notifications</span>
          <span class="popover-shortcut">⌘S</span>
        </a>
        <div class="popover-divider"></div>
        <a class="popover-item danger">
          <span>Log out</span>
        </a>
      </div>
    </div>
  </aside>

  <!-- =========================================================================
       MAIN VIEWPORT & TOPBAR
       ========================================================================= -->
  <main class="main-viewport">
    
    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle-btn" id="toggleSidebarBtn" title="Toggle Sidebar">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
        </button>
      </div>

      <div class="topbar-right">
        <!-- Search bar with ⌘K badge -->
        <div class="topbar-search-box" onclick="document.getElementById('globalSearchInput').focus()">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" id="globalSearchInput" placeholder="Search" style="background:transparent; border:none; outline:none; color:inherit; font-size:12.5px; width:100px;">
          <span class="kbd-badge">⌘K</span>
        </div>

        <!-- Moon / Dark Mode Icon -->
        <button class="topbar-icon-btn" title="Toggle Dark Mode">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
        </button>

        <!-- Settings Icon -->
        <button class="topbar-icon-btn" title="Settings" onclick="document.querySelector('[data-view=\'settingsView\']').click()">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>

        <!-- Top Right Avatar with Dropdown -->
        <button class="topbar-avatar-btn" id="topbarAvatarBtn" title="User Menu">
          <div class="user-avatar">SN</div>
        </button>

        <!-- Top Right Popover Menu -->
        <div class="popover-menu" id="topbarUserMenu">
          <div class="popover-header">
            <span class="pop-title">satnaing</span>
            <span class="pop-sub">satnaingdev@gmail.com</span>
          </div>
          <a class="popover-item">
            <span>Profile</span>
            <span class="popover-shortcut">⇧⌘P</span>
          </a>
          <a class="popover-item">
            <span>Billing</span>
            <span class="popover-shortcut">⌘B</span>
          </a>
          <a class="popover-item">
            <span>Settings</span>
            <span class="popover-shortcut">⌘S</span>
          </a>
          <a class="popover-item">
            <span>New Team</span>
          </a>
          <div class="popover-divider"></div>
          <a class="popover-item danger">
            <span>Sign out</span>
            <span class="popover-shortcut">⇧⌘Q</span>
          </a>
        </div>
      </div>
    </header>

    <!-- Content Container -->
    <div class="content-container">

      <!-- =====================================================================
           VIEW 1: DASHBOARD OVERVIEW
           ===================================================================== -->
      <section class="page-view active-view" id="dashboardView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Dashboard</h1>
          </div>
          <div class="view-actions">
            <!-- Sub-tabs -->
            <div class="subtabs-nav">
              <div class="subtab-item active">Overview</div>
              <div class="subtab-item">Analytics</div>
              <div class="subtab-item">Reports</div>
              <div class="subtab-item">Notifications</div>
            </div>
            <!-- Download Button -->
            <button class="btn-primary" type="button">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
              Download
            </button>
          </div>
        </div>

        <!-- KPI Cards Grid -->
        <div class="kpi-grid">
          <!-- Total Revenue -->
          <div class="kpi-card">
            <div class="kpi-card-header">
              <span class="kpi-label">Total Revenue</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="kpi-value">$45,231.89</div>
            <div class="kpi-subtext">+20.1% from last month</div>
          </div>

          <!-- Subscriptions -->
          <div class="kpi-card">
            <div class="kpi-card-header">
              <span class="kpi-label">Subscriptions</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="kpi-value">+2350</div>
            <div class="kpi-subtext">+180.1% from last month</div>
          </div>

          <!-- Sales -->
          <div class="kpi-card">
            <div class="kpi-card-header">
              <span class="kpi-label">Sales</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
            </div>
            <div class="kpi-value">+12,234</div>
            <div class="kpi-subtext">+19% from last month</div>
          </div>

          <!-- Active Now -->
          <div class="kpi-card">
            <div class="kpi-card-header">
              <span class="kpi-label">Active Now</span>
              <svg class="kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <div class="kpi-value">+573</div>
            <div class="kpi-subtext">+201 since last hour</div>
          </div>
        </div>

        <!-- Charts & Recent Sales Grid -->
        <div class="dash-overview-grid">
          <!-- Overview Bar Chart Panel -->
          <div class="dash-panel">
            <div class="panel-header">
              <div class="panel-title">Overview</div>
            </div>
            <div class="chart-container">
              <svg class="chart-svg" viewBox="0 0 600 270" preserveAspectRatio="none">
                <!-- Horizontal Grid Lines -->
                <line x1="45" y1="20" x2="590" y2="20" class="chart-grid-line" />
                <line x1="45" y1="75" x2="590" y2="75" class="chart-grid-line" />
                <line x1="45" y1="130" x2="590" y2="130" class="chart-grid-line" />
                <line x1="45" y1="185" x2="590" y2="185" class="chart-grid-line" />
                <line x1="45" y1="240" x2="590" y2="240" class="chart-grid-line" />

                <!-- Y-Axis Labels -->
                <text x="35" y="24" text-anchor="end" class="chart-axis-label">$6000</text>
                <text x="35" y="79" text-anchor="end" class="chart-axis-label">$4500</text>
                <text x="35" y="134" text-anchor="end" class="chart-axis-label">$3000</text>
                <text x="35" y="189" text-anchor="end" class="chart-axis-label">$1500</text>
                <text x="35" y="244" text-anchor="end" class="chart-axis-label">$0</text>

                <!-- Month Bars (Crisp White rounded bars) -->
                <!-- Jan (height: 100) -->
                <rect x="58" y="140" width="30" height="100" class="chart-bar"><title>Jan: $2,727</title></rect>
                <text x="73" y="260" text-anchor="middle" class="chart-axis-label">Jan</text>

                <!-- Feb (height: 45) -->
                <rect x="103" y="195" width="30" height="45" class="chart-bar"><title>Feb: $1,227</title></rect>
                <text x="118" y="260" text-anchor="middle" class="chart-axis-label">Feb</text>

                <!-- Mar (height: 155) -->
                <rect x="148" y="85" width="30" height="155" class="chart-bar"><title>Mar: $4,227</title></rect>
                <text x="163" y="260" text-anchor="middle" class="chart-axis-label">Mar</text>

                <!-- Apr (height: 140) -->
                <rect x="193" y="100" width="30" height="140" class="chart-bar"><title>Apr: $3,818</title></rect>
                <text x="208" y="260" text-anchor="middle" class="chart-axis-label">Apr</text>

                <!-- May (height: 205) -->
                <rect x="238" y="35" width="30" height="205" class="chart-bar"><title>May: $5,590</title></rect>
                <text x="253" y="260" text-anchor="middle" class="chart-axis-label">May</text>

                <!-- Jun (height: 110) -->
                <rect x="283" y="130" width="30" height="110" class="chart-bar"><title>Jun: $3,000</title></rect>
                <text x="298" y="260" text-anchor="middle" class="chart-axis-label">Jun</text>

                <!-- Jul (height: 125) -->
                <rect x="328" y="115" width="30" height="125" class="chart-bar"><title>Jul: $3,409</title></rect>
                <text x="343" y="260" text-anchor="middle" class="chart-axis-label">Jul</text>

                <!-- Aug (height: 175) -->
                <rect x="373" y="65" width="30" height="175" class="chart-bar"><title>Aug: $4,772</title></rect>
                <text x="388" y="260" text-anchor="middle" class="chart-axis-label">Aug</text>

                <!-- Sep (height: 215) -->
                <rect x="418" y="25" width="30" height="215" class="chart-bar"><title>Sep: $5,863</title></rect>
                <text x="433" y="260" text-anchor="middle" class="chart-axis-label">Sep</text>

                <!-- Oct (height: 150) -->
                <rect x="463" y="90" width="30" height="150" class="chart-bar"><title>Oct: $4,090</title></rect>
                <text x="478" y="260" text-anchor="middle" class="chart-axis-label">Oct</text>

                <!-- Nov (height: 80) -->
                <rect x="508" y="160" width="30" height="80" class="chart-bar"><title>Nov: $2,181</title></rect>
                <text x="523" y="260" text-anchor="middle" class="chart-axis-label">Nov</text>

                <!-- Dec (height: 180) -->
                <rect x="553" y="60" width="30" height="180" class="chart-bar"><title>Dec: $4,909</title></rect>
                <text x="568" y="260" text-anchor="middle" class="chart-axis-label">Dec</text>
              </svg>
            </div>
          </div>

          <!-- Recent Sales Panel -->
          <div class="dash-panel">
            <div class="panel-header">
              <div class="panel-title">Recent Sales</div>
              <div class="panel-subtitle">You made 265 sales this month.</div>
            </div>
            <div class="sales-list">
              <!-- Item 1 -->
              <div class="sale-item">
                <div class="sale-customer">
                  <div class="sale-avatar">OM</div>
                  <div class="sale-info">
                    <span class="sale-name">Olivia Martin</span>
                    <span class="sale-email">olivia.martin@email.com</span>
                  </div>
                </div>
                <div class="sale-amount">+$1,999.00</div>
              </div>

              <!-- Item 2 -->
              <div class="sale-item">
                <div class="sale-customer">
                  <div class="sale-avatar">JL</div>
                  <div class="sale-info">
                    <span class="sale-name">Jackson Lee</span>
                    <span class="sale-email">jackson.lee@email.com</span>
                  </div>
                </div>
                <div class="sale-amount">+$39.00</div>
              </div>

              <!-- Item 3 -->
              <div class="sale-item">
                <div class="sale-customer">
                  <div class="sale-avatar">IN</div>
                  <div class="sale-info">
                    <span class="sale-name">Isabella Nguyen</span>
                    <span class="sale-email">isabella.nguyen@email.com</span>
                  </div>
                </div>
                <div class="sale-amount">+$299.00</div>
              </div>

              <!-- Item 4 -->
              <div class="sale-item">
                <div class="sale-customer">
                  <div class="sale-avatar">WK</div>
                  <div class="sale-info">
                    <span class="sale-name">William Kim</span>
                    <span class="sale-email">will@email.com</span>
                  </div>
                </div>
                <div class="sale-amount">+$99.00</div>
              </div>

              <!-- Item 5 -->
              <div class="sale-item">
                <div class="sale-customer">
                  <div class="sale-avatar">SD</div>
                  <div class="sale-info">
                    <span class="sale-name">Sofia Davis</span>
                    <span class="sale-email">sofia.davis@email.com</span>
                  </div>
                </div>
                <div class="sale-amount">+$39.00</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- =====================================================================
           VIEW 2: TASKS PAGE
           ===================================================================== -->
      <section class="page-view" id="tasksView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>Tasks</h1>
            <p>Here's a list of your tasks for this month!</p>
          </div>
          <div class="view-actions">
            <button class="btn-outline" type="button">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
              Import
            </button>
            <button class="btn-primary" type="button">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
              Create
            </button>
          </div>
        </div>

        <!-- Filter Toolbar -->
        <div class="toolbar-row">
          <div class="toolbar-left">
            <input type="text" class="table-search-input" id="taskSearchInput" placeholder="Filter by title or ID...">
            
            <!-- + Status Button -->
            <button class="filter-dropdown-btn" id="statusFilterBtn" type="button">
              <span>⊕ Status</span>
            </button>

            <!-- + Priority Button -->
            <button class="filter-dropdown-btn" id="priorityFilterBtn" type="button">
              <span>⊕ Priority</span>
            </button>

            <!-- Active filter badges container -->
            <div id="activeFiltersBadges" style="display:flex; gap:4px; align-items:center;"></div>

            <!-- Reset Button -->
            <button class="filter-reset-btn" id="resetFiltersBtn" style="display:none;" type="button">
              Reset ✕
            </button>
          </div>

          <div class="toolbar-right">
            <button class="btn-outline" style="padding:6px 10px; font-size:12.5px;" type="button">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              View
            </button>
          </div>
        </div>

        <!-- Tasks Table -->
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 40px;">
                  <input type="checkbox" class="row-check" id="selectAllTasksCheck">
                </th>
                <th style="width: 120px;">Task</th>
                <th>Title <span style="font-size:11px;">⇅</span></th>
                <th style="width: 140px;">Status <span style="font-size:11px;">⇅</span></th>
                <th style="width: 110px;">Priority <span style="font-size:11px;">⇅</span></th>
                <th style="width: 50px;"></th>
              </tr>
            </thead>
            <tbody>
              <!-- Row 1 -->
              <tr class="task-table-row" data-task-id="TASK-9366" data-title="Auctus bardus minus pariatur vobis solitudo tamquam solitudo." data-status="Canceled" data-priority="Low">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-9366</td>
                <td>
                  <span class="type-badge">Documentation</span>
                  <span style="margin-left: 8px;">Auctus bardus minus pariatur vobis solitudo tamquam solitudo.</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#64748B;">⊘</span>
                    <span>Canceled</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#94A3B8;">↓</span>
                    <span>Low</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>

              <!-- Row 2 -->
              <tr class="task-table-row" data-task-id="TASK-5736" data-title="Admoneo vehemens suscipit toties desidero tollo allatus blanditiis caute delibero degenero." data-status="Canceled" data-priority="Medium">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-5736</td>
                <td>
                  <span class="type-badge">Bug</span>
                  <span style="margin-left: 8px;">Admoneo vehemens suscipit toties desidero tollo allatus blanditiis caute delibero degenero.</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#64748B;">⊘</span>
                    <span>Canceled</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#F59E0B;">→</span>
                    <span>Medium</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>

              <!-- Row 3 -->
              <tr class="task-table-row" data-task-id="TASK-3204" data-title="Deputo veritas vinculum expedita casus supplanto corona deserunt calamitas considero soleo coma..." data-status="Canceled" data-priority="High">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-3204</td>
                <td>
                  <span class="type-badge">Documentation</span>
                  <span style="margin-left: 8px;">Deputo veritas vinculum expedita casus supplanto corona deserunt calamitas considero soleo coma...</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#64748B;">⊘</span>
                    <span>Canceled</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#EF4444;">↑</span>
                    <span>High</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>

              <!-- Row 4 -->
              <tr class="task-table-row" data-task-id="TASK-7141" data-title="Vester ducimus aequus minima possimus vilis cuppedia celo alter depereo." data-status="Done" data-priority="High">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-7141</td>
                <td>
                  <span class="type-badge">Bug</span>
                  <span style="margin-left: 8px;">Vester ducimus aequus minima possimus vilis cuppedia celo alter depereo.</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#10B981;">✓</span>
                    <span>Done</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#EF4444;">↑</span>
                    <span>High</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>

              <!-- Row 5 -->
              <tr class="task-table-row" data-task-id="TASK-8689" data-title="Admoveo nihil denique acer corrumpo cupio peccatus spectaculum tumultus tergum cui thalassinus v..." data-status="Backlog" data-priority="Medium">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-8689</td>
                <td>
                  <span class="type-badge">Documentation</span>
                  <span style="margin-left: 8px;">Admoveo nihil denique acer corrumpo cupio peccatus spectaculum tumultus tergum cui thalassinus v...</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#64748B;">?</span>
                    <span>Backlog</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#F59E0B;">→</span>
                    <span>Medium</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>

              <!-- Row 6 -->
              <tr class="task-table-row" data-task-id="TASK-3359" data-title="Carus minus uberrime crapula damnatio tristis correptius adhaero itaque defendo." data-status="Canceled" data-priority="High">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-3359</td>
                <td>
                  <span class="type-badge">Feature</span>
                  <span style="margin-left: 8px;">Carus minus uberrime crapula damnatio tristis correptius adhaero itaque defendo.</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#64748B;">⊘</span>
                    <span>Canceled</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#EF4444;">↑</span>
                    <span>High</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>

              <!-- Row 7 -->
              <tr class="task-table-row" data-task-id="TASK-4715" data-title="Solutio cohaero baiulus brevis animadverto adfero adeo callide calco quibusdam vapulus tergum." data-status="Canceled" data-priority="Medium">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-4715</td>
                <td>
                  <span class="type-badge">Bug</span>
                  <span style="margin-left: 8px;">Solutio cohaero baiulus brevis animadverto adfero adeo callide calco quibusdam vapulus tergum.</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#64748B;">⊘</span>
                    <span>Canceled</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#F59E0B;">→</span>
                    <span>Medium</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>

              <!-- Row 8 -->
              <tr class="task-table-row" data-task-id="TASK-7138" data-title="Usitas tardus aliquid comprehendo cupiditas a patria statim copiose crux." data-status="Done" data-priority="Low">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-7138</td>
                <td>
                  <span class="type-badge">Feature</span>
                  <span style="margin-left: 8px;">Usitas tardus aliquid comprehendo cupiditas a patria statim copiose crux.</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#10B981;">✓</span>
                    <span>Done</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#94A3B8;">↓</span>
                    <span>Low</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>

              <!-- Row 9 -->
              <tr class="task-table-row" data-task-id="TASK-1373" data-title="Peior alias comedo pel averto stultus caries." data-status="Canceled" data-priority="Low">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-1373</td>
                <td>
                  <span class="type-badge">Feature</span>
                  <span style="margin-left: 8px;">Peior alias comedo pel averto stultus caries.</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#64748B;">⊘</span>
                    <span>Canceled</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#94A3B8;">↓</span>
                    <span>Low</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>

              <!-- Row 10 -->
              <tr class="task-table-row" data-task-id="TASK-4140" data-title="Eius bibo vulgaris cenaculum sponte est." data-status="Done" data-priority="Low">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-4140</td>
                <td>
                  <span class="type-badge">Feature</span>
                  <span style="margin-left: 8px;">Eius bibo vulgaris cenaculum sponte est.</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#10B981;">✓</span>
                    <span>Done</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#94A3B8;">↓</span>
                    <span>Low</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>

              <!-- Row 11: Todo -->
              <tr class="task-table-row" data-task-id="TASK-8202" data-title="Beatus um varietas tracto calco tracto aptus dens." data-status="Todo" data-priority="Medium">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-8202</td>
                <td>
                  <span class="type-badge">Feature</span>
                  <span style="margin-left: 8px;">Beatus um varietas tracto calco tracto aptus dens.</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#94A3B8;">○</span>
                    <span>Todo</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#F59E0B;">→</span>
                    <span>Medium</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>

              <!-- Row 12: In Progress -->
              <tr class="task-table-row" data-task-id="TASK-9953" data-title="Tunc a go tabesco aliquam claro certe aequitas sponte." data-status="In Progress" data-priority="High">
                <td><input type="checkbox" class="row-check task-row-check"></td>
                <td style="color:var(--text-muted); font-size:12.5px;">TASK-9953</td>
                <td>
                  <span class="type-badge">Feature</span>
                  <span style="margin-left: 8px;">Tunc a go tabesco aliquam claro certe aequitas sponte.</span>
                </td>
                <td>
                  <span class="status-cell">
                    <span class="status-icon" style="color:#3B82F6;">◐</span>
                    <span>In Progress</span>
                  </span>
                </td>
                <td>
                  <span class="priority-cell">
                    <span class="priority-icon" style="color:#EF4444;">↑</span>
                    <span>High</span>
                  </span>
                </td>
                <td style="text-align: right; position: relative;">
                  <button class="row-action-btn" type="button">•••</button>
                  <div class="popover-menu" style="width:140px;">
                    <a class="popover-item"><span>Edit</span></a>
                    <a class="popover-item"><span>Make a copy</span></a>
                    <a class="popover-item"><span>Favorite</span></a>
                    <a class="popover-item"><span>Labels ›</span></a>
                    <div class="popover-divider"></div>
                    <a class="popover-item danger"><span>Delete</span></a>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Floating Popover: Status Filter -->
        <div class="popover-menu filter-popover-box" id="statusFilterMenu">
          <div class="filter-popover-search">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" placeholder="Status">
          </div>
          <div class="filter-options-list">
            <div class="filter-option-row status-filter-option" data-val="Backlog">
              <span class="filter-checkbox"></span>
              <div class="filter-option-label">
                <span style="color:#64748B;">?</span>
                <span>Backlog</span>
              </div>
              <span class="filter-option-count">18</span>
            </div>
            <div class="filter-option-row status-filter-option" data-val="Todo">
              <span class="filter-checkbox"></span>
              <div class="filter-option-label">
                <span style="color:#94A3B8;">○</span>
                <span>Todo</span>
              </div>
              <span class="filter-option-count">16</span>
            </div>
            <div class="filter-option-row status-filter-option" data-val="In Progress">
              <span class="filter-checkbox"></span>
              <div class="filter-option-label">
                <span style="color:#3B82F6;">◐</span>
                <span>In Progress</span>
              </div>
              <span class="filter-option-count">24</span>
            </div>
            <div class="filter-option-row status-filter-option" data-val="Done">
              <span class="filter-checkbox"></span>
              <div class="filter-option-label">
                <span style="color:#10B981;">✓</span>
                <span>Done</span>
              </div>
              <span class="filter-option-count">18</span>
            </div>
            <div class="filter-option-row status-filter-option" data-val="Canceled">
              <span class="filter-checkbox"></span>
              <div class="filter-option-label">
                <span style="color:#64748B;">⊘</span>
                <span>Canceled</span>
              </div>
              <span class="filter-option-count">24</span>
            </div>
          </div>
          <div class="filter-clear-btn" id="clearStatusFiltersBtn">Clear filters</div>
        </div>

        <!-- Floating Popover: Priority Filter -->
        <div class="popover-menu filter-popover-box" id="priorityFilterMenu">
          <div class="filter-popover-search">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" placeholder="Priority">
          </div>
          <div class="filter-options-list">
            <div class="filter-option-row priority-filter-option" data-val="Low">
              <span class="filter-checkbox"></span>
              <div class="filter-option-label">
                <span style="color:#94A3B8;">↓</span>
                <span>Low</span>
              </div>
              <span class="filter-option-count">29</span>
            </div>
            <div class="filter-option-row priority-filter-option" data-val="Medium">
              <span class="filter-checkbox"></span>
              <div class="filter-option-label">
                <span style="color:#F59E0B;">→</span>
                <span>Medium</span>
              </div>
              <span class="filter-option-count">31</span>
            </div>
            <div class="filter-option-row priority-filter-option" data-val="High">
              <span class="filter-checkbox"></span>
              <div class="filter-option-label">
                <span style="color:#EF4444;">↑</span>
                <span>High</span>
              </div>
              <span class="filter-option-count">40</span>
            </div>
            <div class="filter-option-row priority-filter-option" data-val="Critical">
              <span class="filter-checkbox"></span>
              <div class="filter-option-label">
                <span style="color:#DC2626;">!</span>
                <span>Critical</span>
              </div>
              <span class="filter-option-count">8</span>
            </div>
          </div>
        </div>
      </section>

      <!-- =====================================================================
           VIEW 3: APPS / INTEGRATIONS
           ===================================================================== -->
      <section class="page-view" id="appsView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>App Integrations</h1>
            <p>Here's a list of your apps for the integration!</p>
          </div>
        </div>

        <div class="toolbar-row">
          <div class="toolbar-left">
            <input type="text" class="table-search-input" placeholder="Filter apps...">
            <select class="settings-select" style="padding:6px 12px; font-size:12.5px;">
              <option>All Apps</option>
              <option>Connected</option>
              <option>Not Connected</option>
            </select>
          </div>
          <div class="toolbar-right">
            <button class="btn-outline" style="padding:6px 10px; font-size:12.5px;">Ascending ⇅</button>
          </div>
        </div>

        <div class="apps-grid">
          <!-- Discord -->
          <div class="app-card">
            <div>
              <div class="app-card-top">
                <div class="app-icon-wrapper" style="color:#5865F2;">💬</div>
                <button class="app-connect-btn connected" type="button">Connected</button>
              </div>
              <div class="app-title">Discord</div>
              <div class="app-description">Connect with Discord to notify relevant teams directly within dedicated server channels.</div>
            </div>
          </div>

          <!-- Docker -->
          <div class="app-card">
            <div>
              <div class="app-card-top">
                <div class="app-icon-wrapper" style="color:#2496ED;">🐳</div>
                <button class="app-connect-btn" type="button">Connect</button>
              </div>
              <div class="app-title">Docker</div>
              <div class="app-description">Automate application container deployment and fleet telemetry pipeline builds.</div>
            </div>
          </div>

          <!-- Figma -->
          <div class="app-card">
            <div>
              <div class="app-card-top">
                <div class="app-icon-wrapper" style="color:#F24E1E;">🎨</div>
                <button class="app-connect-btn connected" type="button">Connected</button>
              </div>
              <div class="app-title">Figma</div>
              <div class="app-description">Sync UI design assets and review interactive mobile prototype mockups directly.</div>
            </div>
          </div>

          <!-- GitHub -->
          <div class="app-card">
            <div>
              <div class="app-card-top">
                <div class="app-icon-wrapper" style="color:#FFFFFF;">🐙</div>
                <button class="app-connect-btn connected" type="button">Connected</button>
              </div>
              <div class="app-title">GitHub</div>
              <div class="app-description">Link commits and pull requests to automatic issue tracking and CI/CD pipelines.</div>
            </div>
          </div>

          <!-- GitLab -->
          <div class="app-card">
            <div>
              <div class="app-card-top">
                <div class="app-icon-wrapper" style="color:#FC6D26;">🦊</div>
                <button class="app-connect-btn" type="button">Connect</button>
              </div>
              <div class="app-title">GitLab</div>
              <div class="app-description">Continuous integration, code reviews and Git repository management for enterprise teams.</div>
            </div>
          </div>

          <!-- Gmail -->
          <div class="app-card">
            <div>
              <div class="app-card-top">
                <div class="app-icon-wrapper" style="color:#EA4335;">✉</div>
                <button class="app-connect-btn connected" type="button">Connected</button>
              </div>
              <div class="app-title">Gmail</div>
              <div class="app-description">Send booking confirmations, PDF receipts, and cancellation notices directly to users.</div>
            </div>
          </div>

          <!-- Google Drive -->
          <div class="app-card">
            <div>
              <div class="app-card-top">
                <div class="app-icon-wrapper" style="color:#34A853;">📁</div>
                <button class="app-connect-btn" type="button">Connect</button>
              </div>
              <div class="app-title">Google Drive</div>
              <div class="app-description">Store uploaded vehicle inspection reports, registration documents and driver licenses.</div>
            </div>
          </div>

          <!-- Slack -->
          <div class="app-card">
            <div>
              <div class="app-card-top">
                <div class="app-icon-wrapper" style="color:#E01E5A;">#</div>
                <button class="app-connect-btn connected" type="button">Connected</button>
              </div>
              <div class="app-title">Slack</div>
              <div class="app-description">Instant alerts for driver delays, emergency maintenance orders and booking spikes.</div>
            </div>
          </div>

          <!-- Notion -->
          <div class="app-card">
            <div>
              <div class="app-card-top">
                <div class="app-icon-wrapper" style="color:#FFFFFF;">📓</div>
                <button class="app-connect-btn" type="button">Connect</button>
              </div>
              <div class="app-title">Notion</div>
              <div class="app-description">Centralize SOPs, fleet maintenance guidelines, and customer onboarding docs.</div>
            </div>
          </div>
        </div>
      </section>

      <!-- =====================================================================
           VIEW 4: USER LIST
           ===================================================================== -->
      <section class="page-view" id="usersView">
        <div class="view-header">
          <div class="view-title-group">
            <h1>User List</h1>
            <p>Manage your users and their roles here.</p>
          </div>
          <div class="view-actions">
            <button class="btn-outline" type="button">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              Invite User
            </button>
            <button class="btn-primary" type="button">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
              Add User
            </button>
          </div>
        </div>

        <div class="toolbar-row">
          <div class="toolbar-left">
            <input type="text" class="table-search-input" placeholder="Filter users...">
            <select class="settings-select" style="padding:6px 12px; font-size:12.5px;">
              <option>All Roles</option>
              <option>Admin</option>
              <option>Manager</option>
              <option>Driver</option>
              <option>Passenger</option>
            </select>
          </div>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 40px;"><input type="checkbox" class="row-check"></th>
                <th>Username</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Status</th>
                <th>Role</th>
                <th style="width: 40px;"></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input type="checkbox" class="row-check"></td>
                <td style="font-weight:600;">satnaing</td>
                <td>Sat Naing</td>
                <td style="color:var(--text-muted);">satnaingdev@gmail.com</td>
                <td style="color:var(--text-muted);">+1 (555) 234-5678</td>
                <td><span class="user-status-pill active">● Active</span></td>
                <td><span class="role-badge">Superadmin</span></td>
                <td><button class="row-action-btn">•••</button></td>
              </tr>
              <tr>
                <td><input type="checkbox" class="row-check"></td>
                <td style="font-weight:600;">d_perera</td>
                <td>Damith Perera</td>
                <td style="color:var(--text-muted);">damith@smartmove.lk</td>
                <td style="color:var(--text-muted);">+94 77 123 4567</td>
                <td><span class="user-status-pill active">● Active</span></td>
                <td><span class="role-badge">Admin</span></td>
                <td><button class="row-action-btn">•••</button></td>
              </tr>
              <tr>
                <td><input type="checkbox" class="row-check"></td>
                <td style="font-weight:600;">k_silva</td>
                <td>Kasun Silva</td>
                <td style="color:var(--text-muted);">kasun@smartmove.lk</td>
                <td style="color:var(--text-muted);">+94 71 987 6543</td>
                <td><span class="user-status-pill active">● Active</span></td>
                <td><span class="role-badge">Driver</span></td>
                <td><button class="row-action-btn">•••</button></td>
              </tr>
              <tr>
                <td><input type="checkbox" class="row-check"></td>
                <td style="font-weight:600;">n_fernando</td>
                <td>Nadeesha Fernando</td>
                <td style="color:var(--text-muted);">nadeesha@gmail.com</td>
                <td style="color:var(--text-muted);">+94 76 345 6789</td>
                <td><span class="user-status-pill invited">● Invited</span></td>
                <td><span class="role-badge">Passenger</span></td>
                <td><button class="row-action-btn">•••</button></td>
              </tr>
              <tr>
                <td><input type="checkbox" class="row-check"></td>
                <td style="font-weight:600;">m_kamal</td>
                <td>Mohamed Kamal</td>
                <td style="color:var(--text-muted);">kamal@techfleet.io</td>
                <td style="color:var(--text-muted);">+94 70 555 1212</td>
                <td><span class="user-status-pill suspended">● Suspended</span></td>
                <td><span class="role-badge">Driver</span></td>
                <td><button class="row-action-btn">•••</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- =====================================================================
           VIEW 5: SETTINGS PAGE
           ===================================================================== -->
      <section class="page-view" id="settingsView">
        <div class="view-header" style="flex-direction:column; align-items:flex-start;">
          <div class="view-title-group">
            <h1>Settings</h1>
            <p>Manage your account settings and set e-mail preferences.</p>
          </div>
        </div>

        <div style="height:1px; background-color:var(--border-subtle); margin: 6px 0 16px 0;"></div>

        <div class="settings-layout">
          <!-- Left Sub-navigation Pills -->
          <div class="settings-nav">
            <a class="settings-nav-item active">Profile</a>
            <a class="settings-nav-item">Account</a>
            <a class="settings-nav-item">Appearance</a>
            <a class="settings-nav-item">Notifications</a>
            <a class="settings-nav-item">Display</a>
          </div>

          <!-- Right Content Form -->
          <div class="settings-form-panel">
            <div>
              <h2 style="font-size:18px; font-weight:600; color:var(--text-white);">Profile</h2>
              <p style="font-size:13px; color:var(--text-muted); margin-top:2px;">This is how others will see you on the site.</p>
            </div>

            <div style="height:1px; background-color:var(--border-subtle);"></div>

            <!-- Username -->
            <div class="settings-form-group">
              <label class="settings-label">Username</label>
              <input type="text" class="settings-input" value="shadcn">
              <span class="settings-desc">This is your public display name. It can be your real name or a pseudonym. You can only change this once every 30 days.</span>
            </div>

            <!-- Email -->
            <div class="settings-form-group">
              <label class="settings-label">Email</label>
              <select class="settings-select">
                <option>Select a verified email to display ⌄</option>
                <option selected>satnaingdev@gmail.com</option>
                <option>admin@smartmove.lk</option>
              </select>
              <span class="settings-desc">You can manage verified email addresses in your email settings.</span>
            </div>

            <!-- Bio -->
            <div class="settings-form-group">
              <label class="settings-label">Bio</label>
              <textarea class="settings-textarea">I own a computer.</textarea>
              <span class="settings-desc">You can @mention other users and organizations to link to them.</span>
            </div>

            <!-- URLs -->
            <div class="settings-form-group">
              <label class="settings-label">URLs</label>
              <input type="text" class="settings-input" placeholder="https://example.com" value="https://smartmove.lk">
              <span class="settings-desc">Add links to your website, blog, or social media profiles.</span>
            </div>

            <div style="margin-top: 10px;">
              <button class="btn-primary" type="button">Update profile</button>
            </div>
          </div>
        </div>
      </section>

    </div>
  </main>

</div>

<script src="../js/dashboard.js"></script>
</body>
</html>
