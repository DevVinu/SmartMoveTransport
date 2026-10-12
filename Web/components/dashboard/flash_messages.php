        <!-- Flash banners -->
        <?php if (!empty($flashSuccess)): ?>
          <div
            style="background:rgba(16, 185, 129, 0.15); border:1px solid rgba(16, 185, 129, 0.3); border-radius:var(--radius-md); padding:0.75rem 1.25rem; margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; color:#34D399; font-size:0.85rem;">
            <span> <?php echo htmlspecialchars($flashSuccess); ?></span>
            <button type="button" onclick="this.parentElement.style.display='none'"
              style="background:none; border:none; color:#34D399; cursor:pointer; font-size:1.1rem;">&times;</button>
          </div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
          <div
            style="background:rgba(239, 68, 68, 0.15); border:1px solid rgba(239, 68, 68, 0.3); border-radius:var(--radius-md); padding:0.75rem 1.25rem; margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; color:#F87171; font-size:0.85rem;">
            <span> <?php echo htmlspecialchars($flashError); ?></span>
            <button type="button" onclick="this.parentElement.style.display='none'"
              style="background:none; border:none; color:#F87171; cursor:pointer; font-size:1.1rem;">&times;</button>
          </div>
        <?php endif; ?>
