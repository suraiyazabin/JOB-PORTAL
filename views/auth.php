<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Job Portal — <?= $action === 'register' ? 'Register' : 'Login' ?></title>
<link rel="stylesheet" href="<?= VIEWS ?>/external.css">
</head>
<body>
<div class="auth-wrapper">
<?php if ($action === 'register'): ?>
  <div class="auth-box auth-wide">
    <div class="auth-logo">💼</div>
    <h2>Create Account</h2>
    <p class="auth-sub">Join JobPortal as a Job Seeker</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST" action="../controllers/AuthController.php?action=register">
      <div class="form-row">
        <div class="form-group"><label>Full Name *</label><input type="text" name="name" value="<?= htmlspecialchars($_POST['name']??'') ?>" required></div>
        <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($_POST['phone']??'') ?>"></div>
      </div>
      <div class="form-group"><label>Email Address *</label><input type="email" name="email" value="<?= htmlspecialchars($_POST['email']??'') ?>" required></div>
      <div class="form-row">
        <div class="form-group"><label>Password *</label><input type="password" name="password" required></div>
        <div class="form-group"><label>Confirm Password *</label><input type="password" name="password2" required></div>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>
    </form>
    <div class="auth-footer">Already have an account? <a href="../controllers/AuthController.php?action=login">Login here</a></div>
  </div>
<?php else: ?>
  <div class="auth-box">
    <div class="auth-logo">💼</div>
    <h2>Job Portal</h2>
    <p class="auth-sub">Sign in to your account</p>
    <?php if ($flash): ?><div class="alert alert-success"><?= htmlspecialchars($flash) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST" action="../controllers/AuthController.php?action=login">
      <div class="form-group"><label>Email Address</label><input type="email" name="email" value="<?= htmlspecialchars($_POST['email']??'') ?>" required autofocus></div>
      <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In</button>
    </form>
    <div class="auth-footer">No account? <a href="../controllers/AuthController.php?action=register">Register as Job Seeker</a></div>
    <div class="auth-footer" style="margin-top:8px;font-size:.8rem;color:#999;">
      Demo — Admin: admin@jobportal.com / password<br>
      Seeker: seeker@jobportal.com / password
    </div>
  </div>
<?php endif; ?>
</div>
<script src="<?= VIEWS ?>/external.js"></script>
</body>
</html>
<?php // handle logout here too
if ($action === 'logout') { session_destroy(); header('Location: AuthController.php'); exit; }
?>