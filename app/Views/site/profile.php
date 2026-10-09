<?= view('layout/header') ?>

<div class="section">
  <div class="container profile-layout">
    <aside class="profile-sidebar">
      <img src="<?= e($user['avatar'] ? url($user['avatar']) : asset('images/profile.svg')) ?>"
           alt="Avatar" class="avatar-preview">
      <strong><?= e($user['name']) ?></strong>
      <p><?= e($user['email']) ?></p>
      <nav>
        <a href="?tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>"><i class="fa-solid fa-user"></i> Edit Profile</a>
        <a href="?tab=password" class="<?= $tab === 'password' ? 'active' : '' ?>"><i class="fa-solid fa-lock"></i> Change Password</a>
        <a href="?tab=bookmarks" class="<?= $tab === 'bookmarks' ? 'active' : '' ?>"><i class="fa-solid fa-bookmark"></i> Saved Articles</a>
      </nav>
    </aside>

    <div class="profile-content">
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <?php if ($tab === 'profile'): ?>
      <h2>Edit Profile</h2>
      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="profile">
        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" required value="<?= e($user['name']) ?>">
        </div>
        <div class="form-group">
          <label for="bio">Bio</label>
          <textarea id="bio" name="bio" rows="4"><?= e($user['bio'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label for="avatar">Avatar</label>
          <input type="file" id="avatar" name="avatar" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
      </form>

      <?php elseif ($tab === 'password'): ?>
      <h2>Change Password</h2>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="password">
        <div class="form-group">
          <label for="current_password">Current Password</label>
          <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        </div>
        <div class="form-group">
          <label for="new_password">New Password</label>
          <input type="password" id="new_password" name="new_password" required minlength="8"
                 pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Min 8 chars, uppercase, lowercase, number">
        </div>
        <div class="form-group">
          <label for="password_confirm">Confirm New Password</label>
          <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-key"></i> Update Password</button>
      </form>

      <?php elseif ($tab === 'bookmarks'): ?>
      <h2>Saved Articles</h2>
      <?php if (empty($bookmarks)): ?>
      <p class="empty-state">No saved articles yet.</p>
      <?php else: ?>
      <div class="news-grid">
        <?php foreach ($bookmarks as $b): ?>
        <article class="news-card">
          <a href="<?= url('article/' . e($b['slug'])) ?>" class="thumb">
            <img src="<?= e($b['cover_image']) ?>" alt="<?= e($b['title']) ?>" loading="lazy">
          </a>
          <div class="news-card-body">
            <h3><a href="<?= url('article/' . e($b['slug'])) ?>"><?= e($b['title']) ?></a></h3>
            <div class="meta"><span><i class="fa-solid fa-bookmark"></i> Saved <?= format_date($b['saved_at']) ?></span></div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= view('layout/footer') ?>
