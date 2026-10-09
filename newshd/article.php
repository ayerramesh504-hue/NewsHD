<?php
require_once __DIR__ . '/config/config.php';
require_once APP_ROOT . '/includes/pagination.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) {
    redirect(url(''));
}

$stmt = db()->prepare(
    "SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name, u.avatar AS author_avatar
     FROM articles a
     INNER JOIN categories c ON c.id = a.category_id
     INNER JOIN users u ON u.id = a.author_id
     WHERE a.slug = ? AND a.status = 'published'"
);
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $meta = page_meta('Article Not Found');
    require APP_ROOT . '/includes/header.php';
    echo '<div class="empty-state"><i class="fa-solid fa-newspaper"></i><h2>Article not found</h2><p><a href="' . url('') . '">Return home</a></p></div>';
    require APP_ROOT . '/includes/footer.php';
    exit;
}

increment_article_views((int) $article['id']);
$tags = get_article_tags((int) $article['id']);

$relatedStmt = db()->prepare(
    "SELECT a.id, a.title, a.slug, a.cover_image, a.published_at
     FROM articles a WHERE a.category_id = ? AND a.id != ? AND a.status = 'published'
     ORDER BY a.published_at DESC LIMIT 3"
);
$relatedStmt->execute([$article['category_id'], $article['id']]);
$related = $relatedStmt->fetchAll();

$user = current_user();
$isBookmarked = false;
if ($user) {
    $bk = db()->prepare('SELECT id FROM bookmarks WHERE user_id = ? AND article_id = ?');
    $bk->execute([$user['id'], $article['id']]);
    $isBookmarked = (bool) $bk->fetch();
}

$meta = page_meta(
    $article['meta_title'] ?: $article['title'],
    $article['meta_description'] ?: truncate(strip_tags($article['excerpt'] ?? ''), 160),
    $article['cover_image'],
    url('article.php?slug=' . $article['slug'])
);
$extraScripts = ['components/comments.js'];
require APP_ROOT . '/includes/header.php';
?>

<section class="article-hero" style="background-image:url('<?= e($article['cover_image']) ?>')">
  <div class="article-hero-content">
    <a href="<?= url('category.php?slug=' . e($article['category_slug'])) ?>" class="category-badge"><?= e($article['category_name']) ?></a>
    <h1><?= e($article['title']) ?></h1>
    <div class="meta">
      <span><i class="fa-solid fa-user"></i> <?= e($article['author_name']) ?></span>
      <span><i class="fa-solid fa-calendar"></i> <?= format_date($article['published_at'], 'F j, Y') ?></span>
      <span><i class="fa-solid fa-eye"></i> <?= number_format($article['view_count'] + 1) ?> views</span>
    </div>
  </div>
</section>

<article class="article-content">
  <?= $article['content'] ?>

  <?php if ($tags): ?>
  <div class="article-tags">
    <?php foreach ($tags as $tag): ?>
    <a href="<?= url('search.php?q=' . urlencode($tag['name'])) ?>" class="tag-link">#<?= e($tag['name']) ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="share-buttons">
    <span>Share:</span>
    <?php $shareUrl = urlencode(url('article.php?slug=' . $article['slug'])); ?>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener" class="share-btn facebook" aria-label="Share on Facebook"><i class="fab fa-facebook-f"></i></a>
    <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= urlencode($article['title']) ?>" target="_blank" rel="noopener" class="share-btn twitter" aria-label="Share on Twitter"><i class="fab fa-twitter"></i></a>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>" target="_blank" rel="noopener" class="share-btn linkedin" aria-label="Share on LinkedIn"><i class="fab fa-linkedin-in"></i></a>
    <?php if ($user): ?>
    <button type="button" class="btn btn-primary" id="bookmarkBtn"
            data-article-id="<?= (int) $article['id'] ?>"
            data-bookmarked="<?= $isBookmarked ? '1' : '0' ?>">
      <i class="fa-<?= $isBookmarked ? 'solid' : 'regular' ?> fa-bookmark"></i>
      <?= $isBookmarked ? 'Saved' : 'Save Article' ?>
    </button>
    <?php endif; ?>
  </div>
</article>

<?php if ($related): ?>
<div class="container">
  <div class="section-title"><h2>Related Articles</h2></div>
  <div class="news-grid">
    <?php foreach ($related as $rel): ?>
    <article class="news-card">
      <a href="<?= url('article.php?slug=' . e($rel['slug'])) ?>">
        <img src="<?= e($rel['cover_image']) ?>" alt="<?= e($rel['title']) ?>" loading="lazy">
      </a>
      <div class="news-card-body">
        <h3><a href="<?= url('article.php?slug=' . e($rel['slug'])) ?>"><?= e($rel['title']) ?></a></h3>
        <div class="meta"><span><?= format_date($rel['published_at']) ?></span></div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<section class="comments-section">
  <div class="section-title"><h2>Comments</h2></div>
  <div id="commentsRoot"
       data-article-id="<?= (int) $article['id'] ?>"
       data-api="<?= api_url('comments.php') ?>"
       data-logged-in="<?= $user ? '1' : '0' ?>"
       data-user-id="<?= $user ? (int) $user['id'] : 0 ?>"
       data-login-url="<?= url('login.php') ?>"></div>
</section>

<?php require APP_ROOT . '/includes/footer.php'; ?>
