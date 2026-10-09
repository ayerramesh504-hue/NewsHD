<?php
require_once __DIR__ . '/config/config.php';
require_once APP_ROOT . '/includes/pagination.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) {
    redirect(url(''));
}

$catStmt = db()->prepare('SELECT * FROM categories WHERE slug = ?');
$catStmt->execute([$slug]);
$category = $catStmt->fetch();

if (!$category) {
    http_response_code(404);
    redirect(url(''));
}

$activeNav = $slug;
$page = max(1, (int) ($_GET['page'] ?? 1));

$countStmt = db()->prepare('SELECT COUNT(*) FROM articles WHERE category_id = ? AND status = ?');
$countStmt->execute([$category['id'], 'published']);
$total = (int) $countStmt->fetchColumn();

$pagination = paginate($total, $page, ITEMS_PER_PAGE, url('category.php'), ['slug' => $slug]);

$stmt = db()->prepare(
    "SELECT a.*, u.name AS author_name
     FROM articles a INNER JOIN users u ON u.id = a.author_id
     WHERE a.category_id = ? AND a.status = 'published'
     ORDER BY a.published_at DESC LIMIT ? OFFSET ?"
);
$stmt->bindValue(1, $category['id'], PDO::PARAM_INT);
$stmt->bindValue(2, ITEMS_PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(3, $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();

$meta = page_meta($category['name'], $category['description'] ?? '');
require APP_ROOT . '/includes/header.php';
?>

<div class="section-title">
  <h2><?= e($category['name']) ?></h2>
  <?php if ($category['description']): ?>
  <p style="color:var(--text-muted);margin-top:16px;"><?= e($category['description']) ?></p>
  <?php endif; ?>
</div>

<?php if (empty($articles)): ?>
<div class="empty-state">
  <i class="fa-solid fa-folder-open"></i>
  <h2>No articles in this category yet</h2>
</div>
<?php else: ?>
<div class="container">
  <div class="news-grid">
    <?php foreach ($articles as $article): ?>
    <article class="news-card">
      <a href="<?= url('article.php?slug=' . e($article['slug'])) ?>">
        <img src="<?= e($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" loading="lazy">
      </a>
      <div class="news-card-body">
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
</div>
<?php endif; ?>

<?php require APP_ROOT . '/includes/footer.php'; ?>
