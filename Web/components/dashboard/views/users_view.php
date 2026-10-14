        <!-- Users view -->
        <section class="page-view <?php echo $activeView === 'usersView' ? 'active-view' : ''; ?>" id="usersView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>User & Access Management</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Directory of all system
                users, drivers, staff, and passengers</p>
            </div>
            <div class="view-actions">
              <button class="btn-primary" type="button" onclick="openModal('modalAddUser')">
                + Add New User
              </button>
            </div>
          </div>

          <!-- Filters -->
          <div class="filter-pills-bar">
            <span class="filter-pill active" onclick="filterUsers('ALL', this)">All Roles</span>
            <span class="filter-pill" onclick="filterUsers('ADMIN', this)">Admins</span>
            <span class="filter-pill" onclick="filterUsers('STAFF', this)">Staff / Dispatch</span>
            <span class="filter-pill" onclick="filterUsers('DRIVER', this)">Drivers</span>
            <span class="filter-pill" onclick="filterUsers('PASSENGER', this)">Passengers</span>
          </div>

          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
            <div style="overflow-x:auto;">
              <table class="tasks-table" id="usersTable" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">USER
                      ID</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">FULL
                      NAME</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">EMAIL
                      ADDRESS</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">PHONE
                      NUMBER</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">NIC
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">ROLE
                    </th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      STATUS</th>
                    <th style="padding:0.75rem 1rem; text-align:left; color:var(--text-muted); font-size:0.75rem;">
                      ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($usersList)): ?>
                    <tr>
                      <td colspan="8" style="padding:2rem; text-align:center; color:var(--text-muted);">No users found.
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($usersList as $u): ?>
                      <?php
                      $uRole = strtoupper($u['role'] ?? $u['ROLE'] ?? 'PASSENGER');
                      $uStatus = strtoupper($u['accountstatus'] ?? $u['ACCOUNTSTATUS'] ?? 'ACTIVE');
                      $uId = (int) ($u['userid'] ?? $u['USERID'] ?? 0);
                      $fullName = $u['fullname'] ?? $u['FULLNAME'] ?? ($u['firstname'] . ' ' . $u['lastname']);
                      $myId = (int) ($currentUser['userId'] ?? $currentUser['userid'] ?? 0);
                      ?>
                      <tr class="user-row" data-role="<?php echo $uRole; ?>"
                        style="border-bottom:1px solid var(--border-muted);">
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--accent-blue);">
                          #USR-<?php echo $uId; ?></td>
                        <td style="padding:0.85rem 1rem;">
                          <div style="font-weight:600; color:var(--text-white);"><?php echo htmlspecialchars($fullName); ?></div>
                          <?php if ($uRole === 'ADMIN'): ?>
                            <div style="font-size:0.75rem; color:var(--accent-blue); margin-top:2px;">
                              Code: <strong><?php echo htmlspecialchars($u['admincode'] ?? 'ADM-001'); ?></strong> • <?php echo htmlspecialchars($u['privilegelevel'] ?? 'OPS_ADMIN'); ?> (<?php echo htmlspecialchars($u['department'] ?? 'Operations'); ?>)
                            </div>
                          <?php elseif ($uRole === 'DRIVER'): ?>
                            <div style="font-size:0.75rem; color:#FBBF24; margin-top:2px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                              <span>License: <strong><?php echo htmlspecialchars($u['licenseno'] ?? 'N/A'); ?></strong> (<?php echo htmlspecialchars($u['licensetype'] ?? 'COACH'); ?>)</span>
                              <?php if (!empty($u['licensedocument'])): ?>
                                <a href="../<?php echo htmlspecialchars($u['licensedocument']); ?>" target="_blank" style="display:inline-flex; align-items:center; gap:3px; background:rgba(56,189,248,0.15); color:#38BDF8; padding:1px 6px; border-radius:4px; text-decoration:none; font-size:0.7rem; border:1px solid rgba(56,189,248,0.3);" title="View Driver License Document (PDF)">
                                  📄 View License PDF
                                </a>
                              <?php else: ?>
                                <span style="font-size:0.7rem; color:var(--text-muted);">No PDF</span>
                              <?php endif; ?>
                            </div>
                          <?php elseif ($uRole === 'STAFF'): ?>
                            <div style="font-size:0.75rem; color:#34D399; margin-top:2px;">
                              Position: <strong><?php echo htmlspecialchars($u['position'] ?? 'Operations'); ?></strong> • Duty: <?php echo htmlspecialchars($u['staffstatus'] ?? 'ACTIVE'); ?>
                            </div>
                          <?php else: ?>
                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">
                              Address: <?php echo htmlspecialchars($u['address'] ?? 'Sri Lanka'); ?> • Pay: <?php echo htmlspecialchars($u['preferredpaymentmethod'] ?? 'CARD'); ?>
                            </div>
                          <?php endif; ?>
                        </td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($u['email'] ?? $u['EMAIL']); ?></td>
                        <td style="padding:0.85rem 1rem; color:var(--text-secondary);">
                          <?php echo htmlspecialchars($u['phone'] ?? $u['PHONE']); ?></td>
                        <td style="padding:0.85rem 1rem; font-family:monospace; color:var(--text-muted);">
                          <?php echo htmlspecialchars($u['nic'] ?? $u['NIC']); ?></td>
                        <td style="padding:0.85rem 1rem;">
                          <span
                            class="badge-status <?php echo $uRole === 'ADMIN' ? 'badge-urgent' : ($uRole === 'DRIVER' ? 'badge-warning' : ($uRole === 'STAFF' ? 'badge-active' : 'badge-info')); ?>">
                            <?php echo $uRole; ?>
                          </span>
                        </td>
                        <td style="padding:0.85rem 1rem;">
                          <span class="badge-status <?php echo $uStatus === 'ACTIVE' ? 'badge-active' : 'badge-urgent'; ?>">
                            <?php echo $uStatus; ?>
                          </span>
                        </td>
                        <td style="padding:0.85rem 1rem; white-space:nowrap;">
                          <!-- Edit button -->
                          <button type="button" class="action-btn-sm" style="color:var(--accent-blue); margin-right:4px;"
                            onclick='openEditUserModal(<?php echo htmlspecialchars(json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, "UTF-8"); ?>)'
                            title="Edit User">
                            Edit
                          </button>

                          <!-- Toggle status -->
                          <?php if ($uId !== $myId): ?>
                            <form method="POST" action="dashboard.php" style="display:inline;">
                              <input type="hidden" name="action" value="toggleUserStatus">
                              <input type="hidden" name="userId" value="<?php echo $uId; ?>">
                              <?php if ($uStatus === 'ACTIVE'): ?>
                                <input type="hidden" name="newStatus" value="SUSPENDED">
                                <button type="submit" class="action-btn-sm" style="color:#F59E0B; margin-right:4px;"
                                  title="Suspend User">Suspend</button>
                              <?php else: ?>
                                <input type="hidden" name="newStatus" value="ACTIVE">
                                <button type="submit" class="action-btn-sm" style="color:#34D399; margin-right:4px;"
                                  title="Activate User">Activate</button>
                              <?php endif; ?>
                            </form>

                            <!-- Delete button -->
                            <form method="POST" action="dashboard.php" style="display:inline;" onsubmit="return confirm('Permanently delete user #<?php echo $uId; ?> (<?php echo htmlspecialchars(addslashes($fullName)); ?>)? All linked actor records will be removed.');">
                              <input type="hidden" name="action" value="deleteUser">
                              <input type="hidden" name="userId" value="<?php echo $uId; ?>">
                              <button type="submit" class="action-btn-sm action-btn-danger" style="color:#EF4444;" title="Delete User">Delete</button>
                            </form>
                          <?php else: ?>
                            <span style="font-size:0.75rem; color:var(--text-muted);">(You)</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>
