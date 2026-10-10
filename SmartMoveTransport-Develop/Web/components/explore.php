<section class="uber-explore-section" id="explore">
  <h2 class="explore-headline">Explore what you can do <span class="blue-text">with SmartMove</span></h2>

  <div class="explore-cards-grid">
    <?php 
    $vehicles = Vehicle::getAll();
    foreach ($vehicles as $item): 
    ?>
      <article class="explore-card">
        <div class="card-left-info">
          <div>
            <h3 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h3>
            <p class="card-desc"><?php echo htmlspecialchars($item['description']); ?></p>
          </div>
          <a href="pages/login.php" class="btn-details-pill">Details</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
