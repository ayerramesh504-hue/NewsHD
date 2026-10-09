<div class="data-table-wrap">
  <div class="table-toolbar"><h3>Contact Messages</h3></div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>Subject</th>
          <th>From</th>
          <th>Category</th>
          <th>Message</th>
          <th>Status</th>
          <th>Date</th>
          <th>Reply</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($messages)): ?>
        <tr><td colspan="7" class="empty-cell">No messages.</td></tr>
        <?php else: foreach ($messages as $m): ?>
        <tr>
          <td class="strong"><?= e($m['subject']) ?></td>
          <td><?= e($m['user_name']) ?><div class="sub-note"><?= e($m['user_email']) ?></div></td>
          <td><span class="badge badge-cat"><?= e($m['category']) ?></span></td>
          <td style="max-width:260px;">
            <?= e(truncate($m['message'], 90)) ?>
            <?php if ($m['admin_reply']): ?><div class="sub-note"><i class="fa-solid fa-reply"></i> <?= e(truncate($m['admin_reply'], 60)) ?></div><?php endif; ?>
          </td>
          <td><span class="badge badge-<?= $m['status'] === 'resolved' ? 'approved' : ($m['status'] === 'new' ? 'pending' : 'published') ?>"><?= e($m['status']) ?></span></td>
          <td><?= format_date($m['created_at'], 'M j, Y') ?></td>
          <td class="row-actions">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('replyForm<?= (int) $m['id'] ?>').classList.toggle('show')"><i class="fa-solid fa-reply"></i> Reply</button>
            <form method="post" action="<?= url('admin/messages/action/' . (int) $m['id']) ?>" class="reply-form" id="replyForm<?= (int) $m['id'] ?>">
              <?= csrf_field() ?>
              <textarea name="admin_reply" rows="2" placeholder="Reply to this message"><?= e($m['admin_reply'] ?? '') ?></textarea>
              <div class="reply-actions">
                <select name="status">
                  <option value="read" <?= $m['status'] === 'read' ? 'selected' : '' ?>>Read</option>
                  <option value="resolved" <?= $m['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                  <option value="new" <?= $m['status'] === 'new' ? 'selected' : '' ?>>New</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
              </div>
            </form>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
