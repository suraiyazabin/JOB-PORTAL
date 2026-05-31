<div class="page-header">
  <h1>Employer Accounts</h1>
  <a href="AdminController.php?action=employers" class="btn btn-secondary btn-sm">Clear Filter</a>
</div>
<?php if ($msg):   ?><div class="alert alert-success" id="ajax-msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card">
  <form method="GET" action="AdminController.php" class="filter-bar">
    <input type="hidden" name="action" value="employers">
    <label>Search:</label>
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name, email, company...">
    <select name="filter">
      <option value="">All</option>
      <option value="pending"   <?= $filter==='pending'?'selected':'' ?>>Pending Verification</option>
      <option value="verified"  <?= $filter==='verified'?'selected':'' ?>>Verified</option>
      <option value="suspended" <?= $filter==='suspended'?'selected':'' ?>>Suspended</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  </form>
</div>

<div id="ajax-notice" class="alert alert-success" style="display:none;"></div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Name</th><th>Company</th><th>Email</th><th>Industry</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody id="employer-table">
        <?php foreach ($employers as $i => $e): ?>
        <tr id="row-<?= $e['id'] ?>">
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($e['name']) ?></td>
          <td><?= htmlspecialchars($e['company_name'] ?? '—') ?></td>
          <td><?= htmlspecialchars($e['email']) ?></td>
          <td><?= htmlspecialchars($e['industry'] ?? '—') ?></td>
          <td>
            <?php if (!$e['is_active']): ?>
              <span class="badge badge-threat-high">Suspended</span>
            <?php elseif ($e['is_verified']): ?>
              <span class="badge badge-status-released">Verified</span>
            <?php else: ?>
              <span class="badge badge-open">Pending</span>
            <?php endif; ?>
          </td>
          <td><?= date('d M Y', strtotime($e['created_at'])) ?></td>
          <td>
            <?php if (!$e['is_active']): ?>
              <button class="btn btn-success btn-sm ajax-btn" data-action="activate" data-id="<?= $e['id'] ?>">Activate</button>
            <?php elseif (!$e['is_verified']): ?>
              <button class="btn btn-success btn-sm ajax-btn" data-action="verify"  data-id="<?= $e['id'] ?>">Verify</button>
              <button class="btn btn-danger  btn-sm ajax-btn" data-action="reject"  data-id="<?= $e['id'] ?>">Reject</button>
            <?php else: ?>
              <button class="btn btn-warning btn-sm ajax-btn" data-action="suspend" data-id="<?= $e['id'] ?>">Suspend</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($employers)): ?><tr><td colspan="8" class="no-data">No employers found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.querySelectorAll('.ajax-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var action = this.dataset.action;
    var id     = this.dataset.id;
    var row    = document.getElementById('row-' + id);
    var xhr    = new XMLHttpRequest();
    xhr.open('POST', 'AdminController.php?action=employers', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onload = function() {
      var res = JSON.parse(xhr.responseText);
      var notice = document.getElementById('ajax-notice');
      notice.textContent = res.msg;
      notice.style.display = 'block';
      setTimeout(function(){ notice.style.display='none'; }, 3000);
      if (res.success) row.remove();
    };
    xhr.send('ajax_action=' + action + '&id=' + id);
  });
});
</script>