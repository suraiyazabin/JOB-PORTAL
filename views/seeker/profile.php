<div class="page-header"><h1>My Profile</h1></div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="profile-grid">
  <!-- LEFT COLUMN -->
  <div>
    <div class="card" style="text-align:center;">
      <div style="margin-bottom:12px;">
        <?php if (!empty($profile['profile_pic'])): ?>
          <img src="../<?= htmlspecialchars($profile['profile_pic']) ?>" style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #c8a84b;">
        <?php else: ?>
          <div style="width:100px;height:100px;border-radius:50%;background:#0d2a4e;display:inline-flex;align-items:center;justify-content:center;font-size:2.5rem;color:#c8a84b;">👤</div>
        <?php endif; ?>
      </div>
      <h2><?= htmlspecialchars($profile['name']) ?></h2>
      <p style="color:#5a7a9a;font-size:.9rem;"><?= htmlspecialchars($profile['headline'] ?? 'No headline yet') ?></p>
      <p style="color:#5a7a9a;font-size:.85rem;"><?= htmlspecialchars($profile['email']) ?></p>
      <form method="POST" action="SeekerController.php?action=profile" enctype="multipart/form-data" style="margin-top:14px;">
        <input type="hidden" name="submit_action" value="upload_pic">
        <div class="form-group"><label>Change Photo</label><input type="file" name="profile_pic" accept="image/*"></div>
        <button type="submit" class="btn btn-secondary btn-sm">Upload Photo</button>
      </form>
    </div>

    <div class="card">
      <h2>Resume</h2>
      <?php if (!empty($profile['resume_path'])): ?>
        <p style="margin-bottom:10px;"><a href="../<?= htmlspecialchars($profile['resume_path']) ?>" target="_blank" class="btn btn-secondary btn-sm">📄 View Current Resume</a></p>
      <?php else: ?>
        <p style="color:#5a7a9a;font-size:.88rem;margin-bottom:10px;">No resume uploaded yet.</p>
      <?php endif; ?>
      <form method="POST" action="SeekerController.php?action=profile" enctype="multipart/form-data">
        <input type="hidden" name="submit_action" value="upload_resume">
        <div class="form-group"><input type="file" name="resume" accept=".pdf"><small>PDF only, max 5MB</small></div>
        <button type="submit" class="btn btn-primary btn-sm">Upload Resume</button>
      </form>
    </div>

    <div class="card">
      <h2>Change Password</h2>
      <form method="POST" action="SeekerController.php?action=profile">
        <input type="hidden" name="submit_action" value="change_password">
        <div class="form-group"><label>Current Password</label><input type="password" name="current_password" required></div>
        <div class="form-group"><label>New Password</label><input type="password" name="new_password" required></div>
        <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" required></div>
        <button type="submit" class="btn btn-warning">Change Password</button>
      </form>
    </div>
  </div>

  <!-- RIGHT COLUMN -->
  <div class="card">
    <h2>Edit Profile Information</h2>
    <form method="POST" action="SeekerController.php?action=profile">
      <input type="hidden" name="submit_action" value="update_profile">
      <div class="section-title">Personal Info</div>
      <div class="form-row">
        <div class="form-group"><label>Full Name *</label><input type="text" name="name" value="<?= htmlspecialchars($profile['name']??'') ?>" required></div>
        <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($profile['phone']??'') ?>"></div>
      </div>
      <div class="form-group"><label>Professional Headline</label><input type="text" name="headline" value="<?= htmlspecialchars($profile['headline']??'') ?>" placeholder="e.g. Senior PHP Developer"></div>
      <div class="form-group"><label>Professional Summary</label><textarea name="summary" rows="4" placeholder="Brief description of your experience and goals..."><?= htmlspecialchars($profile['summary']??'') ?></textarea></div>

      <div class="section-title">Skills & Experience</div>
      <div class="form-group"><label>Skills (comma separated)</label><input type="text" name="skills" value="<?= htmlspecialchars($profile['skills']??'') ?>" placeholder="PHP, MySQL, JavaScript, HTML, CSS"></div>
      <div class="form-row">
        <div class="form-group">
          <label>Years of Experience</label>
          <select name="years_experience">
            <?php for ($i=0;$i<=20;$i++): ?>
            <option value="<?= $i ?>" <?= ($profile['years_experience']??0)==$i?'selected':'' ?>><?= $i ?><?= $i===20?'+':'' ?> year<?= $i!==1?'s':'' ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Education Level</label>
          <select name="education_level">
            <option value="">Select...</option>
            <?php foreach (['high_school'=>'High School','diploma'=>'Diploma','bachelor'=>'Bachelor\'s','master'=>'Master\'s','phd'=>'PhD','other'=>'Other'] as $val=>$lbl): ?>
            <option value="<?= $val ?>" <?= ($profile['education_level']??'')===$val?'selected':'' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="section-title">Salary & Location</div>
      <div class="form-row">
        <div class="form-group"><label>Current Salary (BDT/yr)</label><input type="number" name="current_salary" value="<?= $profile['current_salary']??'' ?>" placeholder="0"></div>
        <div class="form-group"><label>Expected Salary (BDT/yr)</label><input type="number" name="expected_salary" value="<?= $profile['expected_salary']??'' ?>" placeholder="0"></div>
      </div>
      <div class="form-group"><label>Preferred Location</label><input type="text" name="preferred_location" value="<?= htmlspecialchars($profile['preferred_location']??'') ?>" placeholder="e.g. Dhaka, Remote"></div>

      <button type="submit" class="btn btn-primary btn-lg">Save Profile</button>
    </form>
  </div>
</div>