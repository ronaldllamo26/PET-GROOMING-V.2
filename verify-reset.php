<?php
require_once 'config/database.php';
require_once 'config/auth.php';



if (isLoggedIn()) {
    header('Location: views/user/dashboard.php');
    exit;
}

if (empty($_SESSION['reset_email'])) {
    header('Location: forgot-password.php');
    exit;
}

$email = $_SESSION['reset_email'];
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
        } elseif ($user['otp_code'] !== $otp) {
            $error = 'Invalid OTP code. Please try again.';
        } elseif (strtotime($user['otp_expires_at']) < time()) {
            $error = 'OTP has expired. Please request a new one.';
        } else {
            // OTP valid — clear it, allow reset
            $pdo->prepare('UPDATE users SET otp_code=NULL, otp_expires_at=NULL WHERE email=?')
                ->execute([$email]);

            $_SESSION['reset_verified'] = true;
            header('Location: reset-password.php');
            exit;
        }
    } catch (Exception $e) {
        $error = 'A server error occurred.';
    }
}

$rootPath  = '';
$pageTitle = 'Verify Reset Code';
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
      <h2>Enter your reset code.</h2>
      <p class="mt-3">Check your inbox for the 6-digit code we sent you.</p>
    </div>
  </div>

  <div class="auth-right" style="width:520px;">
    <div class="auth-box" style="max-width:420px;">

      <a href="index.php" class="auth-logo">PawCare</a>
      <h2 class="auth-title">Enter reset code</h2>
      <p class="auth-sub">Sent to <strong><?= htmlspecialchars($email) ?></strong></p>

      <?php if ($error): ?>
      <div class="alert-site alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="verify-reset.php">
        <div class="mb-4">
          <label class="form-label-site">OTP Code</label>
          <input type="text" name="otp" class="form-control-site"
                 placeholder="xxxxxx" maxlength="6"
                 style="font-size:1.5rem;letter-spacing:8px;text-align:center;" required/>
        </div>
        <button type="submit" class="btn-primary-site w-100" style="padding:0.8rem;font-size:0.82rem;">
          Verify Code
        </button>
      </form>

      <div style="text-align:center;margin-top:1.5rem;font-size:0.85rem;color:var(--text-muted);">
        Didn't receive a code?
        <a href="forgot-password.php" style="color:var(--brown);font-weight:500;">Try again</a>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>