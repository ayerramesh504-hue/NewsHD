<?php
require_once __DIR__ . '/config/config.php';
require_once APP_ROOT . '/includes/pagination.php';

$query = sanitize($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$articles = [];
$total = 0;

if ($query !== '') {
    $like = '%' . $query . '%';
    $countStmt = db()->prepare(
        "SELECT COUNT(DISTINCT a.id) FROM articles a
         LEFT JOIN users u ON u.id = a.author_id
         LEFT JOIN categories c ON c.id = a.category_id
         LEFT JOIN article_tags at ON at.article_id = a.id
         LEFT JOIN tags t ON t.id = at.tag_id
         WHERE a.status = 'published' AND (
           a.title LIKE ? OR a.excerpt LIKE ? OR u.name LIKE ?
           OR c.name LIKE ? OR t.name LIKE ?
         )"
    );
    $countStmt->execute([$like, $like, $like, $like, $like]);
    $total = (int) $countStmt->fetchColumn();

    $pagination = paginate($total, $page, ITEMS_PER_PAGE, url('search.php'), ['q' => $query]);

    $stmt = db()->prepare(
        "SELECT DISTINCT a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
         FROM articles a
         INNER JOIN categories c ON c.id = a.category_id
         INNER JOIN users u ON u.id = a.author_id
         LEFT JOIN article_tags at ON at.article_id = a.id
         LEFT JOIN tags t ON t.id = at.tag_id
         WHERE a.status = 'published' AND (
           a.title LIKE ? OR a.excerpt LIKE ? OR u.name LIKE ?
           OR c.name LIKE ? OR t.name LIKE ?
         )
         ORDER BY a.published_at DESC LIMIT ? OFFSET ?"
    );
    $stmt->bindValue(1, $like, PDO::PARAM_STR);
    $stmt->bindValue(2, $like, PDO::PARAM_STR);
    $stmt->bindValue(3, $like, PDO::PARAM_STR);
    $stmt->bindValue(4, $like, PDO::PARAM_STR);
    $stmt->bindValue(5, $like, PDO::PARAM_STR);
    $stmt->bindValue(6, ITEMS_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(7, $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll();
} else {
    $pagination = paginate(0, 1, ITEMS_PER_PAGE, url('search.php'));
}

$meta = page_meta($query ? 'Search: ' . $query : 'Search', 'Search news articles');
require APP_ROOT . '/includes/header.php';
?>

<div class="container" style="padding:40px 0;">
  <div class="section-title"><h2>Search Results</h2></div>

  <form method="get" action="<?= url('search.php') ?>" class="form-card" style="max-width:600px;margin:0 auto 40px;">
    <div class="form-group">
      <label for="searchQuery">Search by title, tag, category, or author</label>
      <input type="search" id="searchQuery" name="q" value="<?= e($query) ?>" placeholder="Enter keywords..." required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Search</button>
  </form>

  <?php if ($query === ''): ?>
  <div class="empty-state"><p>Enter a search term to find articles.</p></div>
  <?php elseif (empty($articles)): ?>
  <div class="empty-state">
    <i class="fa-solid fa-search"></i>
    <h2>No results for "<?= e($query) ?>"</h2>
  </div>
  <?php else: ?>
  <p style="text-align:center;color:var(--text-muted);margin-bottom:24px;">
    Found <?= number_format($total) ?> result<?= $total !== 1 ? 's' : '' ?> for "<?= e($query) ?>"
  </p>
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
          <span><?= e($article['author_name']) ?></span>
          <span><?= format_date($article['published_at']) ?></span>
        </div>
        <p><?= e(truncate(strip_tags($article['excerpt'] ?? ''), 120)) ?></p>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?= render_pagination($pagination) ?>
  <?php endif; ?>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
