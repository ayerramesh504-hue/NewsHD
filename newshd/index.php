<?php
require_once __DIR__ . '/config/config.php';
require_once APP_ROOT . '/includes/pagination.php';

$activeNav = 'home';

$breakingStmt = db()->query(
    "SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
     FROM articles a
     INNER JOIN categories c ON c.id = a.category_id
     INNER JOIN users u ON u.id = a.author_id
     WHERE a.status = 'published' AND a.is_breaking = 1
     ORDER BY a.published_at DESC LIMIT 5"
);
$breaking = $breakingStmt->fetchAll();

if (empty($breaking)) {
    $breakingStmt = db()->query(
        "SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
         FROM articles a
         INNER JOIN categories c ON c.id = a.category_id
         INNER JOIN users u ON u.id = a.author_id
         WHERE a.status = 'published' AND a.is_featured = 1
         ORDER BY a.published_at DESC LIMIT 5"
    );
    $breaking = $breakingStmt->fetchAll();
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$countStmt = db()->query("SELECT COUNT(*) FROM articles WHERE status = 'published'");
$total = (int) $countStmt->fetchColumn();
$pagination = paginate($total, $page, ITEMS_PER_PAGE, url('index.php'));

$articlesStmt = db()->prepare(
    "SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
     FROM articles a
     INNER JOIN categories c ON c.id = a.category_id
     INNER JOIN users u ON u.id = a.author_id
     WHERE a.status = 'published'
     ORDER BY a.published_at DESC
     LIMIT ? OFFSET ?"
);
$articlesStmt->bindValue(1, ITEMS_PER_PAGE, PDO::PARAM_INT);
$articlesStmt->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
$articlesStmt->execute();
$articles = $articlesStmt->fetchAll();

$trendingStmt = db()->query(
    "SELECT a.id, a.title, a.slug, a.view_count, c.name AS category_name
     FROM articles a INNER JOIN categories c ON c.id = a.category_id
     WHERE a.status = 'published' ORDER BY a.view_count DESC LIMIT 5"
);
$trending = $trendingStmt->fetchAll();

$categorySections = [];
foreach (get_categories() as $cat) {
    $stmt = db()->prepare(
        "SELECT a.id, a.title, a.slug, a.cover_image, a.published_at
         FROM articles a WHERE a.category_id = ? AND a.status = 'published'
         ORDER BY a.published_at DESC LIMIT 4"
    );
    $stmt->execute([$cat['id']]);
    $items = $stmt->fetchAll();
    if ($items) {
        $categorySections[] = ['category' => $cat, 'articles' => $items];
    }
}

$meta = page_meta(get_setting('site_name', APP_NAME), get_setting('site_description'));
require APP_ROOT . '/includes/header.php';
?>

<?php if ($breaking): ?>
<section class="hero-slider" aria-label="Breaking news">
  <?php foreach ($breaking as $i => $slide): ?>
  <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>"
       style="background-image:url('<?= e($slide['cover_image']) ?>')">
    <div class="hero-slide-content">
      <?php if ($slide['is_breaking']): ?><span class="hero-badge">BREAKING</span><?php endif; ?>
      <span class="category-badge"><?= e($slide['category_name']) ?></span>
      <h2><a href="<?= url('article.php?slug=' . e($slide['slug'])) ?>"><?= e($slide['title']) ?></a></h2>
      <p><?= e(truncate(strip_tags($slide['excerpt'] ?? ''), 160)) ?></p>
      <a href="<?= url('article.php?slug=' . e($slide['slug'])) ?>" class="btn btn-primary">Read Full Story</a>
    </div>
  </div>
  <?php endforeach; ?>
  <div class="hero-dots" id="heroDots">
    <?php foreach ($breaking as $i => $slide): ?>
    <button class="hero-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>"></button>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<div class="page-layout">
  <div class="main-column">
    <div class="section-title"><h2>Latest News</h2></div>
    <div class="news-grid">
      <?php foreach ($articles as $article): ?>
      <article class="news-card">
        <a href="<?= url('article.php?slug=' . e($article['slug'])) ?>">
          <img src="<?= e($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" loading="lazy">
        </a>
        <div class="news-card-body">
          <a href="<?= url('category.php?slug=' . e($article['category_slug'])) ?>" class="category-badge"><?= e($article['category_name']) ?></a>
          <h3><a href="<?= url('article.php?slug=' . e($article['slug'])) ?>"><?= e($article['title']) ?></a></h3>
          <div class="meta">
            <span><i class="fa-solid fa-user"></i> <?= e($article['author_name']) ?></span>
            <span><i class="fa-solid fa-clock"></i> <?= format_date($article['published_at']) ?></span>
          </div>
          <p><?= e(truncate(strip_tags($article['excerpt'] ?? ''), 120)) ?></p>
          <a href="<?= url('article.php?slug=' . e($article['slug'])) ?>" class="read-more">Read More &rarr;</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?= render_pagination($pagination) ?>

    <?php foreach ($categorySections as $section): ?>
    <div class="section-title"><h2><?= e($section['category']['name']) ?></h2></div>
    <div class="news-grid">
      <?php foreach ($section['articles'] as $article): ?>
      <article class="news-card">
        <a href="<?= url('article.php?slug=' . e($article['slug'])) ?>">
          <img src="<?= e($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" loading="lazy">
        </a>
        <div class="news-card-body">
          <h3><a href="<?= url('article.php?slug=' . e($article['slug'])) ?>"><?= e($article['title']) ?></a></h3>
          <div class="meta"><span><?= format_date($article['published_at']) ?></span></div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;margin-bottom:40px;">
      <a href="<?= url('category.php?slug=' . e($section['category']['slug'])) ?>" class="btn btn-primary">View All <?= e($section['category']['name']) ?></a>
    </p>
    <?php endforeach; ?>
  </div>

  <aside class="sidebar">
    <div class="sidebar-widget">
      <h3><i class="fa-solid fa-fire"></i> Most Read</h3>
      <?php foreach ($trending as $i => $item): ?>
      <div class="trending-item">
        <span class="trending-num"><?= $i + 1 ?></span>
        <div>
          <a href="<?= url('article.php?slug=' . e($item['slug'])) ?>"><?= e($item['title']) ?></a>
          <small><?= e($item['category_name']) ?> &middot; <?= number_format($item['view_count']) ?> views</small>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </aside>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
