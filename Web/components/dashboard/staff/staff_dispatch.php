<?php
// Staff / Dispatcher Live Dispatch Queue Component
?>
<div class="chart-card-box" style="flex: 2;">
  
  <div class="chart-card-header">
    <div class="chart-title-area">
      <h2 class="chart-title">Live Dispatch Queue & Active Trips</h2>
      <div class="chart-num-row">
        <span class="chart-big-num">12</span>
        <span class="chart-badge">Queue Active</span>
      </div>
      <div class="chart-sub-label">Real-time driver matching & status monitor</div>
    </div>

    <div style="display: flex; gap: 0.6rem;">
      <button class="btn-new-view" style="padding: 0.4rem 0.9rem; font-size: 0.78rem;" onclick="alert('Filtering unassigned requests');">Unassigned (4)</button>
      <button class="btn-create-report" style="padding: 0.4rem 0.95rem; font-size: 0.78rem;" onclick="alert('Manual dispatch window opened');">+ Manual Dispatch</button>
    </div>
  </div>

  <div style="overflow-x: auto; margin-top: 0.5rem;">
    <table class="dash-table" style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr>
          <th style="padding: 0.75rem 0.8rem; font-size: 0.74rem; color: var(--dash-text-dim); text-align: left; border-bottom: 1px solid var(--dash-border-subtle);">TRIP ID</th>
          <th style="padding: 0.75rem 0.8rem; font-size: 0.74rem; color: var(--dash-text-dim); text-align: left; border-bottom: 1px solid var(--dash-border-subtle);">PASSENGER</th>
          <th style="padding: 0.75rem 0.8rem; font-size: 0.74rem; color: var(--dash-text-dim); text-align: left; border-bottom: 1px solid var(--dash-border-subtle);">CATEGORY</th>
          <th style="padding: 0.75rem 0.8rem; font-size: 0.74rem; color: var(--dash-text-dim); text-align: left; border-bottom: 1px solid var(--dash-border-subtle);">ASSIGNED DRIVER</th>
          <th style="padding: 0.75rem 0.8rem; font-size: 0.74rem; color: var(--dash-text-dim); text-align: left; border-bottom: 1px solid var(--dash-border-subtle);">STATUS</th>
          <th style="padding: 0.75rem 0.8rem; font-size: 0.74rem; color: var(--dash-text-dim); text-align: left; border-bottom: 1px solid var(--dash-border-subtle);">ACTION</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="padding: 0.9rem 0.8rem; font-family: monospace; color: var(--dash-blue); font-weight: 700; border-bottom: 1px solid var(--dash-border);">#TR-8021</td>
          <td style="padding: 0.9rem 0.8rem; font-weight: 600; color: #FFFFFF; border-bottom: 1px solid var(--dash-border);">Nimal Perera</td>
          <td style="padding: 0.9rem 0.8rem; color: var(--dash-text-muted); border-bottom: 1px solid var(--dash-border);">Executive Sedan</td>
          <td style="padding: 0.9rem 0.8rem; color: #FFFFFF; border-bottom: 1px solid var(--dash-border);">Kamal W. (Car #104)</td>
          <td style="padding: 0.9rem 0.8rem; border-bottom: 1px solid var(--dash-border);">
            <span class="sidebar-badge-new" style="background-color: rgba(59, 130, 246, 0.15); color: #60A5FA;">In Transit</span>
          </td>
          <td style="padding: 0.9rem 0.8rem; border-bottom: 1px solid var(--dash-border);">
            <button class="btn-new-view" style="padding: 0.3rem 0.7rem; font-size: 0.75rem;" onclick="alert('Viewing Telematics #TR-8021');">Track GPS</button>
          </td>
        </tr>

        <tr>
          <td style="padding: 0.9rem 0.8rem; font-family: monospace; color: var(--dash-blue); font-weight: 700; border-bottom: 1px solid var(--dash-border);">#TR-8022</td>
          <td style="padding: 0.9rem 0.8rem; font-weight: 600; color: #FFFFFF; border-bottom: 1px solid var(--dash-border);">Cinnamon Hotel VIP</td>
          <td style="padding: 0.9rem 0.8rem; color: var(--dash-text-muted); border-bottom: 1px solid var(--dash-border);">Chauffeur Van</td>
          <td style="padding: 0.9rem 0.8rem; color: #FFFFFF; border-bottom: 1px solid var(--dash-border);">Saman D. (Van #208)</td>
          <td style="padding: 0.9rem 0.8rem; border-bottom: 1px solid var(--dash-border);">
            <span class="sidebar-badge-new" style="background-color: rgba(59, 130, 246, 0.15); color: #60A5FA;">Boarding</span>
          </td>
          <td style="padding: 0.9rem 0.8rem; border-bottom: 1px solid var(--dash-border);">
            <button class="btn-new-view" style="padding: 0.3rem 0.7rem; font-size: 0.75rem;" onclick="alert('Viewing Telematics #TR-8022');">Track GPS</button>
          </td>
        </tr>

        <tr>
          <td style="padding: 0.9rem 0.8rem; font-family: monospace; color: var(--dash-blue); font-weight: 700; border-bottom: 1px solid var(--dash-border);">#TR-8023</td>
          <td style="padding: 0.9rem 0.8rem; font-weight: 600; color: #FFFFFF; border-bottom: 1px solid var(--dash-border);">Dilshan Fernando</td>
          <td style="padding: 0.9rem 0.8rem; color: var(--dash-text-muted); border-bottom: 1px solid var(--dash-border);">City Express Tuk</td>
          <td style="padding: 0.9rem 0.8rem; color: #EF4444; font-weight: 600; border-bottom: 1px solid var(--dash-border);">Pending Assignment</td>
          <td style="padding: 0.9rem 0.8rem; border-bottom: 1px solid var(--dash-border);">
            <span class="sidebar-badge-hot">Unassigned</span>
          </td>
          <td style="padding: 0.9rem 0.8rem; border-bottom: 1px solid var(--dash-border);">
            <button class="btn-create-report" style="padding: 0.3rem 0.7rem; font-size: 0.75rem;" onclick="alert('Driver Auto-Assigned for #TR-8023');">Assign Driver</button>
          </td>
        </tr>

        <tr>
          <td style="padding: 0.9rem 0.8rem; font-family: monospace; color: var(--dash-blue); font-weight: 700; border-bottom: 1px solid var(--dash-border);">#TR-8024</td>
          <td style="padding: 0.9rem 0.8rem; font-weight: 600; color: #FFFFFF; border-bottom: 1px solid var(--dash-border);">Colombo Medical Lab</td>
          <td style="padding: 0.9rem 0.8rem; color: var(--dash-text-muted); border-bottom: 1px solid var(--dash-border);">Express Courier</td>
          <td style="padding: 0.9rem 0.8rem; color: #FFFFFF; border-bottom: 1px solid var(--dash-border);">Nuwan K. (Bike #04)</td>
          <td style="padding: 0.9rem 0.8rem; border-bottom: 1px solid var(--dash-border);">
            <span class="sidebar-badge-new" style="background-color: rgba(59, 130, 246, 0.15); color: #60A5FA;">Delivering</span>
          </td>
          <td style="padding: 0.9rem 0.8rem; border-bottom: 1px solid var(--dash-border);">
            <button class="btn-new-view" style="padding: 0.3rem 0.7rem; font-size: 0.75rem;" onclick="alert('Viewing Telematics #TR-8024');">Track GPS</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

</div>
