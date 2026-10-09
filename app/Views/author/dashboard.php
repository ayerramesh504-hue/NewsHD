<section class="dashboard-intro dashboard-intro-author">
  <div>
    <span class="dashboard-eyebrow"><i class="fa-solid fa-pen-nib"></i> Your writing space</span>
    <h1>Ready for your next story?</h1>
    <p>Manage drafts, follow your published work, and keep your newsroom moving.</p>
  </div>
  <a href="<?= url('author/articles/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Write article</a>
</section>

<div class="stats-grid author-stats-grid">
  <div class="stat-card stat-articles">
    <div class="stat-icon"><i class="fa-solid fa-newspaper"></i></div>
    <div class="stat-body"><h3><?= number_format((int) ($stats['articles'] ?? 0)) ?></h3><p>All articles</p></div>
  </div>
  <div class="stat-card stat-published">
    <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-body"><h3><?= number_format((int) ($stats['published'] ?? 0)) ?></h3><p>Published</p></div>
  </div>
  <div class="stat-card stat-draft">
    <div class="stat-icon"><i class="fa-solid fa-pen"></i></div>
    <div class="stat-body"><h3><?= number_format((int) ($stats['drafts'] ?? 0)) ?></h3><p>Drafts in progress</p></div>
  </div>
</div>

<div class="data-table-wrap">
  <div class="table-toolbar">
    <div><h3>Your Recent Articles</h3><p class="table-toolbar-note">Pick up where you left off.</p></div>
    <a href="<?= url('author/articles') ?>" class="btn btn-secondary btn-sm">View all <i class="fa-solid fa-arrow-right"></i></a>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Status</th>
          <th>Category</th>
          <th>Updated</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recent)): ?>
        <tr>
          <td colspan="4" class="text-muted">No articles yet.</td>
        </tr>
        <?php else: foreach ($recent as $article): ?>
        <tr>
          <td><a class="table-link" href="<?= url('author/articles/edit/' . (int) $article['id']) ?>"><?= e(truncate($article['title'], 70)) ?></a></td>
          <td><span class="badge badge-<?= e($article['status']) ?>"><?= e($article['status']) ?></span></td>
          <td><?= e($article['category_name'] ?? '-') ?></td>
          <td><?= format_date($article['updated_at'] ?? $article['created_at'], 'M j, Y') ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
