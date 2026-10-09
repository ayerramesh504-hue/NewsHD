<?php
require_once dirname(__DIR__) . '/config/config.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | NEWSHD</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div id="adminRoot"
       data-api="<?= api_url('admin/index.php') ?>"
       data-site-url="<?= url('') ?>"
       data-logout-url="<?= url('logout.php') ?>"
       data-user-name="<?= e(current_user()['name']) ?>"
       data-user-role="<?= e(current_user()['role_name']) ?>"
       data-role-slug="<?= e(current_user()['role_slug']) ?>"></div>
  <script>window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>; window.APP_URL = <?= json_encode(APP_URL) ?>;</script>
  <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
  <script src="<?= asset('js/theme.js') ?>"></script>
  <script src="<?= url('admin/js/app.js') ?>"></script>
</body>
</html>
