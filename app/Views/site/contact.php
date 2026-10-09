<?= view('layout/header') ?>

<div class="page-hero">
  <div class="container">
    <h1>Contact Us</h1>
    <!-- <p>We'd love to hear from you </p> -->
  </div>
</div>

<section class="section">
  <div class="container" style="max-width:720px;">
    <div class="form-card">
      <!-- <p class="form-subtitle">
        This form is available to registered members only. Your contact details are pre-filled.
      </p> -->
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <?php if (! $success): ?>
      <form method="post">
        <?= csrf_field() ?>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="form-group">
            <label>Name</label>
            <input type="text" value="<?= e($user['name']) ?>" readonly>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?= e($user['email']) ?>" readonly>
          </div>
        </div>
        <div class="form-group">
          <label for="category">Department</label>
          <select id="category" name="category" required>
            <option value="">Select department</option>
            <?php foreach ($departments as $dept): ?>
            <option value="<?= e($dept) ?>" <?= old('category') === $dept ? 'selected' : '' ?>><?= e($dept) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" required minlength="3"
                 value="<?= e(old('subject')) ?>">
        </div>
        <div class="form-group">
          <label for="message">Message</label>
          <textarea id="message" name="message" rows="6" required><?= e(old('message')) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<?= view('layout/footer') ?>
