<?php
// Driver Partner Incoming Ride Requests Component
?>
<div class="chart-card-box" style="flex: 2;">
  
  <div class="chart-card-header">
    <div class="chart-title-area">
      <h2 class="chart-title">Available Trip Requests</h2>
      <div class="chart-num-row">
        <span class="chart-big-num">2</span>
        <span class="chart-badge">Within 3 km</span>
      </div>
      <div class="chart-sub-label">Dispatch radar matching rides near your location</div>
    </div>

    <div>
      <span class="sidebar-badge-new" style="background-color: rgba(34, 197, 94, 0.15); color: #22C55E; border: 1px solid rgba(34, 197, 94, 0.3);">🟢 Online & Ready</span>
    </div>
  </div>

  <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 0.5rem;" id="driverRequestsFeed">
    
    <!-- Request Card 1 -->
    <div style="background-color: var(--dash-color-black); border: 1px solid var(--dash-border); border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem;" id="reqCard1">
      <div style="display: flex; gap: 1rem; align-items: center;">
        <div class="user-avatar-circle" style="background-color: var(--dash-blue-primary); width: 44px; height: 44px; font-size: 1.1rem;">AS</div>
        <div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-weight: 800; color: #FFFFFF; font-size: 0.95rem;">Anura Samaraweera</span>
            <span style="font-size: 0.76rem; color: var(--dash-blue); font-weight: 700;">★ 4.95 VIP</span>
          </div>
          <div style="font-size: 0.82rem; color: var(--dash-text-muted); margin-top: 0.2rem;">
            📍 Galle Face Hotel → Bandaranaike Airport (CMB)
          </div>
          <div style="font-size: 0.74rem; color: var(--dash-text-dim); margin-top: 0.2rem;">
            Executive Sedan • 32 km • Pickup in 4 mins
          </div>
        </div>
      </div>

      <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
        <div style="font-size: 1.4rem; font-weight: 800; color: #FFFFFF;">$45.00</div>
        <div style="display: flex; gap: 0.5rem;">
          <button class="btn-new-view" style="padding: 0.4rem 0.8rem; font-size: 0.78rem;" onclick="document.getElementById('reqCard1').style.display='none';">Decline</button>
          <button class="btn-create-report" style="padding: 0.4rem 1rem; font-size: 0.78rem;" onclick="alert('🎉 Ride Accepted!\n\nPassenger: Anura Samaraweera\nRoute: Galle Face Hotel → CMB Airport\nEarnings: $45.00\n\nNavigation launched.'); document.getElementById('reqCard1').style.borderColor='#22C55E';">Accept Trip</button>
        </div>
      </div>
    </div>

    <!-- Request Card 2 -->
    <div style="background-color: var(--dash-color-black); border: 1px solid var(--dash-border); border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem;" id="reqCard2">
      <div style="display: flex; gap: 1rem; align-items: center;">
        <div class="user-avatar-circle" style="background-color: #242832; width: 44px; height: 44px; font-size: 1.1rem; color: #FFFFFF;">MK</div>
        <div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-weight: 800; color: #FFFFFF; font-size: 0.95rem;">Marcus Knight</span>
            <span style="font-size: 0.76rem; color: var(--dash-blue); font-weight: 700;">★ 4.88 Standard</span>
          </div>
          <div style="font-size: 0.82rem; color: var(--dash-text-muted); margin-top: 0.2rem;">
            📍 Colombo Fort Railway → Mount Lavinia Hotel
          </div>
          <div style="font-size: 0.74rem; color: var(--dash-text-dim); margin-top: 0.2rem;">
            Executive Sedan • 14 km • Pickup in 2 mins
          </div>
        </div>
      </div>

      <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
        <div style="font-size: 1.4rem; font-weight: 800; color: #FFFFFF;">$22.50</div>
        <div style="display: flex; gap: 0.5rem;">
          <button class="btn-new-view" style="padding: 0.4rem 0.8rem; font-size: 0.78rem;" onclick="document.getElementById('reqCard2').style.display='none';">Decline</button>
          <button class="btn-create-report" style="padding: 0.4rem 1rem; font-size: 0.78rem;" onclick="alert('🎉 Ride Accepted!\n\nPassenger: Marcus Knight\nRoute: Colombo Fort → Mount Lavinia\nEarnings: $22.50\n\nNavigation launched.'); document.getElementById('reqCard2').style.borderColor='#22C55E';">Accept Trip</button>
        </div>
      </div>
    </div>

  </div>

</div>
