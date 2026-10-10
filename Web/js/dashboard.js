/**
 * SHADCN ADMIN DASHBOARD — INTERACTIVITY ENGINE
 * Pixel-perfect replica of Shadcn Admin (Vite + ShadcnUI)
 */

document.addEventListener('DOMContentLoaded', () => {
  // --- View Switching ---
  const navItems = document.querySelectorAll('.nav-item[data-view]');
  const pageViews = document.querySelectorAll('.page-view');

  function switchView(viewId) {
    pageViews.forEach(view => {
      view.classList.toggle('active-view', view.id === viewId);
    });

    navItems.forEach(item => {
      item.classList.toggle('active', item.dataset.view === viewId);
    });

    // Close any open popovers on view switch
    closeAllPopovers();
  }

  navItems.forEach(item => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      const targetView = item.dataset.view;
      if (targetView) {
        switchView(targetView);
      }
    });
  });

  // --- Submenu Accordion (e.g. Secured by Clerk) ---
  const accordionToggles = document.querySelectorAll('.has-submenu');
  accordionToggles.forEach(toggle => {
    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      toggle.classList.toggle('open');
      const submenu = toggle.nextElementSibling;
      if (submenu && submenu.classList.contains('sidebar-submenu')) {
        submenu.classList.toggle('open');
      }
    });
  });

  // --- Sidebar Collapse ---
  const sidebar = document.getElementById('sidebar');
  const toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
  if (toggleSidebarBtn && sidebar) {
    toggleSidebarBtn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
    });
  }

  // --- Popover Engine ---
  const popovers = document.querySelectorAll('.popover-menu');

  function closeAllPopovers() {
    popovers.forEach(p => p.classList.remove('show'));
  }

  // Topbar user menu toggle
  const topbarAvatarBtn = document.getElementById('topbarAvatarBtn');
  const topbarUserMenu = document.getElementById('topbarUserMenu');
  if (topbarAvatarBtn && topbarUserMenu) {
    topbarAvatarBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isVisible = topbarUserMenu.classList.contains('show');
      closeAllPopovers();
      if (!isVisible) topbarUserMenu.classList.add('show');
    });
  }

  // Sidebar footer user menu toggle
  const sidebarUserBtn = document.getElementById('sidebarUserBtn');
  const sidebarUserMenu = document.getElementById('sidebarUserMenu');
  if (sidebarUserBtn && sidebarUserMenu) {
    sidebarUserBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isVisible = sidebarUserMenu.classList.contains('show');
      closeAllPopovers();
      if (!isVisible) sidebarUserMenu.classList.add('show');
    });
  }

  // Tasks Filter Popovers (Status & Priority)
  const statusFilterBtn = document.getElementById('statusFilterBtn');
  const statusFilterMenu = document.getElementById('statusFilterMenu');
  if (statusFilterBtn && statusFilterMenu) {
    statusFilterBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isVisible = statusFilterMenu.classList.contains('show');
      closeAllPopovers();
      if (!isVisible) {
        statusFilterMenu.classList.add('show');
        // Position popover relative to button
        const rect = statusFilterBtn.getBoundingClientRect();
        statusFilterMenu.style.top = (rect.bottom + window.scrollY + 6) + 'px';
        statusFilterMenu.style.left = (rect.left + window.scrollX) + 'px';
      }
    });
  }

  const priorityFilterBtn = document.getElementById('priorityFilterBtn');
  const priorityFilterMenu = document.getElementById('priorityFilterMenu');
  if (priorityFilterBtn && priorityFilterMenu) {
    priorityFilterBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isVisible = priorityFilterMenu.classList.contains('show');
      closeAllPopovers();
      if (!isVisible) {
        priorityFilterMenu.classList.add('show');
        const rect = priorityFilterBtn.getBoundingClientRect();
        priorityFilterMenu.style.top = (rect.bottom + window.scrollY + 6) + 'px';
        priorityFilterMenu.style.left = (rect.left + window.scrollX) + 'px';
      }
    });
  }

  // Row Action Dropdowns
  document.addEventListener('click', (e) => {
    const actionBtn = e.target.closest('.row-action-btn');
    if (actionBtn) {
      e.stopPropagation();
      const rowMenu = actionBtn.nextElementSibling;
      const isVisible = rowMenu && rowMenu.classList.contains('show');
      closeAllPopovers();
      if (rowMenu && !isVisible) {
        rowMenu.classList.add('show');
        const rect = actionBtn.getBoundingClientRect();
        rowMenu.style.top = (rect.bottom + window.scrollY + 4) + 'px';
        rowMenu.style.left = (rect.right - 140 + window.scrollX) + 'px';
      }
      return;
    }

    // Click outside closes any active popover
    if (!e.target.closest('.popover-menu') && !e.target.closest('.filter-dropdown-btn')) {
      closeAllPopovers();
    }
  });

  // --- Tasks Filtering Logic ---
  const activeStatusFilters = new Set();
  const activePriorityFilters = new Set();
  const taskRows = document.querySelectorAll('.task-table-row');
  const taskSearchInput = document.getElementById('taskSearchInput');
  const activeFiltersContainer = document.getElementById('activeFiltersBadges');
  const resetFiltersBtn = document.getElementById('resetFiltersBtn');

  function updateTaskFiltering() {
    const query = taskSearchInput ? taskSearchInput.value.toLowerCase().trim() : '';

    let visibleCount = 0;
    taskRows.forEach(row => {
      const title = row.dataset.title ? row.dataset.title.toLowerCase() : '';
      const taskId = row.dataset.taskId ? row.dataset.taskId.toLowerCase() : '';
      const status = row.dataset.status;
      const priority = row.dataset.priority;

      const matchesSearch = !query || title.includes(query) || taskId.includes(query);
      const matchesStatus = activeStatusFilters.size === 0 || activeStatusFilters.has(status);
      const matchesPriority = activePriorityFilters.size === 0 || activePriorityFilters.has(priority);

      if (matchesSearch && matchesStatus && matchesPriority) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    // Update active filter pills in header
    renderActiveFilterBadges();
  }

  function renderActiveFilterBadges() {
    if (!activeFiltersContainer) return;
    activeFiltersContainer.innerHTML = '';

    if (activeStatusFilters.size > 0 || activePriorityFilters.size > 0) {
      activeStatusFilters.forEach(st => {
        const badge = document.createElement('span');
        badge.className = 'filter-badge-pill';
        badge.textContent = st;
        activeFiltersContainer.appendChild(badge);
      });
      activePriorityFilters.forEach(pr => {
        const badge = document.createElement('span');
        badge.className = 'filter-badge-pill';
        badge.textContent = pr;
        activeFiltersContainer.appendChild(badge);
      });

      if (resetFiltersBtn) resetFiltersBtn.style.display = 'inline-flex';
    } else {
      if (resetFiltersBtn) resetFiltersBtn.style.display = 'none';
    }
  }

  // Status Filter Checkboxes Click Handlers
  const statusOptions = document.querySelectorAll('.status-filter-option');
  statusOptions.forEach(opt => {
    opt.addEventListener('click', (e) => {
      e.stopPropagation();
      const val = opt.dataset.val;
      if (activeStatusFilters.has(val)) {
        activeStatusFilters.delete(val);
        opt.classList.remove('selected');
      } else {
        activeStatusFilters.add(val);
        opt.classList.add('selected');
      }
      updateTaskFiltering();
    });
  });

  // Priority Filter Checkboxes Click Handlers
  const priorityOptions = document.querySelectorAll('.priority-filter-option');
  priorityOptions.forEach(opt => {
    opt.addEventListener('click', (e) => {
      e.stopPropagation();
      const val = opt.dataset.val;
      if (activePriorityFilters.has(val)) {
        activePriorityFilters.delete(val);
        opt.classList.remove('selected');
      } else {
        activePriorityFilters.add(val);
        opt.classList.add('selected');
      }
      updateTaskFiltering();
    });
  });

  // Clear filters button
  const clearStatusBtn = document.getElementById('clearStatusFiltersBtn');
  if (clearStatusBtn) {
    clearStatusBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      activeStatusFilters.clear();
      statusOptions.forEach(opt => opt.classList.remove('selected'));
      updateTaskFiltering();
    });
  }

  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener('click', () => {
      activeStatusFilters.clear();
      activePriorityFilters.clear();
      statusOptions.forEach(opt => opt.classList.remove('selected'));
      priorityOptions.forEach(opt => opt.classList.remove('selected'));
      if (taskSearchInput) taskSearchInput.value = '';
      updateTaskFiltering();
    });
  }

  if (taskSearchInput) {
    taskSearchInput.addEventListener('input', updateTaskFiltering);
  }

  // --- Select All Rows Checkbox ---
  const selectAllCheck = document.getElementById('selectAllTasksCheck');
  if (selectAllCheck) {
    selectAllCheck.addEventListener('change', () => {
      const isChecked = selectAllCheck.checked;
      document.querySelectorAll('.task-row-check').forEach(cb => {
        cb.checked = isChecked;
      });
    });
  }

  // --- Settings Tab Switching ---
  const settingsNavItems = document.querySelectorAll('.settings-nav-item');
  settingsNavItems.forEach(item => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      settingsNavItems.forEach(i => i.classList.remove('active'));
      item.classList.add('active');
    });
  });

  // --- Dashboard Sub-tabs Switching ---
  const subtabs = document.querySelectorAll('.subtab-item');
  subtabs.forEach(tab => {
    tab.addEventListener('click', () => {
      subtabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
    });
  });

  // --- Apps Connect / Connected Toggle ---
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

  // --- Global Keyboard Shortcuts ---
  window.addEventListener('keydown', (e) => {
    // ⌘K or Ctrl+K focus search
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      const globalSearch = document.getElementById('globalSearchInput');
      if (globalSearch) globalSearch.focus();
    }
    // Escape closes popovers
    if (e.key === 'Escape') {
      closeAllPopovers();
    }
  });

  // Initial render
  updateTaskFiltering();
});
