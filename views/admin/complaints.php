<div class="page-header"><h1>Complaints & Disputes</h1></div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="card">
  <form method="GET" action="AdminController.php" class="filter-bar">
    <input type="hidden" name="action" value="complaints">
    <select name="filter">
      <option value="">All</option>
      <option value="open"     <?= $filter==='open'?'selected':'' ?>>Open</option>
      <option value="resolved" <?= $filter==='resolved'?'selected':'' ?>>Resolved</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  </form>
</div>
<?php foreach ($complaints as $c): ?>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
    <div>
      <strong><?= htmlspecialchars($c['submitter_name']) ?></strong>
      <span style="color:#5a7a9a;"> filed against </span>
      <strong><?= htmlspecialchars($c['subject_name']) ?></strong>
      <span class="badge badge-secondary" style="margin-left:6px;"><?= $c['subject_role'] ?></span>
    </div>
    <div>
      <?= $c['status']==='resolved' ? '<span class="badge badge-status-released">Resolved</span>' : '<span class="badge badge-open">Open</span>' ?>
      <span style="color:#999;font-size:.8rem;margin-left:8px;"><?= date('d M Y', strtotime($c['created_at'])) ?></span>
    </div>
  </div>
  <p style="margin:12px 0;color:#2c3e50;"><?= nl2br(htmlspecialchars($c['description'])) ?></p>
  <?php if ($c['admin_note']): ?>
    <div class="alert alert-success" style="margin:0;font-size:.85rem;"><strong>Admin note:</strong> <?= htmlspecialchars($c['admin_note']) ?></div>
  <?php endif; ?>
  <?php if ($c['status'] === 'open'): ?>
  <form method="POST" action="AdminController.php?action=complaints" style="margin-top:12px;">
    <input type="hidden" name="submit_action" value="resolve_complaint">
    <input type="hidden" name="complaint_id" value="<?= $c['id'] ?>">
    <div class="form-group"><label>Resolution Note</label><textarea name="admin_note" rows="2" placeholder="Describe the resolution..."></textarea></div>
    <button type="submit" class="btn btn-success btn-sm">Mark Resolved</button>
  </form>
  <?php endif; ?>
</div>
<?php endforeach; ?>
<?php if (empty($complaints)): ?><div class="card"><p class="no-data">No complaints found.</p></div><?php endif; ?>