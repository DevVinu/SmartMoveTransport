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

  <!-- Left Sidebar -->
  <aside class="dash-sidebar" id="dashSidebar">
    <div>
      <div class="sidebar-brand-row">
        <a href="../index.php" class="sidebar-logo">SmartMove<span class="blue-dot">.</span></a>
        <span class="badge-portal">Operations</span>
      </div>

      <ul class="sidebar-nav-list">
        <li class="sidebar-nav-item active">
          <a href="#" data-view="overview">
            <span class="nav-icon">📊</span>
            <span>Overview</span>
          </a>
        </li>
        <li class="sidebar-nav-item">
          <a href="#" data-view="dispatch">
            <span class="nav-icon">🗺️</span>
            <span>Live Dispatch</span>
            <span class="sidebar-badge">8 Live</span>
          </a>
        </li>
        <li class="sidebar-nav-item">
          <a href="#" data-view="fleet">
            <span class="nav-icon">🚘</span>
            <span>Fleet & Drivers</span>
          </a>
        </li>
        <li class="sidebar-nav-item">
          <a href="#" data-view="bookings">
            <span class="nav-icon">📑</span>
            <span>Booking History</span>
          </a>
        </li>
        <li class="sidebar-nav-item">
          <a href="#" data-view="revenue">
            <span class="nav-icon">💳</span>
            <span>Billing & Revenue</span>
          </a>
        </li>
        <li class="sidebar-nav-item">
          <a href="#" data-view="settings">
            <span class="nav-icon">⚙️</span>
            <span>Settings</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- User Profile Card -->
    <div class="sidebar-footer-card">
      <div class="passenger-avatar" style="width: 38px; height: 38px; font-size: 1rem;">VD</div>
      <div class="user-meta">
        <div class="user-name">Vinu Dissanayake</div>
        <div class="user-role">Fleet Director</div>
      </div>
      <a href="../index.php" class="btn-exit-portal" title="Return to Public Site">↗</a>
    </div>
  </aside>

  <!-- Main Area -->
  <div class="dash-main-area">

    <!-- Top Navigation Bar -->
    <header class="dash-topbar">
      <div class="topbar-left">
        <button id="toggleSidebarBtn" style="background:none; border:none; color:#FFFFFF; font-size:1.3rem; cursor:pointer; display:none;" aria-label="Toggle menu">☰</button>
        <div class="page-heading-group">
          <h1>Operations <span class="blue-text">Dashboard</span></h1>
          <p>Real-time mobility telematics & dispatch monitoring • Colombo Central</p>
        </div>
      </div>

      <div class="topbar-right">
        <!-- Search bar -->
        <div class="topbar-search-box">
          <span style="color: var(--blue-light);">🔍</span>
          <input type="text" class="topbar-search-input" id="globalSearchInput" placeholder="Search ride, driver, or fleet ID...">
          <span class="search-shortcut">⌘K</span>
        </div>

        <!-- Live status -->
        <div class="live-pill-tag">
          <span class="live-dot-pulse"></span>
          <span>Fleet Online</span>
        </div>

        <!-- Role preview selector -->
        <div class="role-switcher-group" id="roleSwitcher">
          <button type="button" class="role-tab-btn active" data-role="Admin">Admin</button>
          <button type="button" class="role-tab-btn" data-role="Driver">Driver</button>
          <button type="button" class="role-tab-btn" data-role="Passenger">Passenger</button>
        </div>

        <button class="btn-icon-square" title="Notifications" aria-label="Notifications">
          🔔
          <span class="notif-dot-blue"></span>
        </button>

        <a href="../index.php" class="btn-table-action" style="padding: 0.55rem 1.1rem; text-decoration: none;">← Exit to Home</a>
      </div>
    </header>

    <!-- Main Content Body -->
    <main class="dash-content-container">

      <!-- Metrics Row (4 Cards) -->
      <section class="metrics-cards-grid">
        
        <div class="metric-card">
          <div class="metric-top-row">
            <span class="metric-title">Active Fleet Vehicles</span>
            <div class="metric-icon-wrap">🚘</div>
          </div>
          <div class="metric-value-row">
            <span class="metric-big-num" id="activeFleetCounter">148</span>
            <span class="metric-badge-blue">+12.4%</span>
          </div>
          <span class="metric-caption">12 dispatched in the last 15 mins</span>
        </div>

        <div class="metric-card">
          <div class="metric-top-row">
            <span class="metric-title">Today's Completed Trips</span>
            <div class="metric-icon-wrap">🏁</div>
          </div>
          <div class="metric-value-row">
            <span class="metric-big-num">1,284</span>
            <span class="metric-badge-blue">+18.2%</span>
          </div>
          <span class="metric-caption">99.4% on-time arrival rate</span>
        </div>

        <div class="metric-card">
          <div class="metric-top-row">
            <span class="metric-title">Gross Mobility Revenue</span>
            <div class="metric-icon-wrap">💳</div>
          </div>
          <div class="metric-value-row">
            <span class="metric-big-num">$38,420</span>
            <span class="metric-badge-blue">+8.5%</span>
          </div>
          <span class="metric-caption">Exceeding daily target by $4,200</span>
        </div>

        <div class="metric-card">
          <div class="metric-top-row">
            <span class="metric-title">Average Pickup Response</span>
            <div class="metric-icon-wrap">⏱️</div>
          </div>
          <div class="metric-value-row">
            <span class="metric-big-num">3.8 <span style="font-size: 1.1rem; font-weight: 600; color: var(--text-sub);">min</span></span>
            <span class="metric-badge-blue">-0.7m</span>
          </div>
          <span class="metric-caption">Fastest response in Colombo sector</span>
        </div>

      </section>

      <!-- Two Column Layout: Radar Dispatch + Quick Dispatch Box -->
      <section class="dash-two-col-grid">
        
        <!-- Live Dispatch Map Radar Simulator -->
        <div class="dispatch-map-card">
          <div class="card-header-bar">
            <h2 class="card-title-main">
              <span>📡</span> Live Fleet Radar & Telematics
            </h2>
            <div style="display: flex; gap: 0.6rem; align-items: center;">
              <span class="metric-badge-blue" id="radarSectorBadge">Sector: Colombo Central</span>
              <button class="btn-table-action" id="refreshRadarBtn">Refresh</button>
            </div>
          </div>

          <!-- Mock Map Container -->
          <div class="map-viewport-mock" id="mapMock">
            <div class="map-grid-overlay"></div>
            <div class="map-radar-circle"></div>

            <!-- Simulated live vehicle pins -->
            <div class="vehicle-map-pin" style="top: 28%; left: 22%;">
              <span class="pin-dot-blue"></span>
              <span>Sedan #104 • In Transit</span>
            </div>

            <div class="vehicle-map-pin" style="top: 55%; left: 62%;">
              <span class="pin-dot-blue"></span>
              <span>Van #208 • En Route</span>
            </div>

            <div class="vehicle-map-pin" style="top: 36%; left: 74%;">
              <span class="pin-dot-blue"></span>
              <span>Airport #012 • Boarding</span>
            </div>

            <div class="vehicle-map-pin" style="top: 70%; left: 34%;">
              <span class="pin-dot-blue"></span>
              <span>Courier #339 • Delivering</span>
            </div>

            <!-- Bottom Floating Map Stats -->
            <div class="map-overlay-stats-bar">
              <div class="map-stat-item">
                <div class="stat-num">148</div>
                <div class="stat-lbl">Active Vehicles</div>
              </div>
              <div class="map-stat-item">
                <div class="stat-num blue-text">4.9 ★</div>
                <div class="stat-lbl">Fleet Rating</div>
              </div>
              <div class="map-stat-item">
                <div class="stat-num">18</div>
                <div class="stat-lbl">Available Now</div>
              </div>
              <div class="map-stat-item">
                <div class="stat-num blue-text">0 Incidents</div>
                <div class="stat-lbl">Safety Score 100%</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Dispatch Ride Form -->
        <div class="quick-dispatch-card">
          <div class="card-header-bar">
            <h2 class="card-title-main">
              <span>⚡</span> Fast Dispatch Request
            </h2>
            <span class="badge-portal">Direct Booking</span>
          </div>

          <form id="quickDispatchForm" onsubmit="event.preventDefault(); handleQuickDispatch();">
            <div class="dispatch-form-group">
              <label class="dispatch-label" for="dispatchPickup">Pickup Location</label>
              <input type="text" id="dispatchPickup" class="dispatch-input" value="Colombo Fort Central" required>
            </div>

            <div class="dispatch-form-group" style="margin-top: 0.8rem;">
              <label class="dispatch-label" for="dispatchDropoff">Destination</label>
              <input type="text" id="dispatchDropoff" class="dispatch-input" value="Bandaranaike Int. Airport (CMB)" required>
            </div>

            <div class="dispatch-form-group" style="margin-top: 0.8rem;">
              <label class="dispatch-label">Vehicle Tier</label>
              <div class="vehicle-choice-row">
                <div class="vehicle-choice-btn selected" data-tier="sedan" data-fare="42.50">
                  <span class="choice-icon">🚘</span>
                  <span class="choice-name">Sedan</span>
                  <span class="choice-fare">$42.50</span>
                </div>
                <div class="vehicle-choice-btn" data-tier="van" data-fare="68.00">
                  <span class="choice-icon">🚐</span>
                  <span class="choice-name">Van</span>
                  <span class="choice-fare">$68.00</span>
                </div>
                <div class="vehicle-choice-btn" data-tier="bus" data-fare="120.00">
                  <span class="choice-icon">🚌</span>
                  <span class="choice-name">Bus</span>
                  <span class="choice-fare">$120.00</span>
                </div>
              </div>
            </div>

            <div class="fare-estimate-box" style="margin-top: 1rem;">
              <div>
                <div style="font-size: 0.76rem; color: var(--text-sub); text-transform: uppercase; font-weight: 700;">Estimated Total</div>
                <div style="font-size: 0.78rem; color: var(--text-muted);">Includes tax & toll fees</div>
              </div>
              <div class="fare-total-num blue-text" id="fareTotalDisplay">$42.50</div>
            </div>

            <button type="submit" class="btn-dispatch-submit" style="margin-top: 1.2rem;" id="dispatchSubmitBtn">
              Confirm & Dispatch Driver
            </button>
          </form>
        </div>

      </section>

      <!-- Recent Trips / Bookings Table Section -->
      <section class="bookings-section-card">
        <div class="card-header-bar">
          <h2 class="card-title-main">
            <span>📋</span> Active & Recent Dispatches
          </h2>
          <div style="display: flex; gap: 0.8rem;">
            <button class="btn-table-action" id="filterAllBtn">All (148)</button>
            <button class="btn-table-action" id="filterTransitBtn">In Transit (12)</button>
          </div>
        </div>

        <div class="table-responsive-wrapper">
          <table class="dash-table" id="bookingsTable">
            <thead>
              <tr>
                <th>Booking ID</th>
                <th>Passenger / Client</th>
                <th>Category</th>
                <th>Pickup → Destination</th>
                <th>Status</th>
                <th>Fare</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="trip-id-code">#SM-9824</td>
                <td>
                  <div class="passenger-cell">
                    <div class="passenger-avatar">AK</div>
                    <div>
                      <div style="font-weight: 700; color: #FFFFFF;">Anura Kumara</div>
                      <div style="font-size: 0.74rem; color: var(--text-sub);">Passenger VIP</div>
                    </div>
                  </div>
                </td>
                <td><span style="color: #FFFFFF; font-weight: 600;">Executive Sedan</span></td>
                <td>Galle Face Hotel → Cinnamon Grand</td>
                <td>
                  <span class="status-badge-pill status-in-transit">
                    <span class="pin-dot-blue"></span> In Transit
                  </span>
                </td>
                <td style="font-weight: 800; color: #FFFFFF;">$32.50</td>
                <td><button class="btn-table-action" onclick="alert('Viewing telemetry details for Booking #SM-9824');">Details</button></td>
              </tr>

              <tr>
                <td class="trip-id-code">#SM-9823</td>
                <td>
                  <div class="passenger-cell">
                    <div class="passenger-avatar">SR</div>
                    <div>
                      <div style="font-weight: 700; color: #FFFFFF;">Sarah Reynolds</div>
                      <div style="font-size: 0.74rem; color: var(--text-sub);">Corporate Account</div>
                    </div>
                  </div>
                </td>
                <td><span style="color: #FFFFFF; font-weight: 600;">Chauffeur Van</span></td>
                <td>World Trade Center → Airport CMB</td>
                <td>
                  <span class="status-badge-pill status-in-transit">
                    <span class="pin-dot-blue"></span> In Transit
                  </span>
                </td>
                <td style="font-weight: 800; color: #FFFFFF;">$78.00</td>
                <td><button class="btn-table-action" onclick="alert('Viewing telemetry details for Booking #SM-9823');">Details</button></td>
              </tr>

              <tr>
                <td class="trip-id-code">#SM-9822</td>
                <td>
                  <div class="passenger-cell">
                    <div class="passenger-avatar">DL</div>
                    <div>
                      <div style="font-weight: 700; color: #FFFFFF;">Devinda Lokuge</div>
                      <div style="font-size: 0.74rem; color: var(--text-sub);">Standard Passenger</div>
                    </div>
                  </div>
                </td>
                <td><span style="color: #FFFFFF; font-weight: 600;">City Express</span></td>
                <td>Mount Lavinia Beach → Colombo 03</td>
                <td>
                  <span class="status-badge-pill status-completed">Completed</span>
                </td>
                <td style="font-weight: 800; color: #FFFFFF;">$14.20</td>
                <td><button class="btn-table-action" onclick="alert('Viewing receipt for Booking #SM-9822');">Receipt</button></td>
              </tr>

              <tr>
                <td class="trip-id-code">#SM-9821</td>
                <td>
                  <div class="passenger-cell">
                    <div class="passenger-avatar">MK</div>
                    <div>
                      <div style="font-weight: 700; color: #FFFFFF;">Marcus Knight</div>
                      <div style="font-size: 0.74rem; color: var(--text-sub);">International Flight</div>
                    </div>
                  </div>
                </td>
                <td><span style="color: #FFFFFF; font-weight: 600;">Airport Transfer</span></td>
                <td>Negombo Lagoon Resort → CMB Terminal 1</td>
                <td>
                  <span class="status-badge-pill status-completed">Completed</span>
                </td>
                <td style="font-weight: 800; color: #FFFFFF;">$55.00</td>
                <td><button class="btn-table-action" onclick="alert('Viewing receipt for Booking #SM-9821');">Receipt</button></td>
              </tr>

              <tr>
                <td class="trip-id-code">#SM-9820</td>
                <td>
                  <div class="passenger-cell">
                    <div class="passenger-avatar">TC</div>
                    <div>
                      <div style="font-weight: 700; color: #FFFFFF;">TechCorp Lanka</div>
                      <div style="font-size: 0.74rem; color: var(--text-sub);">Staff Shuttle</div>
                    </div>
                  </div>
                </td>
                <td><span style="color: #FFFFFF; font-weight: 600;">Intercity Bus</span></td>
                <td>Kandy Road Junction → Colombo Fort</td>
                <td>
                  <span class="status-badge-pill status-scheduled">Scheduled (15:00)</span>
                </td>
                <td style="font-weight: 800; color: #FFFFFF;">$180.00</td>
                <td><button class="btn-table-action" onclick="alert('Viewing scheduled manifest for Booking #SM-9820');">Manifest</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

    </main>

  </div>

  <!-- Interactive JavaScript logic -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Vehicle Tier choice selection
      const vehicleBtns = document.querySelectorAll('.vehicle-choice-btn');
      const fareDisplay = document.getElementById('fareTotalDisplay');

      vehicleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          vehicleBtns.forEach(b => b.classList.remove('selected'));
          btn.classList.add('selected');
          const fare = btn.getAttribute('data-fare');
          if (fare && fareDisplay) {
            fareDisplay.textContent = '$' + parseFloat(fare).toFixed(2);
          }
        });
      });

      // Role switcher toggle
      const roleBtns = document.querySelectorAll('.role-tab-btn');
      roleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          roleBtns.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          const role = btn.getAttribute('data-role');
          const heading = document.querySelector('.page-heading-group h1');
          if (heading) {
            heading.innerHTML = role + ' <span class="blue-text">Portal View</span>';
          }
        });
      });

      // Radar refresh simulation
      const refreshBtn = document.getElementById('refreshRadarBtn');
      if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
          refreshBtn.textContent = 'Updating...';
          setTimeout(() => {
            refreshBtn.textContent = 'Refreshed ✓';
            setTimeout(() => { refreshBtn.textContent = 'Refresh'; }, 1500);
          }, 400);
        });
      }

      // Quick filter buttons for table
      const filterAll = document.getElementById('filterAllBtn');
      const filterTransit = document.getElementById('filterTransitBtn');
      const rows = document.querySelectorAll('#bookingsTable tbody tr');

      if (filterAll && filterTransit) {
        filterAll.addEventListener('click', () => {
          rows.forEach(r => r.style.display = '');
        });
        filterTransit.addEventListener('click', () => {
          rows.forEach(r => {
            const hasTransit = r.querySelector('.status-in-transit');
            r.style.display = hasTransit ? '' : 'none';
          });
        });
      }
    });

    // Quick Dispatch simulation handler
    function handleQuickDispatch() {
      const pickup = document.getElementById('dispatchPickup').value;
      const dropoff = document.getElementById('dispatchDropoff').value;
      const fare = document.getElementById('fareTotalDisplay').textContent;
      const counter = document.getElementById('activeFleetCounter');

      alert(`🚀 Vehicle Dispatched!\n\nPickup: ${pickup}\nDestination: ${dropoff}\nFare: ${fare}\nStatus: Driver notified via GPS telematics.`);

      if (counter) {
        let count = parseInt(counter.textContent) || 148;
        counter.textContent = count + 1;
      }
    }
  </script>

</body>
</html>
