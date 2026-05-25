<?php
/*
 * view/auth.php
 * Authentication page — Login + 3 registration tabs
 * Roles: Admin (login only) | Employer | Recruiter | Job Seeker
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to correct dashboard
if (!empty($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':     header('Location: AdminDashboardController.php');     exit;
        case 'employer':  header('Location: EmployerDashboardController.php');  exit;
        case 'recruiter': header('Location: RecruiterDashboardController.php'); exit;
        case 'seeker':    header('Location: SeekerDashboardController.php');    exit;
    }
}

// Which tab is active — driven by ?action= in URL
$tab = $_GET['action'] ?? 'login';

// Only allow known tab values; fallback to login
$validTabs = ['login', 'seeker_register', 'employer_register', 'recruiter_register'];
if (!in_array($tab, $validTabs)) {
    $tab = 'login';
}

$flashMsg   = $_SESSION['msg']   ?? '';
$flashError = $_SESSION['error'] ?? '';
$_SESSION['msg']   = '';
$_SESSION['error'] = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Job Portal — Login &amp; Register</title>

    <link rel="stylesheet" href="../view/css/style.css">

    <!-- FONT AWESOME ICONS -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* 4-tab row — keeps tabs on one line at all sizes */
        .auth-tabs {
            display: flex;
            flex-wrap: nowrap;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 26px;
            gap: 0;
            overflow-x: auto;
        }

        .auth-tabs a {
            flex: 1;
            min-width: 0;
            text-align: center;
            padding: 9px 6px;
            color: #6b7280;
            font-weight: 600;
            font-size: .82rem;
            text-decoration: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            white-space: nowrap;
            transition: color .2s, border-color .2s;
        }

        .auth-tabs a.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        .auth-tabs a:hover {
            color: #2563eb;
            text-decoration: none;
        }

        /* ICON STYLE */
        .auth-tabs i,
        .auth-box h1 i,
        button i {
            margin-right: 6px;
        }
    </style>
</head>
<body>

