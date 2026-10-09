<div class="data-table-wrap">
  <div class="table-toolbar">
    <div>
      <h3>Users</h3>
      <!-- <p class="table-toolbar-note">Your active account is shown in the sidebar.</p> -->
    </div>
    <!-- <span class="toolbar-hint"><i class="fa-solid fa-shield-halved"></i> Admin only: change roles or ban accounts</span> -->
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Last Login</th>
          <th>Joined</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
        <tr><td colspan="7" class="empty-cell">No users.</td></tr>
        <?php else: foreach ($users as $u): ?>
        <tr>
          <td class="strong"><?= e($u['name']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><span class="badge role-<?= e($u['role_slug']) ?>"><?= e($u['role_name']) ?></span></td>
          <td><?= $u['is_banned'] ? '<span class="badge badge-rejected">Banned</span>' : '<span class="badge badge-approved">Active</span>' ?></td>
          <td><?= $u['last_login_at'] ? format_date($u['last_login_at'], 'M j, Y') : '&mdash;' ?></td>
          <td><?= format_date($u['created_at'], 'M j, Y') ?></td>
          <td class="row-actions">
            <form method="post" action="<?= url('admin/users/save') ?>" class="inline-form user-form" id="userForm<?= (int) $u['id'] ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
              <select name="role_id" aria-label="Role">
                <?php foreach ($roles as $r): ?>
                <option value="<?= (int) $r['id'] ?>" <?= (int) $u['role_id'] === (int) $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if ($u['is_banned']): ?>
              <button type="submit" class="btn btn-success btn-sm" title="Unban"><i class="fa-solid fa-user-check"></i> Unban</button>
              <?php else: ?>
              <label class="form-check ban-check">
                <input type="checkbox" name="is_banned" value="1"> Ban
              </label>
              <button type="submit" class="btn btn-secondary btn-sm" title="Save"><i class="fa-solid fa-floppy-disk"></i></button>
              <?php endif; ?>
            </form>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
