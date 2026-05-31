<div class="page-header"><h1>All Job Listings</h1></div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="card">
  <form method="GET" action="AdminController.php" class="filter-bar">
    <input type="hidden" name="action" value="jobs">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search title, company...">
    <select name="filter">
      <option value="">All</option>
      <option value="active"   <?= $filter==='active'?'selected':'' ?>>Active</option>
      <option value="closed"   <?= $filter==='closed'?'selected':'' ?>>Closed</option>
      <option value="featured" <?= $filter==='featured'?'selected':'' ?>>Featured</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  </form>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Title</th><th>Company</th><th>Category</th><th>Type</th><th>Status</th><th>Featured</th><th>Posted</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($jobs as $i => $j): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><strong><?= htmlspecialchars($j['title']) ?></strong></td>
          <td><?= htmlspecialchars($j['company_name'] ?? ($j['poster_name'] ?? '—')) ?></td>
          <td><?= htmlspecialchars($j['cat_name'] ?? '—') ?></td>
          <td><span class="badge badge-secondary"><?= $j['job_type'] ?></span></td>
          <td>
            <?php if ($j['status']==='active'):  ?><span class="badge badge-status-released">Active</span>
            <?php elseif ($j['status']==='closed'): ?><span class="badge badge-status-deceased">Closed</span>
            <?php else: ?><span class="badge badge-secondary">Draft</span><?php endif; ?>
          </td>
          <td>
            <form method="POST" action="AdminController.php?action=jobs" style="display:inline;">
              <input type="hidden" name="submit_action" value="toggle_featured">
              <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
              <input type="hidden" name="featured" value="<?= $j['is_featured'] ? 0 : 1 ?>">
              <button type="submit" class="btn btn-sm <?= $j['is_featured'] ? 'btn-warning' : 'btn-secondary' ?>">
                <?= $j['is_featured'] ? '★ Featured' : '☆ Feature' ?>
              </button>
            </form>
          </td>
          <td><?= date('d M Y', strtotime($j['created_at'])) ?></td>
          <td>
            <form method="POST" action="AdminController.php?action=jobs" style="display:inline;" onsubmit="return confirm('Remove this job listing?')">
              <input type="hidden" name="submit_action" value="delete_job">
              <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Remove</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($jobs)): ?><tr><td colspan="9" class="no-data">No jobs found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>