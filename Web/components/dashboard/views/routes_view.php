<!-- Routes view -->
<section class="page-view <?php echo $activeView === 'routesView' ? 'active-view' : ''; ?>" id="routesView">
  <div class="view-header">
    <div class="view-title-group">
      <h1>Transit Routes & Trip Scheduling</h1>
      <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Define intercity route
        corridors and manage daily dispatch timetables</p>
    </div>
    <div class="view-actions">
      <button class="btn-primary" type="button" onclick="openModal('modalAddRoute')">
        + Establish New Route
      </button>
    </div>
  </div>

  <!-- Routes table -->
  <div
    style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem; margin-bottom:1.5rem;">
    <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Active Transit
      Corridors (<code>route</code>)</h2>
    <div style="overflow-x:auto;">
      <table class="tasks-table" style="width:100%; border-collapse:collapse;">
        <thead>
          <tr>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
              ID</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
              NAME</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
              CORRIDOR</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
              DISTANCE</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
              DURATION</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">BASE
              FARE</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TYPE
            </th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($routesList)): ?>
            <tr>
              <td colspan="7" style="padding:2rem; text-align:center; color:var(--text-muted);">No transit routes
                configured yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($routesList as $rt): ?>
              <tr style="border-bottom:1px solid var(--border-muted);">
                <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                  #RT-<?php echo htmlspecialchars($rt['routeid'] ?? $rt['ROUTEID']); ?></td>
                <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                  <?php echo htmlspecialchars($rt['routename'] ?? $rt['ROUTENAME']); ?>
                </td>
                <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                  <?php echo htmlspecialchars($rt['origin'] ?? $rt['ORIGIN']); ?> →
                  <?php echo htmlspecialchars($rt['destination'] ?? $rt['DESTINATION']); ?>
                </td>
                <td style="padding:0.85rem 1rem; color:var(--text-white);">
                  <?php echo htmlspecialchars($rt['distancekm'] ?? $rt['DISTANCEKM']); ?> km
                </td>
                <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                  <?php echo htmlspecialchars($rt['estimateddurationminutes'] ?? $rt['ESTIMATEDDURATIONMINUTES']); ?>
                  mins
                </td>
                <td style="padding:0.85rem 1rem; font-weight:600; color:#34D399;">LKR
                  <?php echo number_format((float) ($rt['basefare'] ?? $rt['BASEFARE']), 2); ?>
                </td>
                <td style="padding:0.85rem 1rem;">
                  <span class="badge-status badge-info">
                    <?php echo htmlspecialchars($rt['routetype'] ?? $rt['ROUTETYPE'] ?? 'EXPRESSWAY'); ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Scheduled trips -->
  <div
    style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
    <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Daily Dispatch
      Timetable & Journey Logs</h2>
    <div style="overflow-x:auto;">
      <table class="tasks-table" style="width:100%; border-collapse:collapse;">
        <thead>
          <tr>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TRIP
              ID</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE
            </th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
              VEHICLE</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">TRIP
              DATE</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">DEP →
              ARR</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">OPEN
              SEATS</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
              STATUS</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($scheduledTripsList)): ?>
            <tr>
              <td colspan="7" style="padding:2rem; text-align:center; color:var(--text-muted);">No scheduled trips
                recorded yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($scheduledTripsList as $st): ?>
              <tr style="border-bottom:1px solid var(--border-muted);">
                <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                  #TRP-<?php echo htmlspecialchars($st['tripid'] ?? $st['TRIPID']); ?></td>
                <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                  <?php echo htmlspecialchars($st['routename'] ?? $st['ROUTENAME']); ?>
                </td>
                <td style="padding:0.85rem 1rem; color:var(--text-white);">
                  <?php echo htmlspecialchars($st['registrationno'] ?? $st['REGISTRATIONNO']); ?>
                  (<?php echo htmlspecialchars($st['vehicletype'] ?? $st['VEHICLETYPE']); ?>)
                </td>
                <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                  <?php echo htmlspecialchars($st['tripdate'] ?? $st['TRIPDATE']); ?>
                </td>
                <td style="padding:0.85rem 1rem; color:var(--text-white);">
                  <?php echo htmlspecialchars($st['departuretime'] ?? $st['DEPARTURETIME']); ?> →
                  <?php echo htmlspecialchars($st['arrivaltime'] ?? $st['ARRIVALTIME']); ?>
                </td>
                <td style="padding:0.85rem 1rem;"><span
                    class="badge-status badge-info"><?php echo htmlspecialchars($st['availableseats'] ?? $st['AVAILABLESEATS']); ?>
                    Seats</span></td>
                <td style="padding:0.85rem 1rem;">
                  <span
                    class="badge-status <?php echo ($st['tripstatus'] ?? $st['TRIPSTATUS']) === 'COMPLETED' ? 'badge-active' : 'badge-warning'; ?>">
                    <?php echo htmlspecialchars($st['tripstatus'] ?? $st['TRIPSTATUS']); ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Intermediate Route Stops -->
  <div
    style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem; margin-top:1.5rem;">
    <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Intermediate Route
      Transit Stops (<code>routeStop</code> &amp; <code>stop</code>)</h2>
    <div style="overflow-x:auto;">
      <table class="tasks-table" style="width:100%; border-collapse:collapse;">
        <thead>
          <tr>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROUTE</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ORDER</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">STATION /
              INTERCHANGE NAME</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">PHYSICAL
              ADDRESS / LANDMARK</th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ARR OFFSET
            </th>
            <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">DEP OFFSET
            </th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($routeStopsList)): ?>
            <tr>
              <td colspan="6" style="padding:2rem; text-align:center; color:var(--text-muted);">No intermediate stops
                recorded.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($routeStopsList as $rs): ?>
              <tr style="border-bottom:1px solid var(--border-muted);">
                <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                  #RT-<?php echo htmlspecialchars($rs['routeId'] ?? $rs['routeid'] ?? ''); ?>
                  (<?php echo htmlspecialchars($rs['routeName'] ?? $rs['routename'] ?? ''); ?>)
                </td>
                <td style="padding:0.85rem 1rem;">
                  <span
                    style="background:var(--bg-card-inner); padding:0.25rem 0.5rem; border-radius:4px; font-weight:700; color:var(--accent-blue);">
                    Stop #<?php echo htmlspecialchars($rs['stopOrder'] ?? $rs['stoporder'] ?? 1); ?>
                  </span>
                </td>
                <td style="padding:0.85rem 1rem; font-weight:600; color:var(--text-white);">
                  <?php echo htmlspecialchars($rs['stopName'] ?? $rs['stopname'] ?? ''); ?>
                </td>
                <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                  <?php echo htmlspecialchars($rs['locationAddress'] ?? $rs['locationaddress'] ?? '-'); ?>
                </td>
                <td style="padding:0.85rem 1rem; color:#34D399; font-weight:600;">
                  +<?php echo htmlspecialchars($rs['arrivalTimeOffset'] ?? $rs['arrivaltimeoffset'] ?? 0); ?> mins
                </td>
                <td style="padding:0.85rem 1rem; color:#38BDF8; font-weight:600;">
                  +<?php echo htmlspecialchars($rs['departureTimeOffset'] ?? $rs['departuretimeoffset'] ?? 0); ?> mins
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>