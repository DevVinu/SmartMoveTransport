        <!-- Feedback view -->
        <section class="page-view <?php echo $activeView === 'mongoView' ? 'active-view' : ''; ?>" id="mongoView">
          <div class="view-header">
            <div class="view-title-group">
              <h1>Customer Feedback & Digital Documents</h1>
              <p style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">Passenger ratings, feedback
                sentiment analysis, and vehicle compliance documentation</p>
            </div>
          </div>

          <!-- Feedback search -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem; margin-bottom:1.5rem;">
            <div
              style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:1rem;">
              <div>
                <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white);">Feedback & Sentiment Keyword
                  Search</h2>
                <p style="color:var(--text-muted); font-size:0.8rem;">Search commuter feedback for keywords like "AC",
                  "delay", "clean", or "courteous"</p>
              </div>

              <form method="GET" action="dashboard.php" style="display:flex; gap:0.5rem;">
                <input type="hidden" name="view" value="mongoView">
                <input type="text" name="keyword" class="search-input-box"
                  placeholder="Search keywords e.g. AC, delay..."
                  value="<?php echo htmlspecialchars($searchKeyword); ?>">
                <button type="submit" class="btn-primary" style="padding:0.6rem 1rem;">Search</button>
              </form>
            </div>

            <!-- Reviews grid -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1rem;">
              <?php if (empty($mongoReviews)): ?>
                <div style="grid-column:1/-1; padding:2rem; text-align:center; color:var(--text-muted);">
                  No passenger reviews found matching keyword "<?php echo htmlspecialchars($searchKeyword); ?>".
                </div>
              <?php else: ?>
                <?php foreach ($mongoReviews as $rev): ?>
                  <div
                    style="background:var(--bg-card-inner); border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:1rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                      <strong
                        style="color:var(--text-white); font-size:0.85rem;"><?php echo htmlspecialchars($rev['passengerName'] ?? 'Passenger'); ?></strong>
                      <span style="color:#FBBF24; font-weight:700; font-size:0.85rem;">★
                        <?php echo htmlspecialchars($rev['rating'] ?? 5); ?>/5</span>
                    </div>
                    <div style="font-size:0.75rem; color:var(--accent-blue); margin-bottom:0.5rem;">
                      <?php echo htmlspecialchars($rev['routeName'] ?? ''); ?>
                      (<?php echo htmlspecialchars($rev['vehicleRegNo'] ?? ''); ?>)
                    </div>
                    <p style="color:var(--text-secondary); font-size:0.82rem; line-height:1.5; margin-bottom:0.75rem;">
                      "<?php echo htmlspecialchars($rev['comment'] ?? ''); ?>"
                    </p>

                    <?php if (!empty($rev['tags'])): ?>
                      <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                        <?php foreach ((array) $rev['tags'] as $t): ?>
                          <span
                            style="background:rgba(255,255,255,0.05); color:#94A3B8; font-size:0.72rem; padding:0.15rem 0.5rem; border-radius:4px;">#<?php echo htmlspecialchars($t); ?></span>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- Top ratings -->
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
            <!-- Top drivers -->
            <div
              style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
              <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Top-Rated
                Transport Drivers</h2>
              <div style="display:flex; flex-direction:column; gap:0.75rem;">
                <?php if (empty($mongoTopRated['drivers'])): ?>
                  <p style="color:var(--text-muted); font-size:0.85rem;">No driver ratings recorded yet.</p>
                <?php else: ?>
                  <?php foreach ($mongoTopRated['drivers'] as $d): ?>
                    <div
                      style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-card-inner); padding:0.75rem 1rem; border-radius:var(--radius-md); border:1px solid var(--border-subtle);">
                      <div>
                        <strong
                          style="color:var(--text-white); font-size:0.88rem;"><?php echo htmlspecialchars($d['name']); ?></strong>
                        <div style="color:var(--text-muted); font-size:0.75rem;">
                          <?php echo htmlspecialchars($d['reviews']); ?> verified passenger reviews</div>
                      </div>
                      <span style="color:#FBBF24; font-weight:700; font-size:0.95rem;">★
                        <?php echo htmlspecialchars($d['avgRating']); ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

            <!-- Top vehicles -->
            <div
              style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
              <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Top-Rated
                Fleet Units</h2>
              <div style="display:flex; flex-direction:column; gap:0.75rem;">
                <?php if (empty($mongoTopRated['vehicles'])): ?>
                  <p style="color:var(--text-muted); font-size:0.85rem;">No vehicle ratings recorded yet.</p>
                <?php else: ?>
                  <?php foreach ($mongoTopRated['vehicles'] as $v): ?>
                    <div
                      style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-card-inner); padding:0.75rem 1rem; border-radius:var(--radius-md); border:1px solid var(--border-subtle);">
                      <div>
                        <strong
                          style="color:var(--text-white); font-size:0.88rem;"><?php echo htmlspecialchars($v['regNo']); ?></strong>
                        <span class="badge-status badge-info"
                          style="margin-left:0.4rem;"><?php echo htmlspecialchars($v['type']); ?></span>
                        <div style="color:var(--text-muted); font-size:0.75rem;">
                          <?php echo htmlspecialchars($v['reviews']); ?> passenger ratings</div>
                      </div>
                      <span style="color:#FBBF24; font-weight:700; font-size:0.95rem;">★
                        <?php echo htmlspecialchars($v['avgRating']); ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Vehicle documents -->
          <div
            style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.5rem;">
            <h2 style="font-size:1.05rem; font-weight:600; color:var(--text-white); margin-bottom:1rem;">Vehicle
              Compliance Documents & Digital Records</h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:1.25rem;">
              <?php foreach ($mongoVehDocs as $vDoc): ?>
                <div
                  style="background:var(--bg-card-inner); border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:1.25rem;">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                    <strong
                      style="color:var(--text-white); font-size:0.95rem;"><?php echo htmlspecialchars($vDoc['registrationNo'] ?? ''); ?></strong>
                    <span
                      class="badge-status badge-info"><?php echo htmlspecialchars($vDoc['vehicleType'] ?? ''); ?></span>
                  </div>
                  <p style="font-size:0.82rem; color:var(--text-secondary); margin-bottom:0.75rem;">
                    <?php echo htmlspecialchars($vDoc['model'] ?? ''); ?>
                    (<?php echo htmlspecialchars($vDoc['manufacturer'] ?? ''); ?>)</p>

                  <!-- Documents list -->
                  <div style="margin-bottom:1rem;">
                    <span
                      style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Attached
                      Compliance Scans:</span>
                    <div style="margin-top:0.4rem; display:flex; flex-direction:column; gap:0.4rem;">
                      <?php foreach ((array) ($vDoc['documents'] ?? []) as $d): ?>
                        <?php $docItem = (array) $d; ?>
                        <div
                          style="background:rgba(255,255,255,0.03); padding:0.4rem 0.6rem; border-radius:4px; font-size:0.78rem; display:flex; justify-content:space-between; align-items:center;">
                          <span style="color:#93C5FD;">📄
                            <?php echo htmlspecialchars($docItem['title'] ?? 'Document'); ?></span>
                          <span
                            style="color:var(--text-muted); font-size:0.72rem;"><?php echo htmlspecialchars($docItem['fileSize'] ?? ''); ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <!-- Gallery -->
                  <?php if (!empty($vDoc['gallery'])): ?>
                    <div>
                      <span
                        style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Vehicle
                        Photo Gallery:</span>
                      <div style="display:flex; gap:0.5rem; margin-top:0.4rem;">
                        <?php foreach ((array) $vDoc['gallery'] as $img): ?>
                          <?php $im = (array) $img; ?>
                          <img src="<?php echo htmlspecialchars($im['url'] ?? ''); ?>"
                            alt="<?php echo htmlspecialchars($im['view'] ?? 'Photo'); ?>"
                            style="width:75px; height:50px; object-fit:cover; border-radius:4px; border:1px solid var(--border-subtle);"
                            title="<?php echo htmlspecialchars($im['view'] ?? ''); ?>">
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
