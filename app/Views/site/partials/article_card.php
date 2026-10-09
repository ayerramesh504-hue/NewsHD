<article class="news-card">
  <a href="<?= url('article/' . e($article['slug'])) ?>" class="thumb">
    <img src="<?= e($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" loading="lazy">
  </a>
  <div class="news-card-body">
    <a href="<?= url('category/' . e($article['category_slug'])) ?>" class="category-badge"><?= e($article['category_name']) ?></a>
    <h3><a href="<?= url('article/' . e($article['slug'])) ?>"><?= e($article['title']) ?></a></h3>
    <div class="meta">
      <span class="author"><i class="fa-solid fa-user"></i> <?= e($article['author_name']) ?></span>
      <span><i class="fa-solid fa-clock"></i> <?= format_date($article['published_at']) ?></span>
    </div>
    <p><?= e(truncate(strip_tags($article['excerpt'] ?? ''), 120)) ?></p>
    <a href="<?= url('article/' . e($article['slug'])) ?>" class="read-more">Read More <i class="fa-solid fa-arrow-right"></i></a>
  </div>
</article>
