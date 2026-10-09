<div class="form-card-wide">
  <h3>Site Settings</h3>
  <p class="form-note">Update your news portal's branding and contact information. Saved instantly.</p>

  <form method="post" action="<?= url('admin/settings/save') ?>">
    <?= csrf_field() ?>

    <h4 class="fieldset-title"><i class="fa-solid fa-brush"></i> Branding</h4>
    <div class="form-row">
      <div class="form-group">
        <label for="site_name">Site Name</label>
        <input type="text" id="site_name" name="site_name" value="<?= e($settings['site_name'] ?? APP_NAME) ?>">
      </div>
      <div class="form-group">
        <label for="logo_url">Logo URL</label>
        <input type="text" id="logo_url" name="logo_url" value="<?= e($settings['logo_url'] ?? '') ?>" placeholder="assets/images/logo.png">
      </div>
    </div>
    <div class="form-group">
      <label for="site_tagline">Tagline</label>
      <input type="text" id="site_tagline" name="site_tagline" value="<?= e($settings['site_tagline'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label for="site_description">Description</label>
      <textarea id="site_description" name="site_description" rows="2"><?= e($settings['site_description'] ?? '') ?></textarea>
    </div>

    <h4 class="fieldset-title"><i class="fa-solid fa-location-dot"></i> Contact</h4>
    <div class="form-row">
      <div class="form-group">
        <label for="contact_email">Email</label>
        <input type="email" id="contact_email" name="contact_email" value="<?= e($settings['contact_email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="contact_phone">Phone</label>
        <input type="text" id="contact_phone" name="contact_phone" value="<?= e($settings['contact_phone'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="contact_address">Address</label>
      <input type="text" id="contact_address" name="contact_address" value="<?= e($settings['contact_address'] ?? '') ?>">
    </div>

    <h4 class="fieldset-title"><i class="fa-solid fa-share-nodes"></i> Social Media</h4>
    <div class="form-row">
      <div class="form-group">
        <label for="facebook_url">Facebook</label>
        <input type="url" id="facebook_url" name="facebook_url" value="<?= e($settings['facebook_url'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="twitter_url">X / Twitter</label>
        <input type="url" id="twitter_url" name="twitter_url" value="<?= e($settings['twitter_url'] ?? '') ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="youtube_url">YouTube</label>
        <input type="url" id="youtube_url" name="youtube_url" value="<?= e($settings['youtube_url'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="meta_keywords">Meta Keywords</label>
        <input type="text" id="meta_keywords" name="meta_keywords" value="<?= e($settings['meta_keywords'] ?? '') ?>">
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Settings</button>
    </div>
  </form>
</div>
