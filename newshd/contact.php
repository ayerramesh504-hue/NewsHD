<?php
require_once __DIR__ . '/config/config.php';
require_login();

$user = current_user();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $subject = sanitize($_POST['subject'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (strlen($subject) < 3) {
        $error = 'Subject must be at least 3 characters.';
    } elseif ($message === '') {
        $error = 'Please enter a message.';
    } elseif (!in_array($category, ['General', 'Editorial', 'Technical', 'Advertising', 'Feedback'], true)) {
        $error = 'Please select a valid department.';
    } else {
        $stmt = db()->prepare(
            'INSERT INTO contact_messages (user_id, subject, category, message) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$user['id'], $subject, $category, $message]);
        log_activity((int) $user['id'], 'contact_submit', 'contact_message', (int) db()->lastInsertId());
        $success = 'Your message has been sent. We will respond soon.';
    }
}

$activeNav = 'contact';
$meta = page_meta('Contact Us');
require APP_ROOT . '/includes/header.php';
?>

<div class="container" style="max-width:640px;padding:40px 0;">
  <div class="form-card">
    <h1>Contact Us</h1>
    <p style="color:var(--text-muted);margin-bottom:20px;text-align:center;">
      This form is available to registered members only.
    </p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

    <?php if (!$success): ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Name</label>
        <input type="text" value="<?= e($user['name']) ?>" readonly>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" value="<?= e($user['email']) ?>" readonly>
      </div>
      <div class="form-group">
        <label for="category">Department</label>
        <select id="category" name="category" required>
          <option value="">Select department</option>
          <?php foreach (['General', 'Editorial', 'Technical', 'Advertising', 'Feedback'] as $dept): ?>
          <option value="<?= e($dept) ?>" <?= ($_POST['category'] ?? '') === $dept ? 'selected' : '' ?>><?= e($dept) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject" required minlength="3"
               value="<?= e($_POST['subject'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="message">Message</label>
        <textarea id="message" name="message" rows="6" required><?= e($_POST['message'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Send Message</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
