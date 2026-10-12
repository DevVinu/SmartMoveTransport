        <!-- Settings view -->
        <section class="page-view <?php echo $activeView === 'settingsView' ? 'active-view' : ''; ?>" id="settingsView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Database Infrastructure & Backups</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Oracle XE and MongoDB
                operational parameters & Data Pump backups</p>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <!-- Oracle status -->
            <div
              style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="color:var(--text-white); font-size:1.05rem;">Oracle Database 19c/21c XE</h3>
                <span class="badge-status <?php echo $oracleUp ? 'badge-active' : 'badge-urgent'; ?>">
                  <?php echo $oracleUp ? 'ONLINE' : 'OFFLINE'; ?>
                </span>
              </div>
              <p style="color:var(--text-secondary); font-size:0.85rem; line-height:1.6;">
                <strong>Container:</strong> XEPDB1<br>
                <strong>Schema Owner:</strong> smartmoveOwner<br>
                <strong>Port:</strong> 1521<br>
                <strong>Status:</strong> <?php echo htmlspecialchars($dbHealth['oracle']['message'] ?? 'N/A'); ?>
              </p>
            </div>

            <!-- Mongo status -->
            <div
              style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="color:var(--text-white); font-size:1.05rem;">MongoDB Community Server</h3>
                <span class="badge-status <?php echo $mongoUp ? 'badge-active' : 'badge-warning'; ?>">
                  <?php echo $mongoUp ? 'CONNECTED' : 'DISCONNECTED'; ?>
                </span>
              </div>
              <p style="color:var(--text-secondary); font-size:0.85rem; line-height:1.6;">
                <strong>Database:</strong> smartmove_db<br>
                <strong>Port:</strong> 27017<br>
                <strong>Collections:</strong> passenger_reviews, vehicle_documents, travel_announcements<br>
                <strong>Status:</strong> <?php echo htmlspecialchars($dbHealth['mongodb']['message'] ?? 'N/A'); ?>
              </p>
            </div>
          </div>

          <!-- Backup commands -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem; margin-top:1.5rem;">
            <h3 style="color:var(--text-white); font-size:1.05rem; margin-bottom:0.75rem;">Database Backup Commands
              (Data Pump)</h3>
            <p style="color:var(--text-secondary); font-size:0.85rem; margin-bottom:1rem;">Run these in Windows Command
              Prompt (cmd) to generate physical dump archives:</p>

            <pre
              style="background:var(--bg-canvas); border:1px solid var(--border-subtle); padding:1rem; border-radius:var(--radius-md); font-family:monospace; font-size:0.8rem; color:#93C5FD; overflow-x:auto;">
expdp smartmoveOwner/Owner123@localhost:1521/XEPDB1 DIRECTORY=smartmoveBackupDir DUMPFILE=smartmove_full.dmp LOGFILE=backup.log SCHEMAS=smartmoveOwner</pre>
          </div>
        </section>
