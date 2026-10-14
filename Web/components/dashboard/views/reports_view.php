        <!-- Reports view -->
        <section class="page-view <?php echo $activeView === 'reportsView' ? 'active-view' : ''; ?>" id="reportsView">
          <div class="view-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:1.5rem;">
            <div class="view-title-group">
              <h1 style="font-size:1.6rem; font-weight:700;">Operational Reports &amp; Business Analytics</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Enterprise performance intelligence and operational analytics across passenger routes and fleet</p>
            </div>
            <div class="view-actions" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
              <a href="export_report.php?report=<?php echo urlencode($activeReport); ?>&autoPrint=1" target="_blank" class="btn-primary" style="display:inline-flex; align-items:center; gap:6px; padding:0.5rem 1rem; text-decoration:none; font-size:0.82rem; font-weight:600; border-radius:6px; background:var(--blue-primary); border:1px solid var(--blue-primary); color:#FFFFFF; box-shadow:none;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                  <polyline points="14 2 14 8 20 8"></polyline>
                  <line x1="16" y1="13" x2="8" y2="13"></line>
                  <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                Export Current Report (PDF)
              </a>
              <a href="export_report.php?report=all&autoPrint=1" target="_blank" class="btn-primary" style="display:inline-flex; align-items:center; gap:6px; padding:0.5rem 1rem; text-decoration:none; font-size:0.82rem; font-weight:600; border-radius:6px; background:var(--bg-card-inner); border:1px solid var(--border-subtle); color:#FFFFFF;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                  <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                Export All 5 Reports
              </a>
            </div>
          </div>

          <!-- Report tabs -->
          <div class="reports-nav-bar">
            <a href="dashboard.php?view=reportsView&report=frequentRoutes"
              class="report-tab-btn <?php echo $activeReport === 'frequentRoutes' ? 'active' : ''; ?>">
              Report 1: Frequent Routes
            </a>
            <a href="dashboard.php?view=reportsView&report=periodRevenue"
              class="report-tab-btn <?php echo $activeReport === 'periodRevenue' ? 'active' : ''; ?>">
              Report 2: Period Revenue
            </a>
            <a href="dashboard.php?view=reportsView&report=passengerHistory"
              class="report-tab-btn <?php echo $activeReport === 'passengerHistory' ? 'active' : ''; ?>">
              Report 3: Passenger Travel History
            </a>
            <a href="dashboard.php?view=reportsView&report=maintenanceDue"
              class="report-tab-btn <?php echo $activeReport === 'maintenanceDue' ? 'active' : ''; ?>">
              Report 4: Maintenance Overdue
            </a>
            <a href="dashboard.php?view=reportsView&report=driverPerformance"
              class="report-tab-btn <?php echo $activeReport === 'driverPerformance' ? 'active' : ''; ?>">
              Report 5: Driver Performance
            </a>
          </div>

          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">

            <?php if ($activeReport === 'frequentRoutes'): ?>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:8px;">
                <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white); margin:0;">Report 1: Top Frequently Used Routes (By Booking Volume)</h2>
                <a href="export_report.php?report=frequentRoutes&autoPrint=1" target="_blank" style="font-size:0.8rem; color:#38BDF8; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-weight:600;">
                  Download PDF ↗
                </a>
              </div>
              <div style="overflow-x:auto;">
                <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                        ID</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                        NAME</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        ORIGIN & DESTINATION</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TOTAL
                        BOOKINGS</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TOTAL
                        REVENUE</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reportData as $row): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #RT-<?php echo htmlspecialchars($row['routeid'] ?? $row['ROUTEID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['routename'] ?? $row['ROUTENAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($row['origin'] ?? $row['ORIGIN']); ?> →
                          <?php echo htmlspecialchars($row['destination'] ?? $row['DESTINATION']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:700; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['totalbookings'] ?? $row['TOTALBOOKINGS']); ?> Rides</td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:#34D399;">LKR
                          <?php echo number_format((float) ($row['totalrevenue'] ?? $row['TOTALREVENUE']), 2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            <?php elseif ($activeReport === 'periodRevenue'): ?>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:8px;">
                <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white); margin:0;">Report 2: Total Revenue Generated By Payment Channel</h2>
                <a href="export_report.php?report=periodRevenue&autoPrint=1" target="_blank" style="font-size:0.8rem; color:#38BDF8; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-weight:600;">
                  Download PDF ↗
                </a>
              </div>
              <div style="overflow-x:auto;">
                <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        PAYMENT METHOD</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        SUCCESSFUL TRANSACTIONS</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TOTAL
                        AMOUNT COLLECTED</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reportData as $row): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['paymentmethod'] ?? $row['PAYMENTMETHOD']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($row['transactioncount'] ?? $row['TRANSACTIONCOUNT']); ?> payments
                        </td>
                        <td style="padding:0.85rem 1rem; font-weight:700; color:#34D399;">LKR
                          <?php echo number_format((float) ($row['totalcollected'] ?? $row['TOTALCOLLECTED']), 2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            <?php elseif ($activeReport === 'maintenanceDue'): ?>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:8px;">
                <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white); margin:0;">Report 4: Fleet Preventive Maintenance Alerts &amp; Scheduling</h2>
                <a href="export_report.php?report=maintenanceDue&autoPrint=1" target="_blank" style="font-size:0.8rem; color:#38BDF8; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-weight:600;">
                  Download PDF ↗
                </a>
              </div>
              <div style="overflow-x:auto;">
                <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        VEHICLE REG NO</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        CATEGORY & MODEL</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        SERVICE ACTION</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">NEXT
                        SERVICE DATE</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        URGENCY STATUS</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reportData as $row): ?>
                      <?php
                      $urg = strtoupper($row['urgencystatus'] ?? $row['URGENCYSTATUS'] ?? '');
                      $bClass = ($urg === 'URGENT OVERDUE') ? 'badge-urgent' : (($urg === 'DUE IN 7 DAYS') ? 'badge-warning' : 'badge-active');
                      ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; font-weight:700; color:var(--accent-blue);">
                          <?php echo htmlspecialchars($row['registrationno'] ?? $row['REGISTRATIONNO']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['vehicletype'] ?? $row['VEHICLETYPE']); ?> -
                          <?php echo htmlspecialchars($row['model'] ?? $row['MODEL']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($row['maintenancetype'] ?? $row['MAINTENANCETYPE']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['nextservicedate'] ?? $row['NEXTSERVICEDATE']); ?></td>
                        <td style="padding:0.85rem 1rem;">
                          <span class="badge-status <?php echo $bClass; ?>"><?php echo htmlspecialchars($urg); ?></span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            <?php elseif ($activeReport === 'driverPerformance'): ?>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:8px;">
                <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white); margin:0;">Report 5: Driver Duty, Completed Trips &amp; Fuel Logs</h2>
                <a href="export_report.php?report=driverPerformance&autoPrint=1" target="_blank" style="font-size:0.8rem; color:#38BDF8; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-weight:600;">
                  Download PDF ↗
                </a>
              </div>
              <div style="overflow-x:auto;">
                <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        DRIVER NAME</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        LICENSE NO</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        COMPLETED TRIPS</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TOTAL
                        SHIFTS</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TOTAL
                        FUEL CONSUMED</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reportData as $row): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['drivername'] ?? $row['DRIVERNAME']); ?></td>
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($row['licenseno'] ?? $row['LICENSENO']); ?></td>
                        <td style="padding:0.85rem 1rem;"><span
                            class="badge-status badge-info"><?php echo htmlspecialchars($row['completedtrips'] ?? $row['COMPLETEDTRIPS']); ?>
                            Trips</span></td>
                        <td style="padding:0.85rem 1rem;"><span
                            class="badge-status badge-active"><?php echo htmlspecialchars($row['totalshifts'] ?? $row['TOTALSHIFTS']); ?>
                            Shifts</span></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:#FBBF24;">
                          <?php echo number_format((float) ($row['totalfuellogged'] ?? $row['TOTALFUELLOGGED']), 1); ?>
                          Liters</td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            <?php else: ?>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:8px;">
                <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white); margin:0;">Report 3: Passenger Travel History Statement</h2>
                <a href="export_report.php?report=passengerHistory&autoPrint=1" target="_blank" style="font-size:0.8rem; color:#38BDF8; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-weight:600;">
                  Download PDF ↗
                </a>
              </div>
              <div style="overflow-x:auto;">
                <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        BOOKING ID</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                        PASSENGER</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">DATE
                        & TIME</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                      </th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">SEAT
                        NO</th>
                      <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">FARE
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reportData as $row): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #BK-<?php echo htmlspecialchars($row['bookingid'] ?? $row['BOOKINGID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['passengername'] ?? $row['PASSENGERNAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($row['tripdate'] ?? $row['TRIPDATE']); ?>
                          (<?php echo htmlspecialchars($row['departuretime'] ?? $row['DEPARTURETIME']); ?>)</td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($row['routename'] ?? $row['ROUTENAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">Seat
                          <?php echo htmlspecialchars($row['seatnumber'] ?? $row['SEATNUMBER']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:#34D399;">LKR
                          <?php echo number_format((float) ($row['ticketprice'] ?? $row['TICKETPRICE']), 2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>

          </div>
        </section>
