<?php
require_once 'config/database.php';
require_once 'config/auth.php';

session_start();

if (isLoggedIn()) {
    header('Location: views/user/dashboard.php');
    exit;
}

// Kung walang session ng email, i-redirect sa register
if (empty($_SESSION['otp_email'])) {
    header('Location: register.php');
    exit;
}

$email = $_SESSION['otp_email'];
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');

    try {
        $pdo  = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'User not found.';
        } elseif ($user['is_verified']) {
            $success = 'Email already verified! Redirecting...';
            header('refresh:2;url=login.php');
        } elseif ($user['otp_code'] !== $otp) {
            $error = 'Invalid OTP code. Please try again.';
        } elseif (strtotime($user['otp_expires_at']) < time()) {
            $error = 'OTP has expired. Please register again.';
        } else {
            // Mark as verified
            $pdo->prepare('UPDATE users SET is_verified=1, otp_code=NULL, otp_expires_at=NULL WHERE email=?')
                ->execute([$email]);

            unset($_SESSION['otp_email']);
            $success = 'Email verified! Redirecting to login...';
            header('refresh:2;url=login.php');
        }
    } catch (Exception $e) {
        $error = 'A server error occurred.';
    }
}

$rootPath  = '';
$pageTitle = 'Verify Email';
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
      <p style="font-size:0.7rem;letter-spacing:2.5px;text-transform:uppercase;color:var(--tan);margin-bottom:0.8rem;">Almost there!</p>
      <h2>Verify your email to get started.</h2>
      <p class="mt-3">Check your inbox for a 6-digit code from PawCare.</p>
    </div>
  </div>

  <div class="auth-right" style="width:520px;">
    <div class="auth-box" style="max-width:420px;">

      <a href="index.php" class="auth-logo">PawCare</a>
      <h2 class="auth-title">Check your email</h2>
      <p class="auth-sub">We sent a 6-digit code to <strong><?= htmlspecialchars($email) ?></strong></p>

      <?php if ($error): ?>
      <div class="alert-site alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
      <div class="alert-site alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" action="verify.php">
        <div class="mb-4">
          <label class="form-label-site">Enter OTP Code</label>
          <input type="text" name="otp" class="form-control-site"
                 placeholder="xxxxxx" maxlength="6"
                 style="font-size:1.5rem;letter-spacing:8px;text-align:center;" required/>
        </div>
        <button type="submit" class="btn-primary-site w-100" style="padding:0.8rem;font-size:0.82rem;">
          Verify Email
        </button>
      </form>

      <div style="text-align:center;margin-top:1.5rem;font-size:0.85rem;color:var(--text-muted);">
        Wrong email?
        <a href="register.php" style="color:var(--brown);font-weight:500;">Go back</a>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>