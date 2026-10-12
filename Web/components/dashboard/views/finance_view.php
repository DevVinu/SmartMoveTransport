        <!-- Finance view -->
        <section class="page-view <?php echo $activeView === 'financeView' ? 'active-view' : ''; ?>" id="financeView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Financial Auditing & Payment Ledger</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Real-time settlement
                records, payment channel breakdown, and promotional discounts</p>
            </div>
          </div>

          <!-- KPI cards -->
          <div class="stats-grid"
            style="display:grid; grid-template-columns: repeat(3, 1fr); gap:16px; margin-bottom:1.5rem;">
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Total Settled Revenue</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23" />
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">LKR <?php echo number_format($totalRevenue, 2); ?></div>
              <div class="stat-subtext">Verified digital receipts & settlements</div>
            </div>
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Recorded Transactions</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="20" height="14" x="2" y="5" rx="2" />
                    <line x1="2" y1="10" x2="22" y2="10" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">+<?php echo number_format(count($paymentsList)); ?></div>
              <div class="stat-subtext">Settled across Card, Cash & Wallets</div>
            </div>
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Active Promo Codes</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z" />
                    <circle cx="7" cy="7" r=".5" fill="currentColor" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">3 Active</div>
              <div class="stat-subtext">WELCOME10, SPEED20, GREEN50</div>
            </div>
          </div>

          <!-- Payments table -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
            <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Verified
              Payment Transactions & Settlements</h2>
            <div style="overflow-x:auto;">
              <table class="tasks-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      PAYMENT ID</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      BOOKING REF</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      PASSENGER</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      AMOUNT</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      METHOD</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">DATE
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      STATUS</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($paymentsList)): ?>
                    <tr>
                      <td colspan="8" style="padding:2rem; text-align:center; color:var(--text-muted);">No payment records
                        found.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($paymentsList as $p): ?>
                      <tr style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #PAY-<?php echo htmlspecialchars($p['paymentid'] ?? $p['PAYMENTID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--text-secondary);">
                          #BK-<?php echo htmlspecialchars($p['bookingid'] ?? $p['BOOKINGID']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                          <?php echo htmlspecialchars($p['passengername'] ?? $p['PASSENGERNAME']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($p['routename'] ?? $p['ROUTENAME']); ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:700; color:#34D399;">LKR
                          <?php echo number_format((float) ($p['amountpaid'] ?? $p['AMOUNTPAID']), 2); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($p['paymentmethod'] ?? $p['PAYMENTMETHOD']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-muted); font-size:0.8rem;">
                          <?php echo htmlspecialchars($p['paymentdate'] ?? $p['PAYMENTDATE']); ?></td>
                        <td style="padding:0.85rem 1rem;">
                          <span
                            class="badge-status <?php echo ($p['paymentstatus'] ?? $p['PAYMENTSTATUS']) === 'PAID' ? 'badge-active' : 'badge-warning'; ?>">
                            <?php echo htmlspecialchars($p['paymentstatus'] ?? $p['PAYMENTSTATUS']); ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>
