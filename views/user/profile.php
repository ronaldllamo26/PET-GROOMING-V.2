<?php
// views/user/profile.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
requireUser();

$pdo  = getPDO();
$user = currentUser($pdo);
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'info') {
        $name  = clean($_POST['name']  ?? '');
        $phone = clean($_POST['phone'] ?? '');
        if (!$name) { $error = 'Name is required.'; }
        else {
            $pdo->prepare('UPDATE users SET name=?, phone=? WHERE id=?')->execute([$name, $phone, $user['id']]);
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated.';
            $user = currentUser($pdo);
        }
    }
    if ($action === 'password') {
        $cur = $_POST['current'] ?? '';
        $new = $_POST['new']     ?? '';
        $con = $_POST['confirm'] ?? '';
        if (!password_verify($cur, $user['password'])) { $error = 'Current password is incorrect.'; }
        elseif (strlen($new) < 6) { $error = 'New password must be at least 6 characters.'; }
        elseif ($new !== $con)    { $error = 'Passwords do not match.'; }
        else {
            $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            $success = 'Password updated successfully.';
        }
    }
}

// ✅ Get full user data
$userFullStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$userFullStmt->execute([$user['id']]);
$userFull = $userFullStmt->fetch();

$pageTitle = 'My Profile';
$rootPath  = '../../';
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include '../../includes/head.php'; ?></head>
<body>
<div class="layout">
  <?php include '../../includes/sidebar_user.php'; ?>
  <div class="main-wrap">
    <div class="topbar">
      <span class="topbar-title">My Profile</span>
      <div class="user-meta">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=EDE6DC&color=3B2F2F&size=64" class="user-avatar"/>
        <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
      </div>
    </div>
    <div class="content-pad">

      <?php if ($error): ?>
      <div class="alert-site alert-error mb-3"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
      <div class="alert-site alert-success mb-3"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <div class="row g-4">
        <!-- Personal Info -->
        <div class="col-md-6">
          <div class="card-site">
            <div class="card-header-site"><h5>Personal Information</h5></div>
            <div class="card-body-site">
              <form method="POST">
                <input type="hidden" name="action" value="info"/>
                <div class="mb-3">
                  <label class="form-label-site">Full Name</label>
                  <input type="text" name="name" class="form-control-site" value="<?= htmlspecialchars($user['name']) ?>" required/>
                </div>
                <div class="mb-3">
                  <label class="form-label-site">Email</label>
                  <input type="email" class="form-control-site" value="<?= htmlspecialchars($user['email']) ?>" disabled/>
                </div>
                <div class="mb-4">
                  <label class="form-label-site">Phone</label>
                  <input type="tel" name="phone" class="form-control-site" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="0917-xxx-xxxx"/>
                </div>
                <button type="submit" class="btn-primary-site btn-sm-site">Save Changes</button>
              </form>
            </div>
          </div>
        </div>

        <!-- Change Password -->
        <div class="col-md-6">
          <div class="card-site">
            <div class="card-header-site"><h5>Change Password</h5></div>
            <div class="card-body-site">
              <form method="POST">
                <input type="hidden" name="action" value="password"/>
                <div class="mb-3">
                  <label class="form-label-site">Current Password</label>
                  <input type="password" name="current" class="form-control-site" required/>
                </div>
                <div class="mb-3">
                  <label class="form-label-site">New Password</label>
                  <input type="password" name="new" class="form-control-site" placeholder="Min. 6 characters" required/>
                </div>
                <div class="mb-4">
                  <label class="form-label-site">Confirm New Password</label>
                  <input type="password" name="confirm" class="form-control-site" required/>
                </div>
                <button type="submit" class="btn-primary-site btn-sm-site">Update Password</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ Account Status Card -->
      <div class="card-site mt-4">
        <div class="card-header-site"><h5>Account Status</h5></div>
        <div class="card-body-site">
          <div class="row g-3">

            <div class="col-md-4">
              <div style="background:var(--cream);border-radius:var(--radius);padding:1rem 1.2rem;">
                <p style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;">Member Since</p>
                <p style="font-size:0.95rem;font-weight:600;color:var(--brown);margin:0;">
                  <?= date('F Y', strtotime($userFull['created_at'])) ?>
                </p>
              </div>
            </div>

            <div class="col-md-4">
              <div style="background:var(--cream);border-radius:var(--radius);padding:1rem 1.2rem;">
                <p style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;">No-Show Record</p>
                <p style="font-size:0.95rem;font-weight:600;margin:0;
                  <?= $userFull['no_show_count'] >= 1 ? 'color:#e6a817;' : 'color:#27ae60;' ?>">
                  <?= $userFull['no_show_count'] ?> / 2
                  <?= $userFull['no_show_count'] >= 1 ? '⚠️' : '✓' ?>
                </p>
              </div>
            </div>

            <div class="col-md-4">
              <div style="background:var(--cream);border-radius:var(--radius);padding:1rem 1.2rem;">
                <p style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;">Account Status</p>
                <p style="font-size:0.95rem;font-weight:600;margin:0;
                  <?= $userFull['is_suspended'] ? 'color:#c0392b;' : 'color:#27ae60;' ?>">
                  <?= $userFull['is_suspended'] ? '🚫 Suspended' : '✓ Active' ?>
                </p>
              </div>
            </div>

          </div>

          <?php if ($userFull['is_suspended']): ?>
          <div style="background:#fdecea;border:1px solid #f5c6cb;border-radius:var(--radius);padding:1rem 1.2rem;margin-top:1rem;font-size:0.85rem;color:#922b21;">
            🚫 Your account is currently suspended. Please contact us at <strong>hello@pawcare.ph</strong> or <strong>0917-123-4567</strong> to appeal.
          </div>
          <?php elseif ($userFull['no_show_count'] === 1): ?>
          <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:var(--radius);padding:1rem 1.2rem;margin-top:1rem;font-size:0.85rem;color:#7c5c3e;">
            ⚠️ You have <strong>1 no-show</strong> on record. A 2nd no-show will result in account suspension.
          </div>
          <?php endif; ?>

        </div>
      </div>

    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>