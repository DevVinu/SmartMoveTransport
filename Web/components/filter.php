<?php
// Transit filter component
?>
<section class="transit-filter-section" id="filter">
  <div class="filter-section-container">
    
    <div class="filter-header-block">
      <div class="filter-eyebrow">SRI LANKA TRANSIT NETWORK</div>
      <h2 class="filter-main-title">Find Your Route &amp; <span class="blue-text">Reserve Your Ride</span></h2>
      <p class="filter-subtitle">
        Filter real-time expressway coaches, airport shuttles, and intercity corporate fleets across major transit corridors.
      </p>
    </div>

    <!-- Filter controls -->
    <div class="filter-controls-panel">
      <!-- Search inputs -->
      <div class="filter-inputs-grid">
        <div class="filter-field-group">
          <label class="filter-field-label">Quick Search</label>
          <div class="filter-input-wrap">
            <input type="text" id="routeKeywordInput" class="filter-text-input" placeholder="Search city, route, or coach (e.g. Galle, E01)...">
          </div>
        </div>

        <div class="filter-field-group">
          <label class="filter-field-label">Departure From (Origin)</label>
          <div class="filter-input-wrap">
            <select id="originFilterSelect" class="filter-select-input">
              <option value="ALL">All Origins (Any Departure)</option>
              <option value="Colombo">Colombo (Fort / Expressway Hub)</option>
              <option value="Kandy">Kandy (Central Transit Hub)</option>
              <option value="Galle">Galle (Karapitiya / Fort)</option>
              <option value="Katunayake">Katunayake (Airport Terminal)</option>
              <option value="Matara">Matara (Highway Terminal)</option>
              <option value="Negombo">Negombo City</option>
            </select>
          </div>
        </div>

        <div class="filter-field-group">
          <label class="filter-field-label">Destination To</label>
          <div class="filter-input-wrap">
            <select id="destFilterSelect" class="filter-select-input">
              <option value="ALL">All Destinations (Any Arrival)</option>
              <option value="Galle">Galle (Southern Expressway E01)</option>
              <option value="Kandy">Kandy (Central Corridor E04)</option>
              <option value="Katunayake">Katunayake (Airport Expressway E03)</option>
              <option value="Matara">Matara (Southern Highway E01)</option>
              <option value="Colombo">Colombo Central Hub</option>
              <option value="Jaffna">Jaffna (Northern Express)</option>
              <option value="Negombo">Negombo Town</option>
            </select>
          </div>
        </div>

        <div class="filter-field-group">
          <label class="filter-field-label">Departure Window</label>
          <div class="filter-input-wrap">
            <select id="timeFilterSelect" class="filter-select-input">
              <option value="ALL">Any Time</option>
              <option value="MORNING">Morning (06:00 AM – 12:00 PM)</option>
              <option value="AFTERNOON">Afternoon (12:00 PM – 06:00 PM)</option>
              <option value="EVENING">Evening / Night (06:00 PM – 11:30 PM)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Fleet category pills -->
      <div class="filter-pills-row">
        <span class="filter-pills-label">Fleet Category:</span>
        <div class="filter-pills-list">
          <button type="button" class="type-pill-btn active" data-type="ALL">All Fleets</button>
          <button type="button" class="type-pill-btn" data-type="BUS">Expressway Coach</button>
          <button type="button" class="type-pill-btn" data-type="VAN">Executive Van</button>
          <button type="button" class="type-pill-btn" data-type="LUXURYCAR">Luxury Sedan</button>
          <button type="button" class="type-pill-btn" data-type="CITY">City Express</button>
        </div>

        <button type="button" class="btn-filter-reset" id="resetFiltersBtn">Reset Filters</button>
      </div>
    </div>

    <!-- Results status -->
    <div class="filter-results-status">
      <span class="results-count-text">
        Showing <strong id="routeResultsCount">8</strong> available transit options
      </span>
      <span class="results-badge-live">● Live Expressways Sync</span>
    </div>

    <!-- Route cards -->
    <div class="filter-cards-grid" id="filterCardsContainer">
      
      <!-- Card: Colombo - Galle -->
      <div class="route-card" data-origin="Colombo" data-dest="Galle" data-type="BUS" data-time="MORNING" data-keyword="colombo galle e01 king long southern expressway bus">
        <div class="route-card-top">
          <span class="route-category-tag tag-bus">Expressway Coach</span>
          <span class="route-status-pill status-open">Available</span>
        </div>
        <h3 class="route-title">Colombo Fort  Galle Interchange (E01)</h3>
        <p class="route-sub">Southern Expressway Non-stop Direct Route</p>
        <div class="route-meta-row">
          <span class="route-meta-item"> 08:30 AM (Morning)</span>
          <span class="route-meta-item">⏱ ~1h 15m</span>
          <span class="route-meta-item"> 42 Seats Available</span>
        </div>
        <div class="route-amenities">
          <span class="amenity-tag"> Dual A/C</span>
          <span class="amenity-tag"> Toll Included</span>
          <span class="amenity-tag"> Free Wi-Fi</span>
        </div>
        <div class="route-card-bottom">
          <div class="route-price-box">
            <span class="price-val">LKR 1,250</span>
            <span class="price-unit">per passenger seat</span>
          </div>
          <a href="pages/signin.php" class="btn-book-route">Reserve Seat →</a>
        </div>
      </div>

      <!-- Card: Colombo - Kandy -->
      <div class="route-card" data-origin="Colombo" data-dest="Kandy" data-type="BUS" data-time="MORNING" data-keyword="colombo kandy central expressway e04 yutong ac">
        <div class="route-card-top">
          <span class="route-category-tag tag-bus">Expressway Coach</span>
          <span class="route-status-pill status-open">Available</span>
        </div>
        <h3 class="route-title">Colombo Fort  Kandy Central (E04)</h3>
        <p class="route-sub">Central Expressway Intercity Air-Conditioned Coach</p>
        <div class="route-meta-row">
          <span class="route-meta-item"> 07:15 AM (Morning)</span>
          <span class="route-meta-item">⏱ ~2h 30m</span>
          <span class="route-meta-item"> 38 Seats Available</span>
        </div>
        <div class="route-amenities">
          <span class="amenity-tag"> Climate Control</span>
          <span class="amenity-tag"> Luggage Bay</span>
          <span class="amenity-tag"> GPS Live</span>
        </div>
        <div class="route-card-bottom">
          <div class="route-price-box">
            <span class="price-val">LKR 1,450</span>
            <span class="price-unit">per passenger seat</span>
          </div>
          <a href="pages/signin.php" class="btn-book-route">Reserve Seat →</a>
        </div>
      </div>

      <!-- Card: Colombo - Airport -->
      <div class="route-card" data-origin="Colombo" data-dest="Katunayake" data-type="VAN" data-time="AFTERNOON" data-keyword="colombo katunayake airport e03 toyota kdh van shuttle">
        <div class="route-card-top">
          <span class="route-category-tag tag-van">Executive Van</span>
          <span class="route-status-pill status-priority">Airport Express</span>
        </div>
        <h3 class="route-title">Colombo Central  Katunayake Intl Airport (E03)</h3>
        <p class="route-sub">Airport Expressway Executive Commuter Van Transfer</p>
        <div class="route-meta-row">
          <span class="route-meta-item"> 02:00 PM (Afternoon)</span>
          <span class="route-meta-item">⏱ ~30 mins</span>
          <span class="route-meta-item"> 10 Seats / Luggage</span>
        </div>
        <div class="route-amenities">
          <span class="amenity-tag"> Flight Tracking</span>
          <span class="amenity-tag"> Large Boot Space</span>
          <span class="amenity-tag"> E03 Toll Free</span>
        </div>
        <div class="route-card-bottom">
          <div class="route-price-box">
            <span class="price-val">LKR 8,500</span>
            <span class="price-unit">total private hire</span>
          </div>
          <a href="pages/signin.php" class="btn-book-route">Reserve Shuttle →</a>
        </div>
      </div>

      <!-- Card: Colombo - Matara -->
      <div class="route-card" data-origin="Colombo" data-dest="Matara" data-type="BUS" data-time="EVENING" data-keyword="colombo matara southern expressway e01 ashok leyland coach">
        <div class="route-card-top">
          <span class="route-category-tag tag-bus">Expressway Coach</span>
          <span class="route-status-pill status-open">Available</span>
        </div>
        <h3 class="route-title">Colombo Commercial Hub  Matara Terminal</h3>
        <p class="route-sub">Southern Expressway E01 Full Corridor Service</p>
        <div class="route-meta-row">
          <span class="route-meta-item"> 06:30 PM (Evening)</span>
          <span class="route-meta-item">⏱ ~1h 50m</span>
          <span class="route-meta-item"> 45 Seats Available</span>
        </div>
        <div class="route-amenities">
          <span class="amenity-tag"> High Cool A/C</span>
          <span class="amenity-tag"> USB Charging</span>
          <span class="amenity-tag"> Expressway Toll</span>
        </div>
        <div class="route-card-bottom">
          <div class="route-price-box">
            <span class="price-val">LKR 1,600</span>
            <span class="price-unit">per passenger seat</span>
          </div>
          <a href="pages/signin.php" class="btn-book-route">Reserve Seat →</a>
        </div>
      </div>

      <!-- Card: Colombo - Kandy Sedan -->
      <div class="route-card" data-origin="Colombo" data-dest="Kandy" data-type="LUXURYCAR" data-time="MORNING" data-keyword="colombo kandy sedan toyota prius chauffeur private ride">
        <div class="route-card-top">
          <span class="route-category-tag tag-sedan">Luxury Sedan</span>
          <span class="route-status-pill status-priority">Chauffeur</span>
        </div>
        <h3 class="route-title">Colombo City  Kandy Royal Botanical</h3>
        <p class="route-sub">Private Executive Chauffeur Direct Door-to-Door</p>
        <div class="route-meta-row">
          <span class="route-meta-item"> 09:00 AM (Morning)</span>
          <span class="route-meta-item">⏱ ~2h 45m</span>
          <span class="route-meta-item"> 1–4 Executive Seats</span>
        </div>
        <div class="route-amenities">
          <span class="amenity-tag"> Verified Captain</span>
          <span class="amenity-tag"> Premium Insurance</span>
          <span class="amenity-tag"> Leather Interior</span>
        </div>
        <div class="route-card-bottom">
          <div class="route-price-box">
            <span class="price-val">LKR 18,500</span>
            <span class="price-unit">full private sedan</span>
          </div>
          <a href="pages/signin.php" class="btn-book-route">Book Chauffeur →</a>
        </div>
      </div>

      <!-- Card: Colombo - Jaffna -->
      <div class="route-card" data-origin="Colombo" data-dest="Jaffna" data-type="BUS" data-time="EVENING" data-keyword="colombo jaffna northern scania sleeper overnight">
        <div class="route-card-top">
          <span class="route-category-tag tag-bus">Expressway Coach</span>
          <span class="route-status-pill status-open">Available</span>
        </div>
        <h3 class="route-title">Colombo Fort  Jaffna Express Terminal</h3>
        <p class="route-sub">Northern Corridor Deluxe Air-Conditioned Sleeper</p>
        <div class="route-meta-row">
          <span class="route-meta-item"> 08:00 PM (Evening)</span>
          <span class="route-meta-item">⏱ ~7h 30m</span>
          <span class="route-meta-item"> 32 Sleeper Seats</span>
        </div>
        <div class="route-amenities">
          <span class="amenity-tag"> Reclining Sleeper</span>
          <span class="amenity-tag"> Temperature Control</span>
          <span class="amenity-tag"> Refreshment Stop</span>
        </div>
        <div class="route-card-bottom">
          <div class="route-price-box">
            <span class="price-val">LKR 2,800</span>
            <span class="price-unit">per passenger seat</span>
          </div>
          <a href="pages/signin.php" class="btn-book-route">Reserve Sleeper →</a>
        </div>
      </div>

      <!-- Card: Colombo - Negombo -->
      <div class="route-card" data-origin="Colombo" data-dest="Negombo" data-type="VAN" data-time="AFTERNOON" data-keyword="colombo negombo beach town hiace van commuter">
        <div class="route-card-top">
          <span class="route-category-tag tag-van">Executive Van</span>
          <span class="route-status-pill status-open">Available</span>
        </div>
        <h3 class="route-title">Colombo Fort  Negombo Town &amp; Beach</h3>
        <p class="route-sub">Coastal Corridor Frequent Shuttle Van</p>
        <div class="route-meta-row">
          <span class="route-meta-item"> 04:30 PM (Afternoon)</span>
          <span class="route-meta-item">⏱ ~45 mins</span>
          <span class="route-meta-item"> 12 Seats Available</span>
        </div>
        <div class="route-amenities">
          <span class="amenity-tag"> Air Conditioned</span>
          <span class="amenity-tag"> Coastal Route</span>
          <span class="amenity-tag">⏱ High Frequency</span>
        </div>
        <div class="route-card-bottom">
          <div class="route-price-box">
            <span class="price-val">LKR 950</span>
            <span class="price-unit">per passenger seat</span>
          </div>
          <a href="pages/signin.php" class="btn-book-route">Reserve Seat →</a>
        </div>
      </div>

      <!-- Card: Metro Tuk -->
      <div class="route-card" data-origin="Colombo" data-dest="Colombo" data-type="CITY" data-time="ALL" data-keyword="colombo city bajaj tuk threewheeler metro quick dispatch">
        <div class="route-card-top">
          <span class="route-category-tag tag-city">City Express</span>
          <span class="route-status-pill status-open">Instant Dispatch</span>
        </div>
        <h3 class="route-title">Greater Colombo Metro Express</h3>
        <p class="route-sub">Quick City Transit &amp; Neighbourhood Commutes</p>
        <div class="route-meta-row">
          <span class="route-meta-item"> On-Demand (24/7)</span>
          <span class="route-meta-item">⏱ Instant Arrival</span>
          <span class="route-meta-item"> 1–3 Passengers</span>
        </div>
        <div class="route-amenities">
          <span class="amenity-tag"> Fast Dispatch</span>
          <span class="amenity-tag"> Metered Tracking</span>
          <span class="amenity-tag"> Cash &amp; Digital Pay</span>
        </div>
        <div class="route-card-bottom">
          <div class="route-price-box">
            <span class="price-val">LKR 450</span>
            <span class="price-unit">base starting fare</span>
          </div>
          <a href="pages/signin.php" class="btn-book-route">Hail City Ride →</a>
        </div>
      </div>

    </div>

    <!-- Empty state -->
    <div class="filter-empty-state" id="filterEmptyState" style="display:none;">
      <div class="empty-state-icon"></div>
      <h3 class="empty-state-title">No Transit Corridors Found</h3>
      <p class="empty-state-desc">No scheduled routes match your current filter criteria. Try choosing "All Fleets" or resetting the search.</p>
      <button type="button" class="btn-filter-reset" onclick="document.getElementById('resetFiltersBtn').click();">Reset All Filters</button>
    </div>

  </div>
