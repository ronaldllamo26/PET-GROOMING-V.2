<?php
require_once 'config/database.php';
require_once 'config/auth.php';
require_once 'config/mailer.php';

if (isLoggedIn()) {
    header('Location: views/user/dashboard.php');
    exit;
}


$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $pdo  = getPDO();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_verified = 1 LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Same message kahit wala para hindi malaman ng attacker
            if ($user) {
                $otp = strval(rand(100000, 999999));
                $exp = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                $pdo->prepare('UPDATE users SET otp_code=?, otp_expires_at=? WHERE email=?')
                    ->execute([$otp, $exp, $email]);

                $subject = 'PawCare – Password Reset Code';
                $body    = "
                    <div style='font-family:sans-serif;max-width:480px;margin:auto;'>
                        <h2 style='color:#7c5c3e;'>Password Reset 🐾</h2>
                        <p>Hi <strong>{$user['name']}</strong>, your password reset code is:</p>
                        <div style='font-size:2.5rem;font-weight:bold;letter-spacing:10px;color:#7c5c3e;margin:20px 0;'>{$otp}</div>
                        <p style='color:#888;font-size:0.85rem;'>This code expires in <strong>10 minutes</strong>.</p>
                        <p style='color:#888;font-size:0.85rem;'>If you did not request this, ignore this email.</p>
                    </div>
                ";

                sendMail($email, $user['name'], $subject, $body);
                $_SESSION['reset_email'] = $email;
                header('Location: verify-reset.php');
                exit;
            }

        } catch (Exception $e) {
            $error = 'A server error occurred. Please try again.';
        }
    }
}

$rootPath  = '';
$pageTitle = 'Forgot Password';
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
      <h2>Forgot your password?</h2>
      <p class="mt-3">No worries! Enter your email and we'll send you a reset code.</p>
    </div>
  </div>

  <div class="auth-right" style="width:520px;">
    <div class="auth-box" style="max-width:420px;">

      <a href="index.php" class="auth-logo">PawCare</a>
      <h2 class="auth-title">Forgot password</h2>
      <p class="auth-sub">Enter your registered email address.</p>

      <?php if ($error): ?>
      <div class="alert-site alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
      <div class="alert-site alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" action="forgot-password.php">
        <div class="mb-4">
          <label class="form-label-site">Email Address</label>
          <input type="email" name="email" class="form-control-site"
                 placeholder="your@email.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
        </div>
        <button type="submit" class="btn-primary-site w-100" style="padding:0.8rem;font-size:0.82rem;">
          Send Reset Code
        </button>
      </form>

      <div style="text-align:center;margin-top:1.5rem;font-size:0.85rem;color:var(--text-muted);">
        Remembered it?
        <a href="login.php" style="color:var(--brown);font-weight:500;">Sign in</a>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>