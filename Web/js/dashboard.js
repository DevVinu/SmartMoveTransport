// Dashboard interactivity controller
document.addEventListener('DOMContentLoaded', () => {
  const navItems = document.querySelectorAll('.nav-item[data-view]');
  const pageViews = document.querySelectorAll('.page-view');

  // Switch active view
  function switchView(viewId) {
    if (!viewId) return;

    pageViews.forEach(view => {
      view.classList.toggle('active-view', view.id === viewId);
    });

    navItems.forEach(item => {
      item.classList.toggle('active', item.dataset.view === viewId);
    });

    try {
      const url = new URL(window.location.href);
      url.searchParams.set('view', viewId);
      window.history.replaceState(null, '', url.toString());
    } catch (err) {}

    closeAllPopovers();
  }

  // Handle data-view clicks
  document.querySelectorAll('[data-view]').forEach(item => {
    item.addEventListener('click', (e) => {
      const targetView = item.dataset.view;
      if (targetView && document.getElementById(targetView)) {
        e.preventDefault();
        switchView(targetView);
      }
    });
  });

  // Restore view from URL
  try {
    const urlParams = new URLSearchParams(window.location.search);
    const initialView = urlParams.get('view') || (urlParams.get('report') ? 'reportsView' : null);
    if (initialView && document.getElementById(initialView)) {
      switchView(initialView);
    }
  } catch (err) {}

  // Submenu accordion
  document.querySelectorAll('.has-submenu').forEach(toggle => {
    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      toggle.classList.toggle('open');
      const submenu = toggle.nextElementSibling;
      if (submenu && submenu.classList.contains('sidebar-submenu')) {
        submenu.classList.toggle('open');
      }
    });
  });

  // Sidebar toggle
  const sidebar = document.getElementById('sidebar');
  const toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
  if (toggleSidebarBtn && sidebar) {
    toggleSidebarBtn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
    });
  }

  // Popover menus
  const popovers = document.querySelectorAll('.popover-menu');

  function closeAllPopovers() {
    popovers.forEach(p => p.classList.remove('show'));
    const sBtn = document.getElementById('sidebarUserBtn');
    if (sBtn) {
      sBtn.classList.remove('active');
      sBtn.setAttribute('aria-expanded', 'false');
    }
  }

  // Topbar user menu
  const topbarAvatarBtn = document.getElementById('topbarAvatarBtn') || document.getElementById('topbarUserBtn');
  const topbarUserMenu = document.getElementById('topbarUserMenu');
  if (topbarAvatarBtn && topbarUserMenu) {
    topbarAvatarBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isVisible = topbarUserMenu.classList.contains('show');
      closeAllPopovers();
      if (!isVisible) topbarUserMenu.classList.add('show');
    });
  }

  // Sidebar user menu
  const sidebarUserBtn = document.getElementById('sidebarUserBtn');
  const sidebarUserMenu = document.getElementById('sidebarUserMenu');
  if (sidebarUserBtn && sidebarUserMenu) {
    sidebarUserBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isVisible = sidebarUserMenu.classList.contains('show');
      closeAllPopovers();
      if (!isVisible) {
        sidebarUserMenu.classList.add('show');
        sidebarUserBtn.classList.add('active');
        sidebarUserBtn.setAttribute('aria-expanded', 'true');
      }
    });

    sidebarUserMenu.querySelectorAll('.popover-item[data-view]').forEach(item => {
      item.addEventListener('click', (e) => {
        e.preventDefault();
        const targetView = item.dataset.view;
        if (targetView) switchView(targetView);
      });
    });
  }

  // Close popovers on click outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.popover-menu') && !e.target.closest('.user-profile-btn') && !e.target.closest('.topbar-avatar-btn')) {
      closeAllPopovers();
    }
  });

  // Settings tabs
  const settingsNavItems = document.querySelectorAll('.settings-nav-item');
  settingsNavItems.forEach(item => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      settingsNavItems.forEach(i => i.classList.remove('active'));
      item.classList.add('active');
    });
  });

  // Subtabs
  const subtabs = document.querySelectorAll('.subtab-item');
  subtabs.forEach(tab => {
    tab.addEventListener('click', () => {
      subtabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
    });
  });

  // App connect toggle
  document.querySelectorAll('.app-connect-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const isConn = btn.classList.contains('connected');
      if (isConn) {
        btn.classList.remove('connected');
        btn.textContent = 'Connect';
      } else {
        btn.classList.add('connected');
        btn.textContent = 'Connected';
      }
    });
  });

  // Keyboard shortcut (Escape)
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAllPopovers();
  });

  // URL view navigation
  const urlParams = new URLSearchParams(window.location.search);
  const viewParam = urlParams.get('view');
  if (urlParams.has('report')) {
    const reportItem = document.querySelector('.nav-item[data-view="reportsView"]');
    if (reportItem) reportItem.click();
  } else if (urlParams.has('keyword') || viewParam === 'mongoView') {
    const mongoItem = document.querySelector('.nav-item[data-view="mongoView"]');
    if (mongoItem) mongoItem.click();
  } else if (viewParam) {
    const targetItem = document.querySelector(`.nav-item[data-view="${viewParam}"]`);
    if (targetItem) targetItem.click();
  }
});

// Modal controls
window.openModal = function(id) {
  const el = document.getElementById(id);
  if (el) el.style.display = 'flex';
};

window.closeModal = function(id) {
  const el = document.getElementById(id);
  if (el) el.style.display = 'none';
};

