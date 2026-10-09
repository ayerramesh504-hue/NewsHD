<?php
$listBase = can_publish_articles() ? 'admin/articles' : 'author/articles';
$editBase = $listBase . '/edit';
?>
<div class="data-table-wrap">
  <div class="table-toolbar">
    <form method="get" action="<?= url($listBase) ?>" class="toolbar-form">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search articles..." aria-label="Search articles">
      <select name="status" aria-label="Filter by status">
        <option value="">All statuses</option>
        <?php foreach (['draft', 'pending', 'published', 'scheduled'] as $s): ?>
        <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-secondary btn-sm"><i class="fa-solid fa-filter"></i> Filter</button>
    </form>
    <a href="<?= url($listBase . '/create') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Article</a>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Category</th>
          <th>Author</th>
          <th>Status</th>
          <th>Featured</th>
          <th>Breaking</th>
          <th>Views</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($articles)): ?>
        <tr><td colspan="9" class="empty-cell">No articles found.</td></tr>
        <?php else: foreach ($articles as $a): ?>
        <tr>
          <td><a href="<?= url($editBase . '/' . (int) $a['id']) ?>" class="table-link"><?= e(truncate($a['title'], 60)) ?></a></td>
          <td><span class="badge badge-cat"><?= e($a['category_name']) ?></span></td>
          <td><?= e($a['author_name']) ?></td>
          <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
          <td><?= $a['is_featured'] ? '<i class="fa-solid fa-star" style="color:var(--admin-accent);"></i>' : '&mdash;' ?></td>
          <td><?= $a['is_breaking'] ? '<i class="fa-solid fa-bolt" style="color:var(--admin-accent);"></i>' : '&mdash;' ?></td>
          <td><?= number_format($a['view_count']) ?></td>
          <td><?= format_date($a['created_at'], 'M j, Y') ?></td>
          <td class="row-actions">
            <?php if (can_publish_articles()): ?>
            <a href="<?= url('admin/articles/preview/' . (int) $a['id']) ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener" title="Preview article"><i class="fa-solid fa-eye"></i> Preview</a>
            <?php else: ?>
            <a href="<?= url($editBase . '/' . (int) $a['id']) ?>" class="btn btn-secondary btn-sm" title="Edit"><i class="fa-solid fa-pen"></i> Edit</a>
            <?php endif; ?>
            <?php if (can_publish_articles()): ?>
            <?php if ($a['status'] !== 'published'): ?>
            <form method="post" action="<?= url($listBase . '/status/' . (int) $a['id']) ?>" class="inline-form" title="<?= $a['status'] === 'pending' ? 'Approve and publish' : 'Publish' ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="status" value="published">
              <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Publish</button>
            </form>
            <?php else: ?>
            <form method="post" action="<?= url($listBase . '/status/' . (int) $a['id']) ?>" class="inline-form" title="Unpublish">
              <?= csrf_field() ?>
              <input type="hidden" name="status" value="draft">
              <button type="submit" class="btn btn-secondary btn-sm"><i class="fa-solid fa-eye-slash"></i></button>
            </form>
            <?php endif; ?>
            <?php endif; ?>
            <form method="post" action="<?= url($listBase . '/delete/' . (int) $a['id']) ?>" class="inline-form" onsubmit="return confirm('Delete this article?');">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?= render_pagination($pagination) ?>
</div>