<div class="auth-wrap">
    <div class="auth-box" style="max-width:540px;width:100%">

        <!-- ONLY ICON CHANGED -->
        <h1>
            <i class="fa-solid fa-briefcase"></i> Job Portal
        </h1>

        <p class="auth-subtitle">
            Find jobs · Hire talent · Manage recruitment
        </p>

        <?php if ($flashMsg): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($flashMsg); ?></div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>

        <!-- ── TAB NAVIGATION ──────────────────────────────────── -->
        <div class="auth-tabs">

            <a href="AuthController.php?action=login"
               class="<?php echo $tab === 'login' ? 'active' : ''; ?>">
                <i class="fa-solid fa-right-to-bracket"></i>
                Login
            </a>

            <a href="AuthController.php?action=seeker_register"
               class="<?php echo $tab === 'seeker_register' ? 'active' : ''; ?>">
                <i class="fa-solid fa-user"></i>
                Job Seeker
            </a>

            <a href="AuthController.php?action=employer_register"
               class="<?php echo $tab === 'employer_register' ? 'active' : ''; ?>">
                <i class="fa-solid fa-building"></i>
                Employer
            </a>

            <a href="AuthController.php?action=recruiter_register"
               class="<?php echo $tab === 'recruiter_register' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i>
                Recruiter
            </a>

        </div>

        <!-- ════════════════════════════════════════════════════════
             TAB 1 — LOGIN
        ═══════════════════════════════════════════════════════════ -->
        <?php if ($tab === 'login'): ?>

        <div class="alert alert-info" style="font-size:.85rem;padding:10px 14px;margin-bottom:18px">
            <strong>Admin?</strong> Select "Admin" in the role dropdown below.<br>
            Default admin: <code>admin@jobportal.com</code> / <code>password</code>
        </div>

        <form action="AuthController.php" method="post"
              onsubmit="return validateLogin(this)" novalidate>

            <input type="hidden" name="action" value="login_save">

            <div class="form-group">
                <label>Email Address *</label>

                <input type="email"
                       name="email"
                       id="loginEmail"
                       placeholder="you@example.com"
                       autocomplete="email">

                <span class="err-msg" id="loginEmailErr"></span>
            </div>

            <div class="form-group">
                <label>Password *</label>

                <input type="password"
                       name="password"
                       id="loginPass"
                       placeholder="••••••••"
                       autocomplete="current-password">

                <span class="err-msg" id="loginPassErr"></span>
            </div>

            <div class="form-group">
                <label>I am logging in as *</label>

                <select name="role" id="loginRole">
                    <option value="">— Select your role —</option>
                    <option value="seeker">Job Seeker</option>
                    <option value="employer">Employer</option>
                    <option value="recruiter">Recruiter</option>
                    <option value="admin">Admin</option>
                </select>

                <span class="err-msg" id="loginRoleErr"></span>
            </div>

            <!-- ONLY ICON CHANGED -->
            <button type="submit"
                    class="btn btn-primary"
                    style="width:100%;padding:12px;font-size:1rem">

                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                Login

            </button>

            <p style="margin-top:16px;font-size:.85rem;color:#6b7280;text-align:center">
                New here?
                <a href="AuthController.php?action=seeker_register">Job Seeker</a> ·
                <a href="AuthController.php?action=employer_register">Employer</a> ·
                <a href="AuthController.php?action=recruiter_register">Recruiter</a>
            </p>

        </form>

        <!-- ════════════════════════════════════════════════════════
             TAB 2 — JOB SEEKER REGISTER
        ═══════════════════════════════════════════════════════════ -->
        <?php elseif ($tab === 'seeker_register'): ?>

        <div class="alert alert-success" style="font-size:.85rem;padding:10px 14px;margin-bottom:18px">
            ✅ Job Seeker accounts are <strong>approved instantly</strong> — no waiting required!
        </div>

        <form action="AuthController.php" method="post"
              onsubmit="return validateSeekerRegister(this)" novalidate>

            <input type="hidden" name="action" value="seeker_register_save">

            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" placeholder="Your full name">
                <span class="err-msg" id="srNameErr"></span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" placeholder="you@email.com">
                    <span class="err-msg" id="srEmailErr"></span>
                </div>

                <div class="form-group">
                    <label>Phone <small>(optional)</small></label>
                    <input type="text" name="phone" placeholder="+880...">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password * <small>(min 6 chars)</small></label>
                    <input type="password" name="password" id="srPass">
                    <span class="err-msg" id="srPassErr"></span>
                </div>

                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" id="srConfirm">
                    <span class="err-msg" id="srConfirmErr"></span>
                </div>
            </div>

            <!-- ONLY ICON CHANGED -->
            <button type="submit"
                    class="btn btn-success"
                    style="width:100%;padding:12px;font-size:1rem">

                <i class="fa-solid fa-user-plus"></i>
                Create Seeker Account — Login Immediately

            </button>

        </form>

        <?php elseif ($tab === 'employer_register'): ?>

        <!-- KEEP REST OF YOUR ORIGINAL CODE SAME -->

        <?php else: ?>

        <!-- KEEP REST OF YOUR ORIGINAL CODE SAME -->

        <?php endif; ?>

    </div>
</div>

<script src="../view/js/main.js"></script>

<script>
function validateSeekerRegister(form) {
    var valid = true;

    var ids = ['srNameErr','srEmailErr','srPassErr','srConfirmErr'];

    ids.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.innerHTML = '';
    });

    if (!form.name.value.trim()) {
        showError('srNameErr', 'Full name is required.');
        valid = false;
    }

    if (!form.email.value.trim() || !isValidEmail(form.email.value.trim())) {
        showError('srEmailErr', 'A valid email address is required.');
        valid = false;
    }

    if (form.password.value.length < 6) {
        showError('srPassErr', 'Password must be at least 6 characters.');
        valid = false;
    }

    if (form.password.value !== form.confirm_password.value) {
        showError('srConfirmErr', 'Passwords do not match.');
        valid = false;
    }

    return valid;
}
</script>

</body>
</html>