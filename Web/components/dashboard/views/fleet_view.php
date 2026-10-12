        <!-- Fleet view -->
        <section class="page-view <?php echo $activeView === 'fleetView' ? 'active-view' : ''; ?>" id="fleetView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Fleet & Vehicle Inventory</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Live fleet inventory,
                passenger capacity, and vehicle service readiness</p>
            </div>
            <div class="view-actions">
              <button class="btn-primary" type="button" onclick="openModal('modalAddVehicle')">
                + Register New Vehicle
              </button>
            </div>
          </div>

          <!-- Filters -->
          <div class="filter-pills-bar">
            <span class="filter-pill active" onclick="filterFleet('ALL', this)">All Fleet Units</span>
            <span class="filter-pill" onclick="filterFleet('BUS', this)">Buses</span>
            <span class="filter-pill" onclick="filterFleet('VAN', this)">Vans</span>
            <span class="filter-pill" onclick="filterFleet('CAR', this)">Luxury Cars</span>
            <span class="filter-pill" onclick="filterFleet('THREEWHEEL', this)">Three-Wheelers</span>
            <span class="filter-pill" onclick="filterFleet('BIKE', this)">Motorbikes</span>
          </div>

          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
            <div style="overflow-x:auto;">
              <table class="tasks-table" id="fleetTable" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      VEHICLE ID</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      REGISTRATION NO</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      CATEGORY</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">MODEL
                      & MANUFACTURER</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      CAPACITY</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      STATUS</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($fleetList)): ?>
                    <tr>
                      <td colspan="7" style="padding:2rem; text-align:center; color:var(--text-muted);">No fleet vehicles
                        registered yet.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($fleetList as $v): ?>
                      <?php
                      $vType = strtoupper($v['vehicletype'] ?? $v['VEHICLETYPE']);
                      $vStatus = strtoupper($v['status'] ?? $v['STATUS']);
                      $vId = (int) ($v['vehicleid'] ?? $v['VEHICLEID']);
                      ?>
                      <tr class="fleet-row" data-type="<?php echo $vType; ?>"
                        style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #VH-<?php echo $vId; ?></td>
                        <td style="padding:0.85rem 1rem; font-weight:700; color:var(--text-white);">
                          <?php echo htmlspecialchars($v['registrationno'] ?? $v['REGISTRATIONNO']); ?></td>
                        <td style="padding:0.85rem 1rem;"><span class="badge-status badge-info"><?php echo $vType; ?></span>
                        </td>
                        <td style="padding:0.85rem 1rem; color:var(--text-white);">
                          <?php echo htmlspecialchars($v['model'] ?? $v['MODEL']); ?>
                          (<?php echo htmlspecialchars($v['manufacturer'] ?? $v['MANUFACTURER']); ?>)</td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($v['capacity'] ?? $v['CAPACITY']); ?> Seats</td>
                        <td style="padding:0.85rem 1rem;">
                          <span
                            class="badge-status <?php echo $vStatus === 'AVAILABLE' ? 'badge-active' : ($vStatus === 'MAINTENANCE' ? 'badge-urgent' : 'badge-warning'); ?>">
                            <?php echo $vStatus; ?>
                          </span>
                        </td>
                        <td style="padding:0.85rem 1rem;">
                          <form method="POST" action="dashboard.php" style="display:inline;">
                            <input type="hidden" name="action" value="toggleVehicleStatus">
                            <input type="hidden" name="vehicleId" value="<?php echo $vId; ?>">
                            <?php if ($vStatus === 'AVAILABLE'): ?>
                              <input type="hidden" name="newStatus" value="MAINTENANCE">
                              <button type="submit" class="action-btn-sm action-btn-danger" title="Send to Maintenance">Set
                                Maintenance</button>
                            <?php else: ?>
                              <input type="hidden" name="newStatus" value="AVAILABLE">
                              <button type="submit" class="action-btn-sm" style="color:#34D399;" title="Mark Available">Set
                                Available</button>
                            <?php endif; ?>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>
