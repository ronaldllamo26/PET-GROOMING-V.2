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
      <div class="alert-site alert-error">
        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>
      <?php if ($success): ?>
      <div class="alert-site alert-success">
        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <?= htmlspecialchars($success) ?>
      </div>
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

      <!-- Account Status Card -->
      <div class="card-site mt-4">
        <div class="card-header-site"><h5>Account Status</h5></div>
        <div class="card-body-site">

          <div class="row g-3 mb-3">

            <!-- Member Since -->
            <div class="col-md-4">
              <div style="background:var(--cream);border-radius:var(--radius);padding:1rem 1.2rem;border:1px solid var(--gray-light);">
                <p style="font-size:0.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1.2px;margin-bottom:0.4rem;font-weight:600;">Member Since</p>
                <p style="font-size:0.92rem;font-weight:600;color:var(--brown);margin:0;">
                  <?= date('F Y', strtotime($userFull['created_at'])) ?>
                </p>
              </div>
            </div>

            <!-- No-Show Record -->
            <div class="col-md-4">
              <div style="background:var(--cream);border-radius:var(--radius);padding:1rem 1.2rem;border:1px solid var(--gray-light);">
                <p style="font-size:0.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1.2px;margin-bottom:0.4rem;font-weight:600;">No-Show Record</p>
                <div style="display:flex;align-items:center;gap:6px;">
                  <?php if ($userFull['no_show_count'] >= 1): ?>
                  <svg fill="none" stroke="#8B6914" stroke-width="1.5" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                  </svg>
                  <p style="font-size:0.92rem;font-weight:600;color:#8B6914;margin:0;"><?= $userFull['no_show_count'] ?> / 2</p>
                  <?php else: ?>
                  <svg fill="none" stroke="#3A6B47" stroke-width="1.5" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <p style="font-size:0.92rem;font-weight:600;color:#3A6B47;margin:0;"><?= $userFull['no_show_count'] ?> / 2</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Account Status -->
            <div class="col-md-4">
              <div style="background:var(--cream);border-radius:var(--radius);padding:1rem 1.2rem;border:1px solid var(--gray-light);">
                <p style="font-size:0.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1.2px;margin-bottom:0.4rem;font-weight:600;">Account Status</p>
                <div style="display:flex;align-items:center;gap:6px;">
                  <?php if ($userFull['is_suspended']): ?>
                  <svg fill="none" stroke="#7A2020" stroke-width="1.5" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                  </svg>
                  <p style="font-size:0.92rem;font-weight:600;color:#7A2020;margin:0;">Suspended</p>
                  <?php else: ?>
                  <svg fill="none" stroke="#3A6B47" stroke-width="1.5" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <p style="font-size:0.92rem;font-weight:600;color:#3A6B47;margin:0;">Active</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

          </div>

          <!-- Status Banner -->
          <?php if ($userFull['is_suspended']): ?>
          <div class="status-banner danger">
            <div class="status-banner-icon">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
              </svg>
            </div>
            <div>
              <p class="status-banner-title">Account Suspended</p>
              <p class="status-banner-desc">Your account is currently suspended. Please contact us at <strong>hello@pawcare.ph</strong> or <strong>0917-123-4567</strong> to appeal.</p>
            </div>
          </div>
          <?php elseif ($userFull['no_show_count'] === 1): ?>
          <div class="status-banner warning">
            <div class="status-banner-icon">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
              </svg>
            </div>
            <div>
              <p class="status-banner-title">No-Show Warning</p>
              <p class="status-banner-desc">You have <strong>1 no-show</strong> on record. A 2nd no-show will result in account suspension. Please cancel at least 24 hours in advance.</p>
            </div>
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