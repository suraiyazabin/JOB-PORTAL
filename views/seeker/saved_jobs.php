<div class="page-header"><h1>Saved Jobs</h1><span style="color:#5a7a9a;"><?= count($saved_jobs) ?> saved</span></div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if (empty($saved_jobs)): ?>
  <div class="card"><p class="no-data">No saved jobs yet. <a href="SeekerController.php?action=jobs">Browse jobs</a> and click ☆ Save to bookmark them.</p></div>
<?php else: ?>
  <?php foreach ($saved_jobs as $j): ?>
  <div class="card" style="margin-bottom:14px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
      <div style="flex:1;">
        <h3 style="margin:0 0 4px;"><a href="SeekerController.php?action=job_detail&id=<?= $j['id'] ?>" style="color:#0d2a4e;"><?= htmlspecialchars($j['title']) ?></a></h3>
        <div style="color:#1a56a0;font-weight:bold;font-size:.9rem;"><?= htmlspecialchars($j['company_name']??'Unknown') ?></div>
        <div style="font-size:.83rem;color:#5a7a9a;margin-top:4px;">
          📍 <?= htmlspecialchars($j['location']??'N/A') ?> &nbsp;|&nbsp;
          <?= htmlspecialchars($j['cat_name']??'') ?> &nbsp;|&nbsp;
          <span class="badge badge-secondary"><?= $j['job_type'] ?></span>
          <?= $j['status']==='active' ? '<span class="badge badge-status-released">Active</span>' : '<span class="badge badge-threat-high">Closed</span>' ?>
        </div>
        <div style="font-size:.8rem;color:#999;margin-top:4px;">Saved <?= date('d M Y', strtotime($j['saved_at'])) ?></div>
      </div>
      <div style="display:flex;gap:8px;">
        <a href="SeekerController.php?action=job_detail&id=<?= $j['id'] ?>" class="btn btn-primary btn-sm">View Job</a>
        <button class="btn btn-warning btn-sm save-btn" data-id="<?= $j['id'] ?>">★ Unsave</button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>

<script>
document.querySelectorAll('.save-btn').forEach(function(btn){
  btn.addEventListener('click', function(){
    var id = this.dataset.id;
    var card = this.closest('.card');
    var xhr = new XMLHttpRequest();
    xhr.open('POST','SeekerController.php?action=toggle_save',true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
    xhr.onload = function(){
      var res = JSON.parse(xhr.responseText);
      if (res.status === 'unsaved') card.remove();
    };
    xhr.send('job_id='+id);
  });
});
</script>