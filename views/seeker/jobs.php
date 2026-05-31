<div class="page-header"><h1>Browse Jobs</h1><span style="color:#5a7a9a;" id="result-count"><?= count($jobs) ?> job(s) found</span></div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- FILTERS -->
<div class="card">
  <div class="filter-bar" id="filter-form">
    <input type="text"  id="f-keyword"  placeholder="Keyword..." value="<?= htmlspecialchars($kw??'') ?>" style="flex:1;min-width:140px;">
    <input type="text"  id="f-location" placeholder="Location..." value="<?= htmlspecialchars($loc??'') ?>">
    <select id="f-category">
      <option value="">All Categories</option>
      <?php foreach ($categories as $c): ?>
      <option value="<?= $c['id'] ?>" <?= ($cat??'')==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="f-type">
      <option value="">All Types</option>
      <option value="full-time" <?= ($type??'')==='full-time'?'selected':'' ?>>Full-Time</option>
      <option value="part-time" <?= ($type??'')==='part-time'?'selected':'' ?>>Part-Time</option>
      <option value="remote"    <?= ($type??'')==='remote'?'selected':'' ?>>Remote</option>
      <option value="contract"  <?= ($type??'')==='contract'?'selected':'' ?>>Contract</option>
    </select>
    <select id="f-exp">
      <option value="">All Levels</option>
      <option value="entry"  <?= ($exp??'')==='entry'?'selected':'' ?>>Entry</option>
      <option value="mid"    <?= ($exp??'')==='mid'?'selected':'' ?>>Mid</option>
      <option value="senior" <?= ($exp??'')==='senior'?'selected':'' ?>>Senior</option>
    </select>
    <button class="btn btn-primary btn-sm" onclick="filterJobs()">Search</button>
    <button class="btn btn-secondary btn-sm" onclick="clearFilters()">Clear</button>
  </div>
</div>

<!-- JOB LISTINGS -->
<div id="jobs-container">
  <?php foreach ($jobs as $j): ?>
  <div class="card job-card" style="margin-bottom:14px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
      <div style="flex:1;">
        <?php if ($j['is_featured']): ?><span class="badge badge-open" style="margin-bottom:6px;display:inline-block;">★ Featured</span><?php endif; ?>
        <h3 style="margin:0 0 4px;"><a href="SeekerController.php?action=job_detail&id=<?= $j['id'] ?>" style="color:#0d2a4e;text-decoration:none;"><?= htmlspecialchars($j['title']) ?></a></h3>
        <div style="color:#1a56a0;font-weight:bold;margin-bottom:4px;"><?= htmlspecialchars($j['company_name'] ?? 'Unknown Company') ?></div>
        <div style="font-size:.85rem;color:#5a7a9a;">
          📍 <?= htmlspecialchars($j['location']??'N/A') ?> &nbsp;|&nbsp;
          <?= htmlspecialchars($j['cat_name']??'') ?> &nbsp;|&nbsp;
          <span class="badge badge-secondary"><?= $j['job_type'] ?></span>
          <span class="badge badge-secondary"><?= $j['experience_level'] ?></span>
        </div>
        <?php if ($j['salary_min'] || $j['salary_max']): ?>
        <div style="font-size:.85rem;color:#2e7d32;margin-top:4px;">💰 BDT <?= number_format($j['salary_min']) ?> – <?= number_format($j['salary_max']) ?> / yr</div>
        <?php endif; ?>
        <div style="font-size:.8rem;color:#999;margin-top:4px;">Posted <?= date('d M Y', strtotime($j['created_at'])) ?><?= $j['deadline'] ? ' · Deadline: '.date('d M Y', strtotime($j['deadline'])) : '' ?></div>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end;">
        <a href="SeekerController.php?action=job_detail&id=<?= $j['id'] ?>" class="btn btn-primary btn-sm">View & Apply</a>
        <button class="btn btn-sm <?= $j['is_saved'] ? 'btn-warning' : 'btn-secondary' ?> save-btn" data-id="<?= $j['id'] ?>">
          <?= $j['is_saved'] ? '★ Saved' : '☆ Save' ?>
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($jobs)): ?>
  <div class="card"><p class="no-data">No jobs found matching your filters.</p></div>
  <?php endif; ?>
</div>

<script>
function filterJobs() {
  var params = new URLSearchParams({
    action:     'jobs',
    keyword:    document.getElementById('f-keyword').value,
    category:   document.getElementById('f-category').value,
    location:   document.getElementById('f-location').value,
    job_type:   document.getElementById('f-type').value,
    exp_level:  document.getElementById('f-exp').value,
  });
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'SeekerController.php?' + params.toString(), true);
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.onload = function() {
    var jobs = JSON.parse(xhr.responseText);
    document.getElementById('result-count').textContent = jobs.length + ' job(s) found';
    var html = '';
    if (jobs.length === 0) {
      html = '<div class="card"><p class="no-data">No jobs found matching your filters.</p></div>';
    } else {
      jobs.forEach(function(j) {
        var saved     = j.is_saved;
        var featured  = j.is_featured == '1' ? '<span class="badge badge-open" style="margin-bottom:6px;display:inline-block;">★ Featured</span>' : '';
        var salary    = (j.salary_min || j.salary_max) ? '<div style="font-size:.85rem;color:#2e7d32;margin-top:4px;">💰 BDT ' + Number(j.salary_min).toLocaleString() + ' – ' + Number(j.salary_max).toLocaleString() + ' / yr</div>' : '';
        html += '<div class="card job-card" style="margin-bottom:14px;"><div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">';
        html += '<div style="flex:1;">' + featured;
        html += '<h3 style="margin:0 0 4px;"><a href="SeekerController.php?action=job_detail&id=' + j.id + '" style="color:#0d2a4e;text-decoration:none;">' + j.title + '</a></h3>';
        html += '<div style="color:#1a56a0;font-weight:bold;margin-bottom:4px;">' + (j.company_name || 'Unknown') + '</div>';
        html += '<div style="font-size:.85rem;color:#5a7a9a;">📍 ' + (j.location||'N/A') + ' &nbsp;|&nbsp; ' + (j.cat_name||'') + ' &nbsp;|&nbsp; <span class="badge badge-secondary">' + j.job_type + '</span> <span class="badge badge-secondary">' + j.experience_level + '</span></div>';
        html += salary + '</div>';
        html += '<div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end;">';
        html += '<a href="SeekerController.php?action=job_detail&id=' + j.id + '" class="btn btn-primary btn-sm">View & Apply</a>';
        html += '<button class="btn btn-sm ' + (saved ? 'btn-warning' : 'btn-secondary') + ' save-btn" data-id="' + j.id + '">' + (saved ? '★ Saved' : '☆ Save') + '</button>';
        html += '</div></div></div>';
      });
    }
    document.getElementById('jobs-container').innerHTML = html;
    bindSaveBtns();
  };
  xhr.send();
}

function clearFilters() {
  ['f-keyword','f-location'].forEach(function(id){ document.getElementById(id).value=''; });
  ['f-category','f-type','f-exp'].forEach(function(id){ document.getElementById(id).selectedIndex=0; });
  filterJobs();
}

function bindSaveBtns() {
  document.querySelectorAll('.save-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id  = this.dataset.id;
      var el  = this;
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'SeekerController.php?action=toggle_save', true);
      xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
      xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
      xhr.onload = function() {
        var res = JSON.parse(xhr.responseText);
        if (res.status === 'saved') { el.textContent = '★ Saved'; el.classList.add('btn-warning'); el.classList.remove('btn-secondary'); }
        else { el.textContent = '☆ Save'; el.classList.add('btn-secondary'); el.classList.remove('btn-warning'); }
      };
      xhr.send('job_id=' + id);
    });
  });
}
bindSaveBtns();

// auto-filter on input
['f-category','f-type','f-exp'].forEach(function(id){
  document.getElementById(id).addEventListener('change', filterJobs);
});
document.getElementById('f-keyword').addEventListener('keydown', function(e){ if(e.key==='Enter') filterJobs(); });
</script>