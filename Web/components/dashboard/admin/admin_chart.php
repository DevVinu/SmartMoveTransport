<?php
// Admin Actor Network Activities Chart Component
?>
<div class="chart-card-box">
  
  <div class="chart-card-header">
    <div class="chart-title-area">
      <h2 class="chart-title">Fleet & Network Activities</h2>
      <div class="chart-num-row">
        <span class="chart-big-num" id="networkMetricNum">6,782</span>
        <span class="chart-badge">+ 7%</span>
      </div>
      <div class="chart-sub-label">Total telematics & dispatch sessions this week</div>
    </div>

    <!-- Time segment switcher -->
    <div class="chart-segmented-control" id="timeRangeControl">
      <button class="segment-btn active" data-days="7">7 days</button>
      <button class="segment-btn" data-days="30">30 days</button>
      <button class="segment-btn" data-days="90">90 days</button>
    </div>
  </div>

  <!-- Scalable Vector Line Chart in Blue & White Theme -->
  <div class="chart-viewport-wrap">
    <svg class="svg-chart-container" viewBox="0 0 650 220" preserveAspectRatio="none">
      <defs>
        <linearGradient id="blueGlowGradient" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="#3B82F6" stop-opacity="0.28"/>
          <stop offset="100%" stop-color="#3B82F6" stop-opacity="0.0"/>
        </linearGradient>
      </defs>

      <!-- Y-Axis Grid Lines & Labels -->
      <text x="32" y="20" fill="#94A3B8" font-size="10" text-anchor="end">800</text>
      <line x1="45" y1="16" x2="640" y2="16" stroke="rgba(255, 255, 255, 0.05)" stroke-dasharray="3,3" stroke-width="1"/>

      <text x="32" y="65" fill="#94A3B8" font-size="10" text-anchor="end">600</text>
      <line x1="45" y1="61" x2="640" y2="61" stroke="rgba(255, 255, 255, 0.05)" stroke-dasharray="3,3" stroke-width="1"/>

      <text x="32" y="110" fill="#94A3B8" font-size="10" text-anchor="end">400</text>
      <line x1="45" y1="106" x2="640" y2="106" stroke="rgba(255, 255, 255, 0.05)" stroke-dasharray="3,3" stroke-width="1"/>

      <text x="32" y="155" fill="#94A3B8" font-size="10" text-anchor="end">200</text>
      <line x1="45" y1="151" x2="640" y2="151" stroke="rgba(255, 255, 255, 0.05)" stroke-dasharray="3,3" stroke-width="1"/>

      <text x="32" y="195" fill="#94A3B8" font-size="10" text-anchor="end">0</text>
      <line x1="45" y1="191" x2="640" y2="191" stroke="rgba(255, 255, 255, 0.05)" stroke-width="1"/>

      <!-- X-Axis Labels -->
      <text x="50" y="212" fill="#CBD5E1" font-size="10">Mon</text>
      <text x="145" y="212" fill="#CBD5E1" font-size="10">Tue</text>
      <text x="240" y="212" fill="#CBD5E1" font-size="10">Wed</text>
      <text x="335" y="212" fill="#CBD5E1" font-size="10">Thu</text>
      <text x="430" y="212" fill="#CBD5E1" font-size="10">Fri</text>
      <text x="525" y="212" fill="#CBD5E1" font-size="10">Sat</text>
      <text x="615" y="212" fill="#CBD5E1" font-size="10">Sun</text>

      <!-- Blue Filled Gradient Area -->
      <path id="blueAreaPath" d="M 50 148 C 110 135, 170 120, 240 122 C 300 125, 335 70, 420 50 C 490 35, 545 65, 630 68 L 630 191 L 50 191 Z" fill="url(#blueGlowGradient)" />

      <!-- Blue Solid Curve: Sessions -->
      <path id="blueLinePath" d="M 50 148 C 110 135, 170 120, 240 122 C 300 125, 335 70, 420 50 C 490 35, 545 65, 630 68" fill="none" stroke="#3B82F6" stroke-width="2.6" stroke-linecap="round" />

      <!-- White Dashed Curve: Page Views -->
      <path id="whiteLinePath" d="M 50 162 C 110 152, 170 138, 240 140 C 300 142, 335 95, 420 85 C 490 75, 545 92, 630 96" fill="none" stroke="#FFFFFF" stroke-width="1.8" stroke-dasharray="5,5" stroke-linecap="round" opacity="0.85" />
    </svg>
  </div>

  <!-- Chart Legend -->
  <div class="chart-legend-row">
    <div class="legend-item">
      <span class="legend-indicator-line line-solid-blue"></span>
      <span>Live Fleet Sessions</span>
    </div>
    <div class="legend-item">
      <span class="legend-indicator-line line-dashed-white"></span>
      <span>Passenger Inquiries</span>
    </div>
  </div>

</div>
