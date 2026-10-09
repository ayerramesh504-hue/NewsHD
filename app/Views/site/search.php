<?= view('layout/header') ?>

<div class="page-hero">
  <div class="container">
    <h1>Search News</h1>
    <p>Find articles by title, tag, category, or author</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <form method="get" action="<?= url('search') ?>" class="form-card" style="max-width:640px;margin:0 auto 32px;">
      <div class="form-group">
        <label for="searchQuery">Search by title, tag, category, or author</label>
        <input type="search" id="searchQuery" name="q" value="<?= e($query) ?>" placeholder="Enter keywords..." required>
      </div>
      <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>

    <?php if ($query === ''): ?>
    <div class="empty-state"><p>Enter a search term to find articles.</p></div>
    <?php elseif (empty($articles)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-magnifying-glass"></i>
      <h2>No results for "<?= e($query) ?>"</h2>
      <p>Try different keywords.</p>
    </div>
    <?php else: ?>
    <p class="text-center mb-3" style="color:var(--text-muted);">
      Found <?= number_format($total) ?> result<?= $total !== 1 ? 's' : '' ?> for "<?= e($query) ?>"
    </p>
    <div class="list-rows">
      <?php foreach ($articles as $article): ?>
      <article class="row-card">
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
          <p><?= e(truncate(strip_tags($article['excerpt'] ?? ''), 140)) ?></p>
          <a href="<?= url('article/' . e($article['slug'])) ?>" class="read-more">Read More <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?= view('layout/footer') ?>
