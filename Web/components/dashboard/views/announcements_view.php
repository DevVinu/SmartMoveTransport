        <!-- Announcements view -->
        <section class="page-view <?php echo $activeView === 'announcementsView' ? 'active-view' : ''; ?>"
          id="announcementsView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Travel Announcements & Alerts</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">
                Real-time expressway alerts, safety advisories, and weather notices broadcast to all transit corridors
              </p>
            </div>
            <div class="view-actions">
              <button class="btn-primary" type="button" onclick="openModal('modalBroadcastAdvisory')">
                + Broadcast New Advisory
              </button>
            </div>
          </div>

          <!-- KPI metrics -->
          <div class="stats-grid"
            style="display:grid; grid-template-columns: repeat(3, 1fr); gap:16px; margin-bottom:1.5rem;">
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Active Advisories</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                  </svg>
                </span>
              </div>
              <div class="stat-value"><?php echo count($mongoAnnouncements); ?></div>
              <div class="stat-subtext">Broadcasting across commuter channels</div>
            </div>
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">Broadcast Network</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="2" y1="12" x2="22" y2="12" />
                    <path
                      d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">All Corridors</div>
              <div class="stat-subtext">Expressway, web & passenger mobile apps</div>
            </div>
            <div class="stat-card">
              <div class="stat-header-row">
                <span class="stat-label">High Severity Notices</span>
                <span class="stat-icon">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                  </svg>
                </span>
              </div>
              <div class="stat-value">
                <?php
                $highPrio = 0;
                foreach ($mongoAnnouncements as $annItem) {
                  $arr = (array) $annItem;
                  if (strtoupper($arr['priority'] ?? '') === 'HIGH')
                    $highPrio++;
                }
                echo $highPrio;
                ?>
              </div>
              <div class="stat-subtext">Expressway hazard & detour alerts</div>
            </div>
          </div>

          <!-- Announcements list -->
          <div style="display:flex; flex-direction:column; gap:1.25rem;">
            <?php if (empty($mongoAnnouncements)): ?>
              <div
                style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:3rem; text-align:center;">

                <h3 style="color:var(--text-white); font-size:1.1rem; margin-bottom:0.5rem;">No Active Travel Advisories
                </h3>
                <p style="color:var(--text-secondary); font-size:0.85rem;">All intercity routes and highway corridors are
                  currently running normally.</p>
              </div>
            <?php else: ?>
              <?php foreach ($mongoAnnouncements as $ann): ?>
                <?php
                $a = (array) $ann;
                $cat = strtoupper($a['category'] ?? 'ALERT');
                $prio = strtoupper($a['priority'] ?? 'MEDIUM');

                $borderCol = 'rgba(59, 130, 246, 0.35)';
                $badgeBg = 'badge-info';
                $icon = '';
                if ($prio === 'HIGH' || strpos($cat, 'EXPRESSWAY') !== false) {
                  $borderCol = 'rgba(239, 68, 68, 0.45)';
                  $badgeBg = 'badge-urgent';
                  $icon = '';
                } elseif (strpos($cat, 'ROADWORK') !== false) {
                  $borderCol = 'rgba(245, 158, 11, 0.45)';
                  $badgeBg = 'badge-warning';
                  $icon = '';
                } elseif (strpos($cat, 'COMMUNITY') !== false) {
                  $borderCol = 'rgba(16, 185, 129, 0.45)';
                  $badgeBg = 'badge-active';
                  $icon = '';
                }
                ?>
                <div
                  style="background:var(--bg-card); border:1px solid <?php echo $borderCol; ?>; border-radius:var(--radius-lg); padding:1.5rem;">
                  <div
                    style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem; gap:1rem; flex-wrap:wrap;">
                    <div style="display:flex; align-items:flex-start; gap:0.9rem;">
                      <div style="font-size:1.6rem; line-height:1;"><?php echo $icon; ?></div>
                      <div>
                        <h3 style="color:var(--text-white); font-size:1.05rem; font-weight:600; margin:0;">
                          <?php echo htmlspecialchars($a['title'] ?? 'Notice'); ?>
                        </h3>
                        <div
                          style="color:var(--text-muted); font-size:0.75rem; margin-top:0.35rem; display:flex; gap:0.75rem; align-items:center;">
                          <span>Notice ID: <code
                              style="color:#93C5FD;"><?php echo htmlspecialchars($a['announcementId'] ?? 'N/A'); ?></code></span>
                          <?php if (!empty($a['publishedAt'])): ?>
                            <span>• Published: <?php echo htmlspecialchars($a['publishedAt']); ?></span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                    <div style="display:flex; gap:0.5rem; align-items:center;">
                      <span class="badge-status <?php echo $badgeBg; ?>"><?php echo htmlspecialchars($cat); ?></span>
                      <span
                        class="badge-status <?php echo ($prio === 'HIGH') ? 'badge-urgent' : 'badge-neutral'; ?>"><?php echo htmlspecialchars($prio); ?>
                        PRIORITY</span>

                      <form method="POST" action="dashboard.php" style="margin-left:0.5rem;"
                        onsubmit="return confirm('Dismiss this travel advisory from MongoDB?');">
                        <input type="hidden" name="action" value="deleteAnnouncement">
                        <input type="hidden" name="announcementId"
                          value="<?php echo htmlspecialchars($a['announcementId'] ?? ''); ?>">
                        <button type="submit" class="action-btn-sm action-btn-danger" title="Dismiss Advisory">✕
                          Dismiss</button>
                      </form>
                    </div>
                  </div>

                  <p
                    style="color:var(--text-secondary); font-size:0.9rem; line-height:1.6; margin-bottom:1.25rem; padding-left:2.5rem;">
                    <?php echo htmlspecialchars($a['message'] ?? ''); ?>
                  </p>

                  <?php if (!empty($a['affectedRoutes'])): ?>
                    <div
                      style="display:flex; align-items:center; gap:0.6rem; padding-left:2.5rem; font-size:0.8rem; color:var(--text-muted); flex-wrap:wrap;">
                      <strong>Affected Route IDs:</strong>
                      <?php foreach ((array) $a['affectedRoutes'] as $rid): ?>
                        <span
                          style="background:var(--bg-canvas); border:1px solid var(--border-subtle); padding:0.25rem 0.65rem; border-radius:var(--radius-sm); color:#93C5FD; font-weight:500;">
                          Route #<?php echo htmlspecialchars($rid); ?>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>
