<div class="page-header">
  <h1>Recruiter Accounts</h1>
  <a href="AdminController.php?action=recruiters" class="btn btn-secondary btn-sm">Clear Filter</a>
</div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="card">
  <form method="GET" action="AdminController.php" class="filter-bar">
    <input type="hidden" name="action" value="recruiters">
    <label>Search:</label>
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name, email, agency...">
    <select name="filter">
      <option value="">All</option>
      <option value="pending"   <?= $filter==='pending'?'selected':'' ?>>Pending</option>
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
      <thead><tr><th>#</th><th>Name</th><th>Agency</th><th>Email</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($recruiters as $i => $r): ?>
        <tr id="row-<?= $r['id'] ?>">
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td><?= htmlspecialchars($r['agency_name'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['email']) ?></td>
          <td>
            <?php if (!$r['is_active']): ?><span class="badge badge-threat-high">Suspended</span>
            <?php elseif ($r['is_verified']): ?><span class="badge badge-status-released">Verified</span>
            <?php else: ?><span class="badge badge-open">Pending</span><?php endif; ?>
          </td>
          <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
          <td>
            <?php if (!$r['is_active']): ?>
              <button class="btn btn-success btn-sm ajax-btn" data-action="activate" data-id="<?= $r['id'] ?>">Activate</button>
            <?php elseif (!$r['is_verified']): ?>
              <button class="btn btn-success btn-sm ajax-btn" data-action="verify" data-id="<?= $r['id'] ?>">Verify</button>
              <button class="btn btn-danger  btn-sm ajax-btn" data-action="reject" data-id="<?= $r['id'] ?>">Reject</button>
            <?php else: ?>
              <button class="btn btn-warning btn-sm ajax-btn" data-action="suspend" data-id="<?= $r['id'] ?>">Suspend</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recruiters)): ?><tr><td colspan="7" class="no-data">No recruiters found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
document.querySelectorAll('.ajax-btn').forEach(function(btn){
  btn.addEventListener('click', function(){
    var xhr = new XMLHttpRequest();
    xhr.open('POST','AdminController.php?action=recruiters',true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
    xhr.onload = function(){
      var res = JSON.parse(xhr.responseText);
      var n = document.getElementById('ajax-notice');
      n.textContent = res.msg; n.style.display='block';
      setTimeout(function(){ n.style.display='none'; },3000);
      if (res.success) document.getElementById('row-'+btn.dataset.id).remove();
    };
    xhr.send('ajax_action='+btn.dataset.action+'&id='+btn.dataset.id);
  });
});
</script>