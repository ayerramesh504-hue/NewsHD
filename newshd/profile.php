<?php
require_once __DIR__ . '/config/config.php';
require_login();

$user = current_user();
$tab = sanitize($_GET['tab'] ?? 'profile');
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $name = sanitize($_POST['name'] ?? '');
        $bio = sanitize($_POST['bio'] ?? '');
        if (strlen($name) < 2) {
            $error = 'Name must be at least 2 characters.';
        } else {
            $avatar = $user['avatar'];
            if (!empty($_FILES['avatar']['name'])) {
                $uploaded = upload_image($_FILES['avatar'], 'avatars');
                if ($uploaded) $avatar = $uploaded;
            }
            $stmt = db()->prepare('UPDATE users SET name = ?, bio = ?, avatar = ? WHERE id = ?');
            $stmt->execute([$name, $bio, $avatar, $user['id']]);
            $success = 'Profile updated successfully.';
            unset($user);
            $user = current_user();
        }
    } elseif ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        if (!password_verify($current, $user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif ($msg = validate_password_strength($new)) {
            $error = $msg;
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $user['id']]);
            $success = 'Password changed successfully.';
        }
    }
}

$commentsStmt = db()->prepare(
    "SELECT c.*, a.title AS article_title, a.slug AS article_slug
     FROM comments c INNER JOIN articles a ON a.id = c.article_id
     WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT 20"
);
$commentsStmt->execute([$user['id']]);
$userComments = $commentsStmt->fetchAll();

$bookmarksStmt = db()->prepare(
    "SELECT a.id, a.title, a.slug, a.cover_image, a.published_at, b.created_at AS saved_at
     FROM bookmarks b INNER JOIN articles a ON a.id = b.article_id
     WHERE b.user_id = ? ORDER BY b.created_at DESC"
);
$bookmarksStmt->execute([$user['id']]);
$bookmarks = $bookmarksStmt->fetchAll();

$meta = page_meta('My Profile');
require APP_ROOT . '/includes/header.php';
?>

<div class="profile-layout container" style="width:90%;max-width:1200px;">
  <aside class="profile-sidebar">
    <img src="<?= e($user['avatar'] ? url($user['avatar']) : asset('images/profile.svg')) ?>"
         alt="Avatar" class="avatar-preview">
    <strong><?= e($user['name']) ?></strong>
    <p style="font-size:13px;color:var(--text-muted);margin:8px 0 16px;"><?= e($user['email']) ?></p>
    <a href="?tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>">Edit Profile</a>
    <a href="?tab=password" class="<?= $tab === 'password' ? 'active' : '' ?>">Change Password</a>
    <a href="?tab=comments" class="<?= $tab === 'comments' ? 'active' : '' ?>">My Comments</a>
    <a href="?tab=bookmarks" class="<?= $tab === 'bookmarks' ? 'active' : '' ?>">Saved Articles</a>
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
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>

    <?php elseif ($tab === 'password'): ?>
    <h2>Change Password</h2>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">
      <div class="form-group">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" required>
      </div>
      <div class="form-group">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required minlength="8">
      </div>
      <div class="form-group">
        <label for="password_confirm">Confirm New Password</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
      </div>
      <button type="submit" class="btn btn-primary">Update Password</button>
    </form>

    <?php elseif ($tab === 'comments'): ?>
    <h2>My Comments</h2>
    <?php if (empty($userComments)): ?>
    <p class="empty-state">You haven't posted any comments yet.</p>
    <?php else: foreach ($userComments as $c): ?>
    <div class="comment-item">
      <div class="comment-header">
        <span class="comment-author"><a href="<?= url('article.php?slug=' . e($c['article_slug'])) ?>"><?= e($c['article_title']) ?></a></span>
        <span class="comment-date"><?= format_date($c['created_at'], 'M j, Y g:i A') ?></span>
      </div>
      <p><?= e($c['body']) ?></p>
      <small>Status: <?= e($c['status']) ?></small>
    </div>
    <?php endforeach; endif; ?>

    <?php elseif ($tab === 'bookmarks'): ?>
    <h2>Saved Articles</h2>
    <?php if (empty($bookmarks)): ?>
    <p class="empty-state">No saved articles yet.</p>
    <?php else: ?>
    <div class="news-grid">
      <?php foreach ($bookmarks as $b): ?>
      <article class="news-card">
        <a href="<?= url('article.php?slug=' . e($b['slug'])) ?>">
          <img src="<?= e($b['cover_image']) ?>" alt="<?= e($b['title']) ?>" loading="lazy">
        </a>
        <div class="news-card-body">
          <h3><a href="<?= url('article.php?slug=' . e($b['slug'])) ?>"><?= e($b['title']) ?></a></h3>
          <div class="meta"><span>Saved <?= format_date($b['saved_at']) ?></span></div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
