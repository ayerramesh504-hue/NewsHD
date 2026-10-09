<?= view('layout/header') ?>

<section class="section">
  <div class="container">
    <div class="empty-state">
      <i class="fa-solid fa-newspaper"></i>
      <h2>Article not found</h2>
      <p>The article you're looking for may have been removed or moved.</p>
      <p class="mt-2"><a href="<?= url('/') ?>" class="btn btn-primary">Return Home</a></p>
    </div>
  </div>
</section>

<?= view('layout/footer') ?>
