<?php
require_once 'config/database.php';
require_once 'config/auth.php';

session_start();

if (isLoggedIn()) {
    header('Location: views/user/dashboard.php');
    exit;
}

// Must have passed OTP verify step
if (empty($_SESSION['reset_email']) || empty($_SESSION['reset_verified'])) {
    header('Location: forgot-password.php');
    exit;
}

$email = $_SESSION['reset_email'];
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $pdo  = getPDO();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password=? WHERE email=?')
                ->execute([$hash, $email]);

            // Clear session
            unset($_SESSION['reset_email'], $_SESSION['reset_verified']);

            $success = 'Password updated! Redirecting to login...';
            header('refresh:2;url=login.php');
        } catch (Exception $e) {
            $error = 'A server error occurred.';
        }
    }
}

$rootPath  = '';
$pageTitle = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/head.php'; ?>
</head>
<body>

<div class="auth-page">
  <div class="auth-left d-none d-lg-flex">
    <img src="https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=900&q=80&auto=format&fit=crop" alt="Dog" class="auth-left-img"/>
    <div class="auth-left-content">
      <p style="font-size:0.7rem;letter-spacing:2.5px;text-transform:uppercase;color:var(--tan);margin-bottom:0.8rem;">PawCare</p>
      <h2>Create a new password.</h2>
      <p class="mt-3">Make sure it's at least 6 characters long.</p>
    </div>
  </div>

  <div class="auth-right" style="width:520px;">
    <div class="auth-box" style="max-width:420px;">

      <a href="index.php" class="auth-logo">PawCare</a>
      <h2 class="auth-title">Reset password</h2>
      <p class="auth-sub">Choose a strong new password.</p>

      <?php if ($error): ?>
      <div class="alert-site alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
      <div class="alert-site alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" action="reset-password.php">
        <div class="mb-3">
          <label class="form-label-site">New Password</label>
          <input type="password" name="password" class="form-control-site"
                 placeholder="Min. 6 characters" required/>
        </div>
        <div class="mb-4">
          <label class="form-label-site">Confirm Password</label>
          <input type="password" name="confirm" class="form-control-site"
                 placeholder="Repeat password" required/>
        </div>
        <button type="submit" class="btn-primary-site w-100" style="padding:0.8rem;font-size:0.82rem;">
          Update Password
        </button>
      </form>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>