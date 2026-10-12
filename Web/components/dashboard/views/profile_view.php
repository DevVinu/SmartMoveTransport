<?php
// Profile view
$currentUser = Auth::user() ?? [];
$uId = (int)($currentUser['userId'] ?? $currentUser['userid'] ?? 1);
$uFirstName = htmlspecialchars($currentUser['firstName'] ?? $currentUser['firstname'] ?? 'User');
$uLastName = htmlspecialchars($currentUser['lastName'] ?? $currentUser['lastname'] ?? '');
$uEmail = htmlspecialchars($currentUser['email'] ?? '');
$uPhone = htmlspecialchars($currentUser['phone'] ?? '');
$uNic = htmlspecialchars($currentUser['nic'] ?? '');
$uRole = strtoupper($currentUser['role'] ?? 'USER');
$uStatus = strtoupper($currentUser['accountStatus'] ?? $currentUser['accountstatus'] ?? 'ACTIVE');
$uInitials = strtoupper(substr($uFirstName, 0, 1) . substr($uLastName, 0, 1));
$uLicense = htmlspecialchars($currentUser['licenseNo'] ?? $currentUser['licenseNumber'] ?? '');
$uAddress = htmlspecialchars($currentUser['address'] ?? '');
?>
<!-- Profile view -->
<section class="page-view <?php echo ($activeView ?? '') === 'profileView' ? 'active-view' : ''; ?>" id="profileView">
  <div class="view-header" style="margin-bottom:1.5rem;">
    <div class="view-title-group">
      <h1 style="font-size:1.85rem; font-weight:700; color:var(--text-white); letter-spacing:-0.03em;">Account Profile</h1>
      <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">
        Manage your personal information, contact details, and account security
      </p>
    </div>
  </div>

  <!-- Identity card -->
  <div class="dash-panel" style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem; margin-bottom:1.5rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
      <div style="display:flex; align-items:center; gap:1.25rem;">
        <div class="user-avatar" style="width:64px; height:64px; font-size:1.4rem; font-weight:700; border-radius:50%; background:var(--blue-primary); color:#FFFFFF; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <?php echo $uInitials ?: 'SM'; ?>
        </div>
        <div>
          <h2 style="font-size:1.25rem; font-weight:700; color:var(--text-white); margin-bottom:0.25rem;">
            <?php echo $uFirstName . ' ' . $uLastName; ?>
          </h2>
          <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.85rem; color:var(--text-secondary); flex-wrap:wrap;">
            <span><?php echo $uEmail; ?></span>
            <span>•</span>
            <span>ID: #<?php echo $uId; ?></span>
          </div>
        </div>
      </div>

      <div style="display:flex; align-items:center; gap:0.75rem;">
        <span class="badge-status badge-active" style="padding:0.35rem 0.85rem; border-radius:var(--radius-sm); font-size:0.75rem; font-weight:700; letter-spacing:0.04em;">
          <?php echo $uRole; ?>
        </span>
        <span style="background:rgba(16, 185, 129, 0.15); color:#34D399; border:1px solid rgba(16, 185, 129, 0.3); padding:0.35rem 0.85rem; border-radius:var(--radius-sm); font-size:0.75rem; font-weight:600;">
           <?php echo $uStatus; ?>
        </span>
      </div>
    </div>
  </div>

  <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:1.5rem; align-items:start;">
    
    <!-- Personal info -->
    <div class="dash-panel" style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
      <div style="margin-bottom:1.25rem; border-bottom:1px solid var(--border-subtle); padding-bottom:0.75rem;">
        <h3 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:0.25rem;">Personal Information</h3>
        <p style="font-size:0.8rem; color:var(--text-secondary);">Update your name and primary contact details</p>
      </div>

      <form method="POST">
        <input type="hidden" name="action" value="updateProfile">
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label class="form-label">First Name *</label>
            <input type="text" name="firstName" class="form-input" value="<?php echo $uFirstName; ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Last Name *</label>
            <input type="text" name="lastName" class="form-input" value="<?php echo $uLastName; ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-input" value="<?php echo $uEmail; ?>" disabled style="opacity:0.65; cursor:not-allowed;">
          <small style="color:var(--text-muted); font-size:0.75rem; display:block; margin-top:4px;">
            Email is your account identifier and cannot be changed directly.
          </small>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label class="form-label">Phone Number *</label>
            <input type="text" name="phone" class="form-input" value="<?php echo $uPhone; ?>" required placeholder="+94 77 123 4567">
          </div>
          <div class="form-group">
            <label class="form-label">NIC / National ID *</label>
            <input type="text" name="nic" class="form-input" value="<?php echo $uNic; ?>" required placeholder="199512345678">
          </div>
        </div>

        <?php if ($uRole === 'DRIVER'): ?>
          <div class="form-group">
            <label class="form-label">Driver License Number</label>
            <input type="text" name="licenseNo" class="form-input" value="<?php echo $uLicense; ?>" placeholder="B-1234567">
          </div>
        <?php endif; ?>

        <?php if ($uRole === 'PASSENGER'): ?>
          <div class="form-group">
            <label class="form-label">Residential Address</label>
            <input type="text" name="address" class="form-input" value="<?php echo $uAddress; ?>" placeholder="Colombo, Sri Lanka">
          </div>
        <?php endif; ?>

        <div style="display:flex; justify-content:flex-end; margin-top:1.25rem;">
          <button type="submit" class="btn-primary" style="padding:0.6rem 1.4rem;">
            Save Profile Changes
          </button>
        </div>
      </form>
    </div>

    <!-- Security & password -->
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
      <div class="dash-panel" style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
        <div style="margin-bottom:1.25rem; border-bottom:1px solid var(--border-subtle); padding-bottom:0.75rem;">
          <h3 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:0.25rem;">Security & Password</h3>
          <p style="font-size:0.8rem; color:var(--text-secondary);">Change your sign-in password</p>
        </div>

        <form method="POST">
          <input type="hidden" name="action" value="updatePassword">

          <div class="form-group">
            <label class="form-label">Current Password *</label>
            <input type="password" name="currentPassword" class="form-input" required placeholder="Enter current password">
          </div>

          <div class="form-group">
            <label class="form-label">New Password *</label>
            <input type="password" name="newPassword" class="form-input" minlength="6" required placeholder="Minimum 6 characters">
          </div>

          <div class="form-group">
            <label class="form-label">Confirm New Password *</label>
            <input type="password" name="confirmPassword" class="form-input" minlength="6" required placeholder="Re-enter new password">
          </div>

          <div style="display:flex; justify-content:flex-end; margin-top:1.25rem;">
            <button type="submit" class="btn-primary" style="padding:0.6rem 1.4rem;">
              Change Password
            </button>
          </div>
        </form>
      </div>

      <!-- Account info -->
      <div class="dash-panel" style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.25rem;">
        <h4 style="font-size:0.9rem; font-weight:600; color:var(--text-white); margin-bottom:0.5rem;">Account Information</h4>
        <div style="display:flex; flex-direction:column; gap:0.5rem; font-size:0.8rem; color:var(--text-secondary);">
          <div style="display:flex; justify-content:space-between;">
            <span>Account Type:</span>
            <strong style="color:var(--text-white);"><?php echo $uRole; ?></strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Account Status:</span>
            <strong style="color:#34D399;"><?php echo $uStatus; ?></strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Security Hash:</span>
            <span style="color:var(--text-muted); font-family:monospace;">BCrypt (Cost 10)</span>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>
