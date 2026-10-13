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
      <a href="../index.php" class="popover-item">Main Website</a>
      <?php if ($currentRole !== 'ADMIN' && strtoupper(Auth::user()['role'] ?? '') === 'ADMIN'): ?>
        <a href="dashboard.php" class="popover-item">Admin Command Portal</a>
      <?php endif; ?>
      <div class="popover-divider"></div>
      <a href="logout.php" class="popover-item danger">Sign out</a>
    </div>
  </div>
</header>
