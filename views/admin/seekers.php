<div class="page-header"><h1>Job Seeker Accounts</h1></div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="card">
  <form method="GET" action="AdminController.php" class="filter-bar">
    <input type="hidden" name="action" value="seekers">
    <label>Search:</label>
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name or email...">
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
  </form>
</div>
<div id="ajax-notice" class="alert alert-success" style="display:none;"></div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Headline</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($seekers as $i => $s): ?>
        <tr id="row-<?= $s['id'] ?>">
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td><?= htmlspecialchars($s['email']) ?></td>
          <td><?= htmlspecialchars($s['headline'] ?? '—') ?></td>
          <td><?= $s['is_active'] ? '<span class="badge badge-status-released">Active</span>' : '<span class="badge badge-threat-high">Deactivated</span>' ?></td>
          <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
          <td>
            <?php if ($s['is_active']): ?>
              <button class="btn btn-danger btn-sm ajax-btn" data-action="suspend" data-id="<?= $s['id'] ?>">Deactivate</button>
            <?php else: ?>
              <button class="btn btn-success btn-sm ajax-btn" data-action="activate" data-id="<?= $s['id'] ?>">Activate</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($seekers)): ?><tr><td colspan="7" class="no-data">No seekers found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
document.querySelectorAll('.ajax-btn').forEach(function(btn){
  btn.addEventListener('click', function(){
    var xhr = new XMLHttpRequest();
    xhr.open('POST','AdminController.php?action=seekers',true);
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