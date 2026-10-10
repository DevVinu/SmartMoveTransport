<?php
$pageTitle = "Operations Dashboard | SmartMove Transport";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  
  <link rel="stylesheet" href="../css/variables.css">
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body class="dashboard-body">

  <!-- Left Sidebar (NO ICONS inside menu items as requested) -->
  <aside class="dash-sidebar" id="dashSidebar">
    
    <!-- Sidebar Header / Logo -->
    <div class="sidebar-header">
      <div class="sidebar-logo-badge">SM</div>
      <div class="sidebar-brand-text">
        <span>SmartMove<span class="blue-dot">.</span></span>
        <span class="sidebar-brand-version">Portal</span>
      </div>
    </div>

    <!-- Scrollable Navigation Groups -->
    <div class="sidebar-scrollable">
      
      <!-- GENERAL Group -->
      <div>
        <div class="sidebar-group-title">GENERAL</div>
        <ul class="sidebar-nav-list">
          <li class="sidebar-nav-item active">
            <a href="#">
              <span class="item-label">Dashboards</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Forms</span>
              <span class="sidebar-badge-hot">Hot</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Tables</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Charts</span>
              <span class="sidebar-badge-new">New</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Calendar</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Map</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- APPS Group -->
      <div>
        <div class="sidebar-group-title">APPS</div>
        <ul class="sidebar-nav-list">
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Chat</span>
              <span class="sidebar-badge-count">3</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Inbox</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Kanban</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Files</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Notifications</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- E-COMMERCE / MOBILITY Group -->
      <div>
        <div class="sidebar-group-title">FLEET & E-COMMERCE</div>
        <ul class="sidebar-nav-list">
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Storefront</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Product / Fleet</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Orders</span>
              <span class="sidebar-chevron">›</span>
            </a>
          </li>
          <li class="sidebar-nav-item">
            <a href="#">
              <span class="item-label">Invoice</span>
            </a>
          </li>
        </ul>
      </div>

    </div>

    <!-- Sidebar Bottom User Profile -->
    <div class="sidebar-user-footer">
      <div class="user-avatar-circle">VD</div>
      <div class="user-info-text">
        <div class="user-info-name">Vinu Dissanayake</div>
        <div class="user-info-role">Fleet Admin</div>
      </div>
      <a href="../index.php" title="Back to Home" style="color: var(--dash-text-muted); font-size: 1rem; text-decoration: none;">↗</a>
    </div>

  </aside>

  <!-- Main Content Area -->
  <div class="dash-main-area">

    <!-- Top Navigation Bar -->
    <header class="dash-topbar">
      
      <div class="topbar-left">
        <button class="btn-hamburger" id="hamburgerBtn" title="Toggle Sidebar" aria-label="Toggle navigation">
          ☰
        </button>
        <div class="breadcrumb-path">
          <a href="../index.php">Home</a>
          <span>›</span>
          <span class="current">Dashboard</span>
        </div>
      </div>

      <!-- Center Search -->
      <div class="topbar-search-container">
        <span class="search-icon-svg">🔍</span>
        <input type="text" class="topbar-search-input" id="dashSearch" placeholder="Search pages or run a comm...">
        <span class="kbd-shortcut">⌘K</span>
      </div>

      <!-- Right Actions & User Avatar -->
      <div class="topbar-right">
        <a href="#docs" class="btn-docs-pill">
          <span>📄</span>
          <span>Docs</span>
        </a>

        <button class="btn-topbar-icon" title="Toggle Theme" aria-label="Toggle theme">
          ☼
        </button>

        <button class="btn-topbar-icon" title="Notifications" aria-label="Notifications" style="position: relative;">
          🔔
          <span style="position: absolute; top: 7px; right: 7px; width: 6px; height: 6px; background-color: var(--dash-blue); border-radius: 50%;"></span>
        </button>

        <button class="btn-topbar-icon" title="Messages" aria-label="Messages">
          ✉
        </button>

        <div class="topbar-user-avatar" title="Vinu Dissanayake (Admin)">
          VD
        </div>
      </div>

    </header>

    <!-- Main Workspace Content -->
    <main class="dash-content">

      <!-- Header: Overview / Actions -->
      <div class="dash-overview-row">
        <div class="overview-title-group">
          <div class="sub-label">OVERVIEW</div>
          <h1 class="main-title">Operations <span class="blue-text">Dashboard</span></h1>
        </div>

        <div class="overview-actions">
          <button class="btn-new-view">+ New view</button>
          <button class="btn-create-report">+ Create report</button>
        </div>
      </div>

      <!-- Top 6 Metric Cards (2 rows x 3 columns) -->
      <section class="metrics-grid-2x3">
        
        <!-- Card 1: Total Users -->
        <div class="metric-card-box">
          <div class="metric-card-content">
            <div class="metric-icon-square icon-blue-primary-theme">
              👥
            </div>
            <div class="metric-details-stack">
              <span class="metric-label-top">TOTAL USERS</span>
              <div class="metric-num-row">
                <span class="metric-big-value">2,500</span>
                <span class="metric-growth-pill">+ 12%</span>
              </div>
              <span class="metric-sub-note">342 new this week</span>
            </div>
          </div>
          <!-- Mini sparkline bars -->
          <div class="metric-sparkbars-wrap">
            <span class="spark-bar spark-blue-primary" style="height: 12px;"></span>
            <span class="spark-bar spark-blue-primary" style="height: 16px;"></span>
            <span class="spark-bar spark-blue-primary" style="height: 14px;"></span>
            <span class="spark-bar spark-blue-primary" style="height: 22px;"></span>
            <span class="spark-bar spark-blue-primary" style="height: 30px;"></span>
            <span class="spark-bar spark-blue-primary" style="height: 26px;"></span>
          </div>
        </div>

        <!-- Card 2: Avg Session -->
        <div class="metric-card-box">
          <div class="metric-card-content">
            <div class="metric-icon-square icon-blue-light-theme">
              ⏱
            </div>
            <div class="metric-details-stack">
              <span class="metric-label-top">AVG SESSION</span>
              <div class="metric-num-row">
                <span class="metric-big-value">123.5<span style="font-size: 0.9rem; font-weight: 500;">min</span></span>
                <span class="metric-growth-pill">+ 8%</span>
              </div>
              <span class="metric-sub-note">+14min from last week</span>
            </div>
          </div>
          <div class="metric-sparkbars-wrap">
            <span class="spark-bar spark-blue-light" style="height: 14px;"></span>
            <span class="spark-bar spark-blue-light" style="height: 18px;"></span>
            <span class="spark-bar spark-blue-light" style="height: 24px;"></span>
            <span class="spark-bar spark-blue-light" style="height: 28px;"></span>
            <span class="spark-bar spark-blue-light" style="height: 22px;"></span>
          </div>
        </div>

        <!-- Card 3: Orders -->
        <div class="metric-card-box">
          <div class="metric-card-content">
            <div class="metric-icon-square icon-white-accent-theme">
              💼
            </div>
            <div class="metric-details-stack">
              <span class="metric-label-top">ORDERS</span>
              <div class="metric-num-row">
                <span class="metric-big-value">1,240</span>
                <span class="metric-growth-pill">+ 3%</span>
              </div>
              <span class="metric-sub-note">78 shipped today</span>
            </div>
          </div>
          <div class="metric-sparkbars-wrap">
            <span class="spark-bar spark-white" style="height: 18px;"></span>
            <span class="spark-bar spark-white" style="height: 22px;"></span>
            <span class="spark-bar spark-white" style="height: 15px;"></span>
            <span class="spark-bar spark-white" style="height: 26px;"></span>
            <span class="spark-bar spark-white" style="height: 28px;"></span>
          </div>
        </div>

        <!-- Card 4: Revenue (with Blue Primary bottom bar) -->
        <div class="metric-card-box">
          <div class="metric-card-content">
            <div class="metric-icon-square icon-blue-primary-theme">
              $
            </div>
            <div class="metric-details-stack">
              <span class="metric-label-top">REVENUE</span>
              <div class="metric-num-row">
                <span class="metric-big-value">$24,567</span>
                <span class="metric-growth-pill">+ 18%</span>
              </div>
              <span class="metric-sub-note">$3,218 today</span>
            </div>
          </div>
          <div class="metric-progress-line line-blue-primary"></div>
        </div>

        <!-- Card 5: Conversions (with Blue Light bottom bar) -->
        <div class="metric-card-box">
          <div class="metric-card-content">
            <div class="metric-icon-square icon-blue-light-theme">
              📉
            </div>
            <div class="metric-details-stack">
              <span class="metric-label-top">CONVERSIONS</span>
              <div class="metric-num-row">
                <span class="metric-big-value">2,315</span>
                <span class="metric-growth-pill">+ 5%</span>
              </div>
              <span class="metric-sub-note">Rate: 4.2%</span>
            </div>
          </div>
          <div class="metric-progress-line line-blue-light"></div>
        </div>

        <!-- Card 6: Page Views (with White Accent bottom bar) -->
        <div class="metric-card-box">
          <div class="metric-card-content">
            <div class="metric-icon-square icon-white-accent-theme">
              👁
            </div>
            <div class="metric-details-stack">
              <span class="metric-label-top">PAGE VIEWS</span>
              <div class="metric-num-row">
                <span class="metric-big-value">47,325</span>
                <span class="metric-growth-pill">+ 22%</span>
              </div>
              <span class="metric-sub-note">6,854 unique visitors</span>
            </div>
          </div>
          <div class="metric-progress-line line-white-accent"></div>
        </div>

      </section>

      <!-- Middle Two-Column Grid: Network Activities Chart & Recent Activity List -->
      <section class="dash-middle-two-col">

        <!-- Left: Network Activities Chart Card -->
        <div class="chart-card-box">
          
          <div class="chart-card-header">
            <div class="chart-title-area">
              <h2 class="chart-title">Network Activities</h2>
              <div class="chart-num-row">
                <span class="chart-big-num" id="networkMetricNum">6,782</span>
                <span class="chart-badge">+ 7%</span>
              </div>
              <div class="chart-sub-label">Total fleet telemetry sessions this week</div>
            </div>

            <!-- Time segment switcher -->
            <div class="chart-segmented-control" id="timeRangeControl">
              <button class="segment-btn active" data-days="7">7 days</button>
              <button class="segment-btn" data-days="30">30 days</button>
              <button class="segment-btn" data-days="90">90 days</button>
            </div>
          </div>

          <!-- Scalable Vector Line Chart in Blue & White Theme -->
          <div class="chart-viewport-wrap">
            <svg class="svg-chart-container" viewBox="0 0 650 220" preserveAspectRatio="none">
              <defs>
                <!-- Blue area gradient glow matching website theme -->
                <linearGradient id="blueGlowGradient" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#3B82F6" stop-opacity="0.28"/>
                  <stop offset="100%" stop-color="#3B82F6" stop-opacity="0.0"/>
                </linearGradient>
              </defs>

              <!-- Y-Axis Grid Lines & Labels -->
              <!-- 800 -->
              <text x="32" y="20" fill="#94A3B8" font-size="10" text-anchor="end">800</text>
              <line x1="45" y1="16" x2="640" y2="16" stroke="rgba(255, 255, 255, 0.05)" stroke-dasharray="3,3" stroke-width="1"/>

              <!-- 600 -->
              <text x="32" y="65" fill="#94A3B8" font-size="10" text-anchor="end">600</text>
              <line x1="45" y1="61" x2="640" y2="61" stroke="rgba(255, 255, 255, 0.05)" stroke-dasharray="3,3" stroke-width="1"/>

              <!-- 400 -->
              <text x="32" y="110" fill="#94A3B8" font-size="10" text-anchor="end">400</text>
              <line x1="45" y1="106" x2="640" y2="106" stroke="rgba(255, 255, 255, 0.05)" stroke-dasharray="3,3" stroke-width="1"/>

              <!-- 200 -->
              <text x="32" y="155" fill="#94A3B8" font-size="10" text-anchor="end">200</text>
              <line x1="45" y1="151" x2="640" y2="151" stroke="rgba(255, 255, 255, 0.05)" stroke-dasharray="3,3" stroke-width="1"/>

              <!-- 0 -->
              <text x="32" y="195" fill="#94A3B8" font-size="10" text-anchor="end">0</text>
              <line x1="45" y1="191" x2="640" y2="191" stroke="rgba(255, 255, 255, 0.05)" stroke-width="1"/>

              <!-- X-Axis Labels -->
              <text x="50" y="212" fill="#CBD5E1" font-size="10">Mon</text>
              <text x="145" y="212" fill="#CBD5E1" font-size="10">Tue</text>
              <text x="240" y="212" fill="#CBD5E1" font-size="10">Wed</text>
              <text x="335" y="212" fill="#CBD5E1" font-size="10">Thu</text>
              <text x="430" y="212" fill="#CBD5E1" font-size="10">Fri</text>
              <text x="525" y="212" fill="#CBD5E1" font-size="10">Sat</text>
              <text x="615" y="212" fill="#CBD5E1" font-size="10">Sun</text>

              <!-- Blue Filled Gradient Area -->
              <path id="blueAreaPath" d="M 50 148 C 110 135, 170 120, 240 122 C 300 125, 335 70, 420 50 C 490 35, 545 65, 630 68 L 630 191 L 50 191 Z" fill="url(#blueGlowGradient)" />

              <!-- Blue Solid Curve: Sessions -->
              <path id="blueLinePath" d="M 50 148 C 110 135, 170 120, 240 122 C 300 125, 335 70, 420 50 C 490 35, 545 65, 630 68" fill="none" stroke="#3B82F6" stroke-width="2.6" stroke-linecap="round" />

              <!-- White Dashed Curve: Page Views -->
              <path id="whiteLinePath" d="M 50 162 C 110 152, 170 138, 240 140 C 300 142, 335 95, 420 85 C 490 75, 545 92, 630 96" fill="none" stroke="#FFFFFF" stroke-width="1.8" stroke-dasharray="5,5" stroke-linecap="round" opacity="0.85" />
            </svg>
          </div>

          <!-- Chart Legend -->
          <div class="chart-legend-row">
            <div class="legend-item">
              <span class="legend-indicator-line line-solid-blue"></span>
              <span>Sessions</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator-line line-dashed-white"></span>
              <span>Page views</span>
            </div>
          </div>

        </div>

        <!-- Right: Recent Activity Card -->
        <div class="activity-card-box">
          
          <div class="activity-header">
            <h2 class="activity-title">Recent Activity</h2>
            <button class="activity-minimize-btn" title="Collapse" aria-label="Minimize">―</button>
          </div>

          <div class="activity-list">
            
            <!-- Item 1: Sarah K. (Royal Blue) -->
            <div class="activity-item">
              <div class="activity-avatar avatar-blue-pri">SK</div>
              <div class="activity-content">
                <div class="activity-text">
                  <strong>Sarah K.</strong> placed a new booking for $245.00
                </div>
                <div class="activity-timestamp">2 min ago</div>
              </div>
            </div>

            <!-- Item 2: Michael R. (Luminous Blue) -->
            <div class="activity-item">
              <div class="activity-avatar avatar-blue-light">MR</div>
              <div class="activity-content">
                <div class="activity-text">
                  <strong>Michael R.</strong> registered a passenger account
                </div>
                <div class="activity-timestamp">18 min ago</div>
              </div>
            </div>

            <!-- Item 3: Payment (Black & Blue outline) -->
            <div class="activity-item">
              <div class="activity-avatar avatar-black-solid">SY</div>
              <div class="activity-content">
                <div class="activity-text">
                  <strong>Payment</strong> processed — Invoice #4521
                </div>
                <div class="activity-timestamp">45 min ago</div>
              </div>
            </div>

            <!-- Item 4: Jeffie L. (Dark Gray) -->
            <div class="activity-item">
              <div class="activity-avatar avatar-gray-dark">JL</div>
              <div class="activity-content">
                <div class="activity-text">
                  <strong>Jeffie L.</strong> reviewed <strong>SmartMove Fleet</strong>
                </div>
                <div class="activity-timestamp">1 hour ago</div>
              </div>
            </div>

            <!-- Item 5: Emmy L. (Deep Blue) -->
            <div class="activity-item">
              <div class="activity-avatar avatar-blue-hover">EL</div>
              <div class="activity-content">
                <div class="activity-text">
                  <strong>Emmy L.</strong> scheduled dispatch <strong>Airport CMB</strong>
                </div>
                <div class="activity-timestamp">4 hours ago</div>
              </div>
            </div>

            <!-- Item 6: Shipment (Crisp White) -->
            <div class="activity-item">
              <div class="activity-avatar avatar-white-solid">DS</div>
              <div class="activity-content">
                <div class="activity-text">
                  <strong>Chauffeur</strong> dispatched — Trip #3847
                </div>
                <div class="activity-timestamp">6 hours ago</div>
              </div>
            </div>

          </div>

        </div>

      </section>

    </main>

  </div>

  <!-- Interactive JavaScript logic -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Sidebar Mobile Toggle
      const hamburger = document.getElementById('hamburgerBtn');
      const sidebar = document.getElementById('dashSidebar');
      if (hamburger && sidebar) {
        hamburger.addEventListener('click', () => {
          sidebar.classList.toggle('open');
        });
      }

      // Time Range Segment Switcher & Chart Morph Simulation
      const segmentBtns = document.querySelectorAll('.segment-btn');
      const metricNum = document.getElementById('networkMetricNum');
      const blueLine = document.getElementById('blueLinePath');
      const blueArea = document.getElementById('blueAreaPath');
      const whiteLine = document.getElementById('whiteLinePath');

      const chartData = {
        '7': {
          num: '6,782',
          blue: 'M 50 148 C 110 135, 170 120, 240 122 C 300 125, 335 70, 420 50 C 490 35, 545 65, 630 68',
          area: 'M 50 148 C 110 135, 170 120, 240 122 C 300 125, 335 70, 420 50 C 490 35, 545 65, 630 68 L 630 191 L 50 191 Z',
          white: 'M 50 162 C 110 152, 170 138, 240 140 C 300 142, 335 95, 420 85 C 490 75, 545 92, 630 96'
        },
        '30': {
          num: '28,490',
          blue: 'M 50 120 C 120 90, 190 140, 260 80 C 330 60, 400 95, 480 40 C 530 30, 580 50, 630 45',
          area: 'M 50 120 C 120 90, 190 140, 260 80 C 330 60, 400 95, 480 40 C 530 30, 580 50, 630 45 L 630 191 L 50 191 Z',
          white: 'M 50 140 C 120 115, 190 160, 260 105 C 330 85, 400 115, 480 65 C 530 55, 580 75, 630 70'
        },
        '90': {
          num: '84,120',
          blue: 'M 50 160 C 130 110, 200 70, 290 85 C 370 100, 450 50, 520 30 C 560 25, 600 40, 630 35',
          area: 'M 50 160 C 130 110, 200 70, 290 85 C 370 100, 450 50, 520 30 C 560 25, 600 40, 630 35 L 630 191 L 50 191 Z',
          white: 'M 50 180 C 130 135, 200 95, 290 110 C 370 125, 450 75, 520 55 C 560 50, 600 65, 630 60'
        }
      };

      segmentBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          segmentBtns.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          const days = btn.getAttribute('data-days');
          if (chartData[days]) {
            if (metricNum) metricNum.textContent = chartData[days].num;
            if (blueLine) blueLine.setAttribute('d', chartData[days].blue);
            if (blueArea) blueArea.setAttribute('d', chartData[days].area);
            if (whiteLine) whiteLine.setAttribute('d', chartData[days].white);
          }
        });
      });

      // Search bar shortcut keybind (⌘K or Ctrl+K)
      const searchInput = document.getElementById('dashSearch');
      document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
          e.preventDefault();
          if (searchInput) searchInput.focus();
        }
      });
    });
  </script>

</body>
</html>
