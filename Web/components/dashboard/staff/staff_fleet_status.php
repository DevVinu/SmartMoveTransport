<?php
// Staff / Dispatcher Fleet Status Breakdown Component
?>
<div class="activity-card-box" style="flex: 1;">
  
  <div class="activity-header">
    <h2 class="activity-title">Fleet Allocation</h2>
    <span class="sidebar-badge-new" style="background-color: rgba(59, 130, 246, 0.15); color: #60A5FA;">142 / 160 Active</span>
  </div>

  <div style="display: flex; flex-direction: column; gap: 1.25rem;">
    
    <!-- Executive Sedans -->
    <div>
      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 0.35rem;">
        <span style="font-weight: 700; color: #FFFFFF;">Executive Sedans</span>
        <span style="color: var(--dash-blue); font-weight: 700;">48 / 50 (96%)</span>
      </div>
      <div style="background-color: var(--dash-color-black); border-radius: 999px; height: 6px; overflow: hidden; border: 1px solid var(--dash-border);">
        <div style="background-color: var(--dash-blue-primary); width: 96%; height: 100%;"></div>
      </div>
    </div>

    <!-- Chauffeur Vans -->
    <div>
      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 0.35rem;">
        <span style="font-weight: 700; color: #FFFFFF;">Chauffeur & Shuttle Vans</span>
        <span style="color: var(--dash-blue); font-weight: 700;">32 / 36 (88%)</span>
      </div>
      <div style="background-color: var(--dash-color-black); border-radius: 999px; height: 6px; overflow: hidden; border: 1px solid var(--dash-border);">
        <div style="background-color: var(--dash-blue); width: 88%; height: 100%;"></div>
      </div>
    </div>

    <!-- Intercity & Buses -->
    <div>
      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 0.35rem;">
        <span style="font-weight: 700; color: #FFFFFF;">Intercity City Buses</span>
        <span style="color: var(--dash-blue); font-weight: 700;">18 / 24 (75%)</span>
      </div>
      <div style="background-color: var(--dash-color-black); border-radius: 999px; height: 6px; overflow: hidden; border: 1px solid var(--dash-border);">
        <div style="background-color: #60A5FA; width: 75%; height: 100%;"></div>
      </div>
    </div>

    <!-- Express Motorbikes -->
    <div>
      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 0.35rem;">
        <span style="font-weight: 700; color: #FFFFFF;">Parcel Express Motorbikes</span>
        <span style="color: var(--dash-blue); font-weight: 700;">44 / 50 (88%)</span>
      </div>
      <div style="background-color: var(--dash-color-black); border-radius: 999px; height: 6px; overflow: hidden; border: 1px solid var(--dash-border);">
        <div style="background-color: #93C5FD; width: 88%; height: 100%;"></div>
      </div>
    </div>

    <!-- Depot Quick Stats -->
    <div style="background-color: var(--dash-color-black); border: 1px solid var(--dash-border); border-radius: 8px; padding: 0.9rem; margin-top: 0.4rem; display: flex; justify-content: space-between; text-align: center;">
      <div>
        <div style="font-size: 1.1rem; font-weight: 800; color: #FFFFFF;">99.2%</div>
        <div style="font-size: 0.72rem; color: var(--dash-text-dim);">On-Time SLA</div>
      </div>
      <div style="border-left: 1px solid var(--dash-border-subtle); border-right: 1px solid var(--dash-border-subtle); padding: 0 0.8rem;">
        <div style="font-size: 1.1rem; font-weight: 800; color: var(--dash-blue);">4.9 ★</div>
        <div style="font-size: 0.72rem; color: var(--dash-text-dim);">Fleet Rating</div>
      </div>
      <div>
        <div style="font-size: 1.1rem; font-weight: 800; color: #22C55E;">0</div>
        <div style="font-size: 0.72rem; color: var(--dash-text-dim);">Open Issues</div>
      </div>
    </div>

  </div>

</div>
