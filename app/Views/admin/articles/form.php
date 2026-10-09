<?php
$editId = $article ? (int) $article['id'] : 0;
$vals = array_merge([
    'title' => '', 'slug' => '', 'content' => '',
    'category_id' => 0, 'status' => 'draft', 'is_featured' => 0,
    'is_breaking' => 0, 'cover_image' => '', 'tags' => '',
], $old);
?>
<div class="form-card-wide">
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="<?= url(can_publish_articles() ? 'admin/articles/save' : 'author/articles/save') ?>" enctype="multipart/form-data" id="articleForm">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $editId ?>">

    <div class="form-row">
      <div class="form-group form-group-wide">
        <label for="title">Title *</label>
        <input type="text" id="title" name="title" required value="<?= e($vals['title']) ?>" placeholder="Article headline">
      </div>
      <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" value="<?= e($vals['slug']) ?>" placeholder="auto-generated">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="category_id">Category *</label>
        <select id="category_id" name="category_id" required>
          <option value="">Select category</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= (int) $cat['id'] ?>" <?= (int) $vals['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="status">Status</label>
        <?php if (! can_publish_articles()): ?>
        <input type="hidden" name="status" value="pending">
        <div class="status-review-callout"><i class="fa-solid fa-shield-check"></i><span><strong>Pending review</strong><small>Your article will be reviewed before it appears publicly.</small></span></div>
        <?php else: ?>
        <select id="status" name="status">
          <option value="draft" <?= $vals['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
          <option value="pending" <?= $vals['status'] === 'pending' ? 'selected' : '' ?>>Pending review</option>
          <?php if (can_publish_articles()): ?>
          <option value="published" <?= $vals['status'] === 'published' ? 'selected' : '' ?>>Published</option>
          <option value="scheduled" <?= $vals['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
          <?php endif; ?>
        </select>
        <?php endif; ?>
        <?php if (! can_publish_articles()): ?>
        <small>Save as draft — an admin will review before publishing.</small>
        <?php endif; ?>
      </div>
    </div>

    <div class="form-group">
      <label for="content">Content *</label>
      <textarea id="content" name="content" class="content-textarea" rows="16" required placeholder="Write your story in plain text — leave a blank line between paragraphs."><?= e($vals['content'] ?? '') ?></textarea>
      <div class="content-hint" id="contentHint"><i class="fa-solid fa-list-ol"></i> <span id="contentWordCount">0</span> words &middot; no HTML needed &middot; blank line = new paragraph &middot; <code>#</code> heading &middot; <code>-</code> bullet list</div>
    </div>

    <div class="form-row">
    <div class="form-group cover-upload-group">
      <label for="cover_file">Cover Image</label>
      <div class="cover-dropzone" id="coverDropzone" tabindex="0">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <strong>Upload a cover image from your device</strong>
        <span>Drop an image here or click to browse · JPG, PNG, WebP or GIF</span>
        <input type="file" id="cover_file" name="cover_file" accept="image/*" hidden>
        <img id="coverPreview" alt="Cover preview" <?= $vals['cover_image'] ? 'src="' . e($vals['cover_image']) . '"' : 'hidden' ?>>
        <button type="button" class="btn btn-secondary btn-sm" id="coverBrowseBtn"><i class="fa-solid fa-folder-open"></i> Choose from Device</button>
      </div>
      <input type="hidden" name="cover_image" id="cover_image" value="<?= e($vals['cover_image']) ?>">
      <small class="cover-hint"><?= $vals['cover_image'] ? 'Current image shown above — pick a new one to replace it.' : 'No cover image set yet.' ?></small>
    </div>
    </div>

    <div class="form-group">
      <label for="tags">Tags (comma separated)</label>
      <input type="text" id="tags" name="tags" value="<?= e($vals['tags']) ?>" placeholder="politics, economy, election">
    </div>

    <div class="form-row checkbox-row">
      <label class="form-check">
        <input type="checkbox" name="is_featured" value="1" <?= ! empty($vals['is_featured']) ? 'checked' : '' ?>> Feature on homepage
      </label>
      <label class="form-check">
        <input type="checkbox" name="is_breaking" value="1" <?= ! empty($vals['is_breaking']) ? 'checked' : '' ?>> Breaking news
      </label>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> <?= can_publish_articles() ? 'Save Article' : 'Submit for Review' ?></button>
      <a href="<?= url(can_publish_articles() ? 'admin/articles' : 'author/articles') ?>" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