</section>

<!-- Filter script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const keywordInput = document.getElementById('routeKeywordInput');
  const originSelect = document.getElementById('originFilterSelect');
  const destSelect   = document.getElementById('destFilterSelect');
  const timeSelect   = document.getElementById('timeFilterSelect');
  const typePills    = document.querySelectorAll('.type-pill-btn');
  const resetBtn     = document.getElementById('resetFiltersBtn');
  const cards        = document.querySelectorAll('.route-card');
  const countEl      = document.getElementById('routeResultsCount');
  const emptyState   = document.getElementById('filterEmptyState');

  let activeType = 'ALL';

  function applyFilters() {
    const keyword = (keywordInput ? keywordInput.value : '').trim().toLowerCase();
    const origin  = originSelect ? originSelect.value : 'ALL';
    const dest    = destSelect ? destSelect.value : 'ALL';
    const time    = timeSelect ? timeSelect.value : 'ALL';

    let visibleCount = 0;

    cards.forEach(card => {
      const cardType    = card.getAttribute('data-type') || '';
      const cardOrigin  = card.getAttribute('data-origin') || '';
      const cardDest    = card.getAttribute('data-dest') || '';
      const cardTime    = card.getAttribute('data-time') || '';
      const cardKeyword = (card.getAttribute('data-keyword') || '') + ' ' + card.innerText.toLowerCase();

      // Check category
      const matchType = (activeType === 'ALL') || (cardType === activeType);

      // Check origin
      const matchOrigin = (origin === 'ALL') || (cardOrigin === origin);

      // Check destination
      const matchDest = (dest === 'ALL') || (cardDest === dest);

      // Check time
      const matchTime = (time === 'ALL') || (cardTime === 'ALL') || (cardTime === time);

      // Check keyword
      const matchKeyword = !keyword || cardKeyword.includes(keyword);

      if (matchType && matchOrigin && matchDest && matchTime && matchKeyword) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (countEl) countEl.textContent = visibleCount;
    if (emptyState) {
      emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  // Type pills click handler
  typePills.forEach(pill => {
    pill.addEventListener('click', function() {
      typePills.forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      activeType = this.getAttribute('data-type') || 'ALL';
      applyFilters();
    });
  });

  // Inputs change handlers
  if (keywordInput) keywordInput.addEventListener('input', applyFilters);
  if (originSelect) originSelect.addEventListener('change', applyFilters);
  if (destSelect)   destSelect.addEventListener('change', applyFilters);
  if (timeSelect)   timeSelect.addEventListener('change', applyFilters);

  // Reset button
  if (resetBtn) {
    resetBtn.addEventListener('click', function() {
      if (keywordInput) keywordInput.value = '';
      if (originSelect) originSelect.value = 'ALL';
      if (destSelect)   destSelect.value = 'ALL';
      if (timeSelect)   timeSelect.value = 'ALL';
      activeType = 'ALL';
      typePills.forEach(p => {
        p.classList.toggle('active', p.getAttribute('data-type') === 'ALL');
      });
      applyFilters();
    });
  }
});
</script>
