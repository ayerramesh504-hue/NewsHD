<div class="admin-split">
  <div class="data-table-wrap">
    <div class="table-toolbar"><h3>Categories</h3></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Slug</th>
            <th>Description</th>
            <th>Order</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categories)): ?>
          <tr><td colspan="5" class="empty-cell">No categories yet.</td></tr>
          <?php else: foreach ($categories as $cat): ?>
          <tr>
            <td class="strong"><?= e($cat['name']) ?></td>
            <td><code><?= e($cat['slug']) ?></code></td>
            <td><?= e(truncate($cat['description'] ?? '', 60)) ?></td>
            <td><?= (int) $cat['sort_order'] ?></td>
            <td class="row-actions">
              <form method="post" action="<?= url('admin/categories/delete/' . (int) $cat['id']) ?>" class="inline-form" onsubmit="return confirm('Delete this category? Articles in it will be orphaned.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="form-card-wide">
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <h3>Add / Edit Category</h3>
    <form method="post" action="<?= url('admin/categories/save') ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" required value="<?= e($old['name'] ?? '') ?>" placeholder="Category name">
      </div>
      <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" value="<?= e($old['slug'] ?? '') ?>" placeholder="auto-generated">
      </div>
      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3"><?= e($old['description'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label for="sort_order">Sort Order</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($old['sort_order'] ?? 0) ?>">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Save Category</button>
    </form>
  </div>
</div>
