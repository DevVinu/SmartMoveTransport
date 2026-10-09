// Core application script
document.addEventListener('DOMContentLoaded', () => {
  initPickupTimeToggle();
  initSeePricesAction();
  initChangeCityAction();
  initMobileNavToggle();
});

// Toggle pickup time between now and scheduled
function initPickupTimeToggle() {
  const timeBtn = document.getElementById('timePillBtn');
  if (!timeBtn) return;

  let isNow = true;
  timeBtn.addEventListener('click', () => {
    isNow = !isNow;
    timeBtn.innerHTML = isNow ? '🕒 Pickup now ▾' : '📅 Schedule for later ▾';
  });
}

// See prices button handler
function initSeePricesAction() {
  const seePricesBtn = document.getElementById('seePricesBtn');
  const pickupInput = document.getElementById('pickupInput');
  const dropoffInput = document.getElementById('dropoffInput');

  if (!seePricesBtn) return;

  seePricesBtn.addEventListener('click', () => {
    const pickupVal = pickupInput ? pickupInput.value.trim() : '';
    const dropoffVal = dropoffInput ? dropoffInput.value.trim() : '';

    if (!dropoffVal || dropoffVal.toLowerCase() === 'dropoff location') {
      alert('Please enter a destination / dropoff location to see prices.');
      if (dropoffInput) dropoffInput.focus();
      return;
    }

    alert(`🎉 Route Found!\n\nPickup: ${pickupVal || 'Current Location'}\nDropoff: ${dropoffVal}\n\nRedirecting to login/passenger account to finalize booking...`);
    window.location.href = 'pages/login.php';
  });
}

// Change city prompt
function initChangeCityAction() {
  const changeCityBtn = document.getElementById('changeCityBtn');
  const cityTag = document.querySelector('.city-location-tag');

  if (!changeCityBtn) return;

  changeCityBtn.addEventListener('click', (e) => {
    e.preventDefault();
    const newCity = prompt('Enter your city:', 'Colombo, LK');
    if (newCity && cityTag) {
      cityTag.innerHTML = `📍 ${newCity} <a href="#" id="changeCityBtn">Change city</a>`;
      initChangeCityAction();
    }
  });
}

// Mobile navigation menu toggle
function initMobileNavToggle() {
  const toggleBtn = document.getElementById('mobileNavToggle');
  const navMenu = document.querySelector('.nav-menu-links');

  if (!toggleBtn || !navMenu) return;

  toggleBtn.addEventListener('click', () => {
    if (navMenu.style.display === 'flex') {
      navMenu.style.display = 'none';
    } else {
      navMenu.style.display = 'flex';
      navMenu.style.flexDirection = 'column';
      navMenu.style.position = 'absolute';
      navMenu.style.top = '100%';
      navMenu.style.left = '0';
      navMenu.style.width = '100%';
      navMenu.style.backgroundColor = '#0B0F17';
      navMenu.style.padding = '1.5rem';
      navMenu.style.borderBottom = '1px solid rgba(56, 189, 248, 0.25)';
    }
  });
}