// Filter functions
window.filterFleet = function(type, btn) {
  document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.fleet-row').forEach(r => {
    const rType = (r.dataset.type || '').toUpperCase();
    const isMatch = (type === 'ALL' || rType === type || (type === 'CAR' && rType === 'LUXURYCAR'));
    r.style.display = isMatch ? '' : 'none';
  });
};

window.filterUsers = function(role, btn) {
  document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.user-row').forEach(r => {
    r.style.display = (role === 'ALL' || r.dataset.role === role) ? '' : 'none';
  });
};

window.filterTrips = function(status, btn) {
  const container = btn.closest('.filter-pills-bar') || document;
  container.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.trip-row').forEach(r => {
    r.style.display = (status === 'ALL' || r.dataset.status === status) ? '' : 'none';
  });
};

window.filterShifts = function(status, btn) {
  const container = btn.closest('.filter-pills-bar') || document;
  container.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.shift-row').forEach(r => {
    r.style.display = (status === 'ALL' || r.dataset.status === status) ? '' : 'none';
  });
};

// Toggle actor specific fields
window.toggleRoleFields = function(role, prefix) {
  const roles = ['Passenger', 'Driver', 'Staff', 'Admin'];
  roles.forEach(r => {
    const el = document.getElementById(`${prefix}${r}Fields`);
    if (el) {
      el.style.display = (r.toUpperCase() === role.toUpperCase()) ? 'block' : 'none';
    }
  });
};

// Toggle vehicle subtype fields
window.toggleVehicleFields = function(vType) {
  const types = ['BUS', 'VAN', 'LUXURYCAR', 'THREEWHEEL', 'BIKE'];
  const capMap = { 'BUS': 32, 'VAN': 14, 'LUXURYCAR': 4, 'THREEWHEEL': 3, 'BIKE': 1 };
  types.forEach(t => {
    const el = document.getElementById(`addVehicle${t}Fields`);
    if (el) {
      el.style.display = (t.toUpperCase() === vType.toUpperCase()) ? 'block' : 'none';
    }
  });
  const capInput = document.getElementById('addVehicleCapacityInput');
  if (capInput && capMap[vType.toUpperCase()]) {
    capInput.value = capMap[vType.toUpperCase()];
  }
};

// Open and populate edit user modal
window.openEditUserModal = function(u) {
  if (!u) return;

  const id = u.userid || u.userId || u.USERID || '';
  const first = u.firstname || u.firstName || u.FIRSTNAME || '';
  const last = u.lastname || u.lastName || u.LASTNAME || '';
  const email = u.email || u.EMAIL || '';
  const phone = u.phone || u.PHONE || '';
  const nic = u.nic || u.NIC || '';
  const role = (u.role || u.ROLE || 'PASSENGER').toUpperCase();
  const status = (u.accountstatus || u.accountStatus || u.ACCOUNTSTATUS || 'ACTIVE').toUpperCase();

  const idEl = document.getElementById('editUserId');
  if (idEl) idEl.value = id;
  const fnEl = document.getElementById('editFirstName');
  if (fnEl) fnEl.value = first;
  const lnEl = document.getElementById('editLastName');
  if (lnEl) lnEl.value = last;
  const emEl = document.getElementById('editEmail');
  if (emEl) emEl.value = email;
  const phEl = document.getElementById('editPhone');
  if (phEl) phEl.value = phone;
  const nicEl = document.getElementById('editNic');
  if (nicEl) nicEl.value = nic;
  const rEl = document.getElementById('editRoleSelect');
  if (rEl) rEl.value = role;
  const stEl = document.getElementById('editAccountStatus');
  if (stEl) stEl.value = status;
  const pwEl = document.getElementById('editPassword');
  if (pwEl) pwEl.value = '';

  // Passenger attributes
  const pAddr = document.getElementById('editPassengerAddress');
  if (pAddr) pAddr.value = u.address || u.ADDRESS || '';
  const pPay = document.getElementById('editPassengerPayment');
  if (pPay) pPay.value = (u.preferredpaymentmethod || u.preferredPaymentMethod || 'CARD').toUpperCase();

  // Driver attributes
  const dLic = document.getElementById('editDriverLicenseNo');
  if (dLic) dLic.value = u.licenseno || u.licenseNo || u.licenseNumber || '';
  const dType = document.getElementById('editDriverLicenseType');
  if (dType) dType.value = u.licensetype || u.licenseType || 'HEAVY_COACH';
  const dExp = document.getElementById('editDriverLicenseExpiryDate');
  if (dExp) dExp.value = u.licenseexpirydate || u.licenseExpiryDate || '';
  const dStat = document.getElementById('editDriverStatus');
  if (dStat) dStat.value = (u.driverstatus || u.driverStatus || 'AVAILABLE').toUpperCase();

  // Staff attributes
  const sPos = document.getElementById('editStaffPosition');
  if (sPos) sPos.value = u.position || u.POSITION || '';
  const sStat = document.getElementById('editStaffStatus');
  if (sStat) sStat.value = (u.staffstatus || u.staffStatus || 'ACTIVE').toUpperCase();

  // Admin attributes
  const aCode = document.getElementById('editAdminCode');
  if (aCode) aCode.value = u.admincode || u.adminCode || '';
  const aPriv = document.getElementById('editAdminPrivilege');
  if (aPriv) aPriv.value = (u.privilegelevel || u.privilegeLevel || 'OPS_ADMIN').toUpperCase();
  const aDept = document.getElementById('editAdminDepartment');
  if (aDept) aDept.value = u.department || u.DEPARTMENT || '';

  toggleRoleFields(role, 'edit');
  openModal('modalEditUser');
};
