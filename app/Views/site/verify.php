<?= view('layout/header') ?>

<div class="form-page">
  <div class="form-card">
    <?php if ($verified): ?>
    <div class="text-center mb-3"><i class="fa-solid fa-circle-check" style="font-size:48px;color:#16a34a;"></i></div>
    <h1 class="mb-2">Email Verified</h1>
    <p class="form-subtitle">Your email has been verified successfully!</p>
    <a href="<?= url('login') ?>" class="btn btn-primary btn-block">Login Now</a>
    <?php else: ?>
    <div class="text-center mb-3"><i class="fa-solid fa-triangle-exclamation" style="font-size:48px;color:var(--primary);"></i></div>
    <h1 class="mb-2">Verification Failed</h1>
    <p class="form-subtitle">Invalid or expired verification link.</p>
    <a href="<?= url('/') ?>" class="btn btn-primary btn-block">Go Home</a>
    <?php endif; ?>
  </div>
</div>

<?= view('layout/footer') ?>
