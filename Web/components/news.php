<section class="latest-news-section">
  <div class="news-container">
    <div class="news-header-row">
      <h2 class="news-headline">Latest News</h2>
      <a href="#about" class="view-all-link">View All →</a>
    </div>

    <div class="news-cards-grid">
      <?php 
      $newsItems = News::getLatest();
      foreach ($newsItems as $article): 
      ?>
        <article class="news-card">
          <div class="news-img-wrap">
            <img src="<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="news-img">
          </div>
          <span class="news-date"><?php echo htmlspecialchars($article['date']); ?></span>
          <h3 class="news-title"><?php echo htmlspecialchars($article['title']); ?></h3>
          <p class="news-excerpt"><?php echo htmlspecialchars($article['excerpt']); ?></p>
          <a href="<?php echo htmlspecialchars($article['link']); ?>" class="news-read-link">Read Full Story →</a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
