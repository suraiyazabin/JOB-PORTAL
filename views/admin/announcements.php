<div class="page-header"><h1>Announcements</h1></div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="two-col">
  <div class="card">
    <h2>Post New Announcement</h2>
    <form method="POST" action="AdminController.php?action=announcements">
      <input type="hidden" name="submit_action" value="add_announcement">
      <div class="form-group"><label>Title *</label><input type="text" name="title" required placeholder="Announcement title"></div>
      <div class="form-group"><label>Message *</label><textarea name="body" rows="5" required placeholder="Write your announcement..."></textarea></div>
      <button type="submit" class="btn btn-primary">Post Announcement</button>
    </form>
  </div>
  <div>
    <h2 style="color:#0d2a4e;margin-bottom:16px;font-family:Arial;">Previous Announcements</h2>
    <?php foreach ($announcements as $a): ?>
    <div class="card" style="margin-bottom:14px;">
      <div style="display:flex;justify-content:space-between;align-items:start;">
        <div>
          <strong style="color:#0d2a4e;"><?= htmlspecialchars($a['title']) ?></strong>
          <div style="font-size:.8rem;color:#5a7a9a;margin-top:2px;">By <?= htmlspecialchars($a['admin_name']) ?> — <?= date('d M Y, H:i', strtotime($a['created_at'])) ?></div>
        </div>
        <form method="POST" action="AdminController.php?action=announcements" onsubmit="return confirm('Delete this announcement?')">
          <input type="hidden" name="submit_action" value="delete_announcement">
          <input type="hidden" name="ann_id" value="<?= $a['id'] ?>">
          <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
      </div>
      <p style="margin-top:10px;color:#2c3e50;"><?= nl2br(htmlspecialchars($a['body'])) ?></p>
    </div>
    <?php endforeach; ?>
    <?php if (empty($announcements)): ?><div class="card"><p class="no-data">No announcements yet.</p></div><?php endif; ?>
  </div>
</div>