        <!-- Overview view -->
        <section class="page-view <?php echo $activeView === 'dashboardView' ? 'active-view' : ''; ?>"
          id="dashboardView">
          <div class="view-header" style="margin-bottom:1.25rem;">
            <div class="view-title-group">
              <h1 style="font-size:2rem; font-weight:700; color:var(--text-white); letter-spacing:-0.03em;">Dashboard
                Overview</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">High-level transit
                operational metrics, fleet revenue, and recent booking activity</p>
            </div>
          </div>

          <?php include __DIR__ . "/../kpi_cards.php"; ?>


          <!-- Recent sales -->
          <div class="dash-panel"
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:24px; margin-bottom:1.5rem; width:100%;">
            <div class="panel-header" style="margin-bottom:20px;">
              <div class="panel-title" style="font-size:16px; font-weight:600; color:var(--text-white);">Recent Sales &
                Passenger Bookings</div>
              <div class="panel-subtitle" style="font-size:12.5px; color:var(--text-muted); margin-top:3px;">
                Passenger ticket transactions and revenue settlements recorded across all corridors.
              </div>
            </div>
            <div class="sales-list" style="display:flex; flex-direction:column; gap:18px;">
              <?php
              $demoSales = [
                ['name' => 'Olivia Martin', 'email' => 'olivia.martin@email.com', 'amount' => '+$1,999.00', 'initials' => 'OM'],
                ['name' => 'Jackson Lee', 'email' => 'jackson.lee@email.com', 'amount' => '+$39.00', 'initials' => 'JL'],
                ['name' => 'Isabella Nguyen', 'email' => 'isabella.nguyen@email.com', 'amount' => '+$299.00', 'initials' => 'IN'],
                ['name' => 'William Kim', 'email' => 'will@email.com', 'amount' => '+$99.00', 'initials' => 'WK'],
                ['name' => 'Sofia Davis', 'email' => 'sofia.davis@email.com', 'amount' => '+$39.00', 'initials' => 'SD'],
              ];

              $displaySales = [];
              if (!empty($recentBookings)) {
                foreach (array_slice($recentBookings, 0, 5) as $rb) {
                  $pName = trim($rb['passengername'] ?? $rb['PASSENGERNAME'] ?? 'Passenger');
                  $parts = explode(' ', $pName);
                  $ini = strtoupper(substr($parts[0] ?? 'P', 0, 1) . substr($parts[1] ?? 'C', 0, 1));
                  $displaySales[] = [
                    'name' => $pName,
                    'email' => strtolower(str_replace(' ', '.', $pName)) . '@email.com',
                    'amount' => '+LKR ' . number_format((float) ($rb['ticketprice'] ?? $rb['TICKETPRICE'] ?? 400), 2),
                    'initials' => $ini ?: 'BK'
                  ];
                }
              } else {
                $displaySales = $demoSales;
              }

              foreach ($displaySales as $s):
                ?>
                <div class="sale-item" style="display:flex; align-items:center; justify-content:space-between;">
                  <div class="sale-customer" style="display:flex; align-items:center; gap:12px;">
                    <div class="sale-avatar"
                      style="width:36px; height:36px; border-radius:50%; background:var(--bg-muted); color:var(--text-white); font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:center; border:1px solid var(--border-subtle);">
                      <?php echo htmlspecialchars($s['initials']); ?>
                    </div>
                    <div class="sale-info" style="display:flex; flex-direction:column;">
                      <span class="sale-name"
                        style="font-size:13.5px; font-weight:500; color:var(--text-primary);"><?php echo htmlspecialchars($s['name']); ?></span>
                      <span class="sale-email"
                        style="font-size:12px; color:var(--text-muted);"><?php echo htmlspecialchars($s['email']); ?></span>
                    </div>
                  </div>
                  <span class="sale-amount"
                    style="font-size:14px; font-weight:600; color:var(--text-white);"><?php echo htmlspecialchars($s['amount']); ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Reservations table -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem; margin-top:1.5rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
              <div>
                <h2 style="font-size:1.1rem; font-weight:600; color:var(--text-white);">Recent Passenger Reservations
                </h2>
                <p style="color:var(--text-muted); font-size:0.8rem;">Recent commuter ticket reservations and booking
                  check-ins</p>
              </div>
              <span class="badge-status badge-active">CONFIRMED</span>
            </div>

            <div style="overflow-x:auto;">
              <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      BOOKING ID</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      PASSENGER</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">SEAT
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">FARE
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      STATUS</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      PAYMENT</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($recentBookings)): ?>
                    <tr>
                      <td colspan="7" style="padding:2rem; text-align:center; color:var(--text-muted);">
                        No passenger reservations recorded yet.
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($recentBookings as $b): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #BK-<?php echo htmlspecialchars($b['bookingid'] ?? $b['BOOKINGID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($b['passengername'] ?? $b['PASSENGERNAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($b['routename'] ?? $b['ROUTENAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">Seat
                          <?php echo htmlspecialchars($b['seatnumber'] ?? $b['SEATNUMBER']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:#34D399;">LKR
                          <?php echo number_format((float) ($b['ticketprice'] ?? $b['TICKETPRICE']), 2); ?></td>
                        <td style="padding:0.85rem 1rem;">
                          <span
                            class="badge-status <?php echo ($b['bookingstatus'] ?? $b['BOOKINGSTATUS']) === 'CONFIRMED' ? 'badge-active' : 'badge-warning'; ?>">
                            <?php echo htmlspecialchars($b['bookingstatus'] ?? $b['BOOKINGSTATUS']); ?>
                          </span>
                        </td>
                        <td style="padding:0.85rem 1rem; color:var(--text-muted); font-size:0.8rem;">
                          <?php echo htmlspecialchars($b['paymentmethod'] ?? $b['PAYMENTMETHOD'] ?? 'CARD'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>
