<?php $chartLabels = []; $chartData = []; $now = time(); for ($i = 6; $i >= 0; $i--) { $d = date('Y-m-d', $now - $i * 86400); $chartLabels[] = date('M j', strtotime($d)); $chartData[$d] = 0; } foreach ($chart as $c) { if (isset($chartData[$c['day']])) { $chartData[$c['day']] = (int) $c['count']; } } ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<section class="dashboard-intro">
  <div>
    <span class="dashboard-eyebrow"><i class="fa-solid fa-sparkles"></i> Newsroom overview</span>
    <h1>Everything is in view.</h1>
    <p>Track your publishing activity, audience reach, and incoming work from one place.</p>
  </div>
  <a href="<?= url('admin/articles/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create article</a>
</section>
<div class="stats-grid">
  <div class="stat-card stat-articles">
    <div class="stat-icon"><i class="fa-solid fa-newspaper"></i></div>
    <div class="stat-body">
      <h3><?= number_format($stats['articles']) ?></h3>
      <p>Total Articles</p>
    </div>
  </div>
  <div class="stat-card stat-published">
    <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-body">
      <h3><?= number_format($stats['published']) ?></h3>
      <p>Published</p>
    </div>
  </div>
  <div class="stat-card stat-draft">
    <div class="stat-icon"><i class="fa-solid fa-pen"></i></div>
    <div class="stat-body">
      <h3><?= number_format($stats['drafts']) ?></h3>
      <p>Drafts</p>
    </div>
  </div>
  <div class="stat-card stat-users">
    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
    <div class="stat-body">
      <h3><?= number_format($stats['users']) ?></h3>
      <p>Users</p>
    </div>
  </div>
  <div class="stat-card stat-messages">
    <div class="stat-icon"><i class="fa-solid fa-envelope"></i></div>
    <div class="stat-body">
      <h3><?= number_format($stats['new_messages']) ?></h3>
      <p>New Messages</p>
    </div>
  </div>
</div>

<div class="chart-card">
  <div class="card-head">
    <h3>Activity (last 7 days)</h3>
  </div>
  <canvas id="activityChart" height="90"
          data-labels='<?= json_encode($chartLabels) ?>'
          data-values='<?= json_encode(array_values($chartData)) ?>'></canvas>
</div>

<div class="data-table-wrap">
  <div class="table-toolbar">
    <h3>Recent Articles</h3>
    <a href="<?= url('admin/articles/create') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Article</a>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Category</th>
          <th>Author</th>
          <th>Status</th>
          <th>Views</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recent)): ?>
        <tr><td colspan="7" class="empty-cell">No articles yet.</td></tr>
        <?php else: foreach ($recent as $a): ?>
        <tr>
          <td><a href="<?= url('admin/articles/edit/' . (int) $a['id']) ?>" class="table-link"><?= e(truncate($a['title'], 60)) ?></a></td>
          <td><span class="badge badge-cat"><?= e($a['category_name']) ?></span></td>
          <td><?= e($a['author_name']) ?></td>
          <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
          <td><span class="table-metric"><i class="fa-regular fa-eye"></i><?= number_format($a['view_count']) ?></span></td>
          <td><?= format_date($a['created_at'], 'M j, Y') ?></td>
          <td class="row-actions">
            <a href="<?= url('admin/articles/edit/' . (int) $a['id']) ?>" class="btn btn-secondary btn-sm" title="Edit"><i class="fa-solid fa-pen"></i></a>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
