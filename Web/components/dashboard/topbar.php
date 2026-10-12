<?php

$currentRole = strtoupper($role ?? 'ADMIN');
?>
<!-- Topbar -->
<header class="topbar">
  <div class="topbar-left">
    <button class="sidebar-toggle-btn" id="toggleSidebarBtn" type="button" title="Toggle Sidebar">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect width="18" height="18" x="3" y="3" rx="2" />
        <path d="M9 3v18" />
      </svg>
    </button>
    <?php if (isset($topbarTitle) && !empty($topbarTitle)): ?>
      <span style="font-weight:600;font-size:13.5px;color:var(--text-white); margin-left:0.5rem;"><?php echo htmlspecialchars($topbarTitle); ?></span>
    <?php endif; ?>
  </div>

  <div class="topbar-right">
    <?php if (isset($oracleUp) && isset($mongoUp) && $currentRole !== 'ADMIN'): ?>
      <div class="db-status-pill" title="Oracle DB Status">
        <span class="status-dot <?php echo $oracleUp ? 'dot-green' : 'dot-red'; ?>"></span>
        <span>Oracle <?php echo $oracleUp ? 'Live' : 'Mock/Offline'; ?></span>
      </div>
      <div class="db-status-pill" title="MongoDB Status">
        <span class="status-dot <?php echo $mongoUp ? 'dot-green' : 'dot-red'; ?>"></span>
        <span>MongoDB <?php echo $mongoUp ? 'Live' : 'Mock/Offline'; ?></span>
      </div>
    <?php endif; ?>

    <!-- Theme toggle -->
    <button class="topbar-icon-btn" type="button" title="Toggle theme">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
      </svg>
    </button>

    <!-- Settings -->
    <button class="topbar-icon-btn" type="button"
      onclick="document.querySelector('.nav-item[data-view=\'settingsView\']')?.click();" title="Settings">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path
          d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
        <circle cx="12" cy="12" r="3" />
      </svg>
    </button>

    <!-- User avatar -->
    <button class="topbar-avatar-btn" id="topbarUserBtn" type="button" title="<?php echo htmlspecialchars($fullName ?? 'User'); ?>">
      <div class="user-avatar"
        style="border-radius:50%; width:32px; height:32px; background:var(--bg-muted); font-weight:600; font-size:12px; border:1px solid var(--border-subtle);">
        <?php
        $parts = explode(' ', trim($fullName ?? 'Admin User'));
        $init = strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? 'D', 0, 1));
        echo $init ?: 'SM';
        ?>
      </div>
    </button>

    <!-- User menu -->
    <div class="popover-menu" id="topbarUserMenu">
      <div class="popover-header">
        <span class="pop-title"><?php echo htmlspecialchars($fullName ?? 'User'); ?></span>
        <span class="pop-sub"><?php echo htmlspecialchars($email ?? ''); ?></span>
      </div>
      <a class="popover-item" data-view="profileView"
        onclick="document.querySelector('[data-view=\'profileView\']')?.click();">Manage Profile</a>
      <?php if ($currentRole === 'ADMIN'): ?>
        <a class="popover-item"
          onclick="document.querySelector('.nav-item[data-view=\'usersView\']')?.click();">User Management</a>
        <a class="popover-item"
          onclick="document.querySelector('.nav-item[data-view=\'settingsView\']')?.click();">System Settings</a>
      <?php else: ?>
        <a href="../index.php" class="popover-item">Main Website</a>
        <?php if (strtoupper(Auth::user()['role'] ?? '') === 'ADMIN'): ?>
          <a href="dashboard.php" class="popover-item">Admin Command Portal</a>
        <?php endif; ?>
      <?php endif; ?>
      <div class="popover-divider"></div>
      <a href="logout.php" class="popover-item danger">Sign out</a>
    </div>
  </div>
</header>
