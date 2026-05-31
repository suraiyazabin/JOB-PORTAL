<div class="page-header"><h1>My Applications</h1><span style="color:#5a7a9a;"><?= count($applications) ?> total</span></div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Job Title</th><th>Company</th><th>Location</th><th>Type</th><th>Status</th><th>Applied</th><th>Action</th></tr></thead>
      <tbody>
        <?php
        $statusClass = ['submitted'=>'badge-secondary','reviewed'=>'badge-under-investigation','shortlisted'=>'badge-status-released','interview'=>'badge-open','rejected'=>'badge-threat-high','withdrawn'=>'badge-status-deceased'];
        foreach ($applications as $i => $a):
        $cls = $statusClass[$a['status']] ?? 'badge-secondary';
        ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><a href="SeekerController.php?action=job_detail&id=<?= $a['job_id'] ?>"><?= htmlspecialchars($a['job_title']) ?></a></td>
          <td><?= htmlspecialchars($a['company_name']??'—') ?></td>
          <td><?= htmlspecialchars($a['location']??'—') ?></td>
          <td><span class="badge badge-secondary"><?= $a['job_type'] ?></span></td>
          <td><span class="badge <?= $cls ?>"><?= ucfirst($a['status']) ?></span></td>
          <td><?= date('d M Y', strtotime($a['applied_at'])) ?></td>
          <td>
            <?php if ($a['status'] === 'submitted'): ?>
            <form method="POST" action="SeekerController.php?action=applications" onsubmit="return confirm('Withdraw this application?')">
              <input type="hidden" name="submit_action" value="withdraw_application">
              <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Withdraw</button>
            </form>
            <?php else: ?>
            <span style="color:#999;font-size:.82rem;">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($applications)): ?>
        <tr><td colspan="8" class="no-data">No applications yet. <a href="SeekerController.php?action=jobs">Browse jobs</a></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>