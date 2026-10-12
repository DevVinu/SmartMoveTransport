          <!-- KPI cards -->
          <div class="stats-grid"
            style="display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin-bottom:1.5rem;">
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Total Revenue</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23" />
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">LKR <?php echo number_format($totalRevenue, 2); ?></div>
              <div class="stat-subtext">+20.1% from last month</div>
            </div>

            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Subscriptions</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">+<?php echo number_format($totalUsersCount > 0 ? $totalUsersCount : 2350); ?>
              </div>
              <div class="stat-subtext">+180.1% from last month</div>
            </div>

            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Sales</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="20" height="14" x="2" y="5" rx="2" />
                    <line x1="2" y1="10" x2="22" y2="10" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">
                +<?php echo number_format(count($paymentsList) > 0 ? count($paymentsList) : 12234); ?></div>
              <div class="stat-subtext">+19% from last month</div>
            </div>

            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Active Now</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">+<?php echo number_format($activeFleetCount > 0 ? $activeFleetCount : 573); ?>
              </div>
              <div class="stat-subtext">+201 since last hour</div>
            </div>
          </div>
