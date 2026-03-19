<?php
require_once 'config/database.php';
require_once 'config/auth.php';
require_once 'config/mailer.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'views/admin/dashboard.php' : 'views/user/dashboard.php'));
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean($_POST['name']     ?? '');
    $email    = clean($_POST['email']    ?? '');
    $phone    = clean($_POST['phone']    ?? '');
    $password = $_POST['password']       ?? '';
    $confirm  = $_POST['confirm']        ?? '';

    if (!$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $pdo   = getPDO();
            $check = $pdo->prepare('SELECT id, is_verified FROM users WHERE email = ?');
            $check->execute([$email]);
            $existing = $check->fetch();

            if ($existing && $existing['is_verified']) {
                $error = 'This email is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $otp  = strval(rand(100000, 999999));
                $exp  = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                if ($existing && !$existing['is_verified']) {
                    // Update lang kung nag-register na siya dati pero hindi pa na-verify
                    $pdo->prepare('UPDATE users SET name=?, phone=?, password=?, otp_code=?, otp_expires_at=? WHERE email=?')
                        ->execute([$name, $phone, $hash, $otp, $exp, $email]);
                } else {
                    // Brand new user
                    $pdo->prepare('INSERT INTO users (name, email, phone, password, role, is_verified, otp_code, otp_expires_at) VALUES (?,?,?,?,?,?,?,?)')
                        ->execute([$name, $email, $phone, $hash, 'user', 0, $otp, $exp]);
                }

                // Send OTP Email
                $subject = 'PawCare – Your Verification Code';
                $body    = "
                    <div style='font-family:sans-serif;max-width:480px;margin:auto;'>
                        <h2 style='color:#7c5c3e;'>Welcome to PawCare! 🐾</h2>
                        <p>Hi <strong>{$name}</strong>, your verification code is:</p>
                        <div style='font-size:2.5rem;font-weight:bold;letter-spacing:10px;color:#7c5c3e;margin:20px 0;'>{$otp}</div>
                        <p style='color:#888;font-size:0.85rem;'>This code expires in <strong>10 minutes</strong>.</p>
                        <p style='color:#888;font-size:0.85rem;'>If you did not create an account, ignore this email.</p>
                    </div>
                ";

                if (sendMail($email, $name, $subject, $body)) {
                    // Store email in session para ma-access sa verify page
                    session_start();
                    $_SESSION['otp_email'] = $email;
                    header('Location: verify.php');
                    exit;
                } else {
                    $error = 'Failed to send verification email. Please try again.';
                }
            }
        } catch (Exception $e) {
            $error = 'A server error occurred. Please try again.';
        }
    }
}

$rootPath  = '';
$pageTitle = 'Create Account';
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
      <p style="font-size:0.7rem;letter-spacing:2.5px;text-transform:uppercase;color:var(--tan);margin-bottom:0.8rem;">Join PawCare</p>
      <h2>Start your pet's grooming journey today.</h2>
      <p class="mt-3">Create a free account and book your first grooming session in under 2 minutes.</p>
    </div>
  </div>

  <div class="auth-right" style="width: 520px;">
    <div class="auth-box" style="max-width: 420px;">

      <a href="index.php" class="auth-logo">PawCare</a>
      <h2 class="auth-title">Create account</h2>
      <p class="auth-sub">Free to sign up. No credit card required.</p>

      <?php if ($error): ?>
      <div class="alert-site alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
      <div class="alert-site alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <div class="mb-3">
          <label class="form-label-site">Full Name *</label>
          <input type="text" name="name" class="form-control-site" placeholder="Your full name"
                 value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required/>
        </div>
        <div class="mb-3">
          <label class="form-label-site">Email Address *</label>
          <input type="email" name="email" class="form-control-site" placeholder="your@email.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
        </div>
        <div class="mb-3">
          <label class="form-label-site">Phone Number</label>
          <input type="tel" name="phone" class="form-control-site" placeholder="0917-xxx-xxxx"
                 value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"/>
        </div>
        <div class="row g-3 mb-4">
          <div class="col-6">
            <label class="form-label-site">Password *</label>
            <input type="password" name="password" class="form-control-site" placeholder="Min. 6 chars" required/>
          </div>
          <div class="col-6">
            <label class="form-label-site">Confirm *</label>
            <input type="password" name="confirm" class="form-control-site" placeholder="Repeat" required/>
          </div>
        </div>

        <button type="submit" class="btn-primary-site w-100" style="padding:0.8rem;font-size:0.82rem;">
          Create Account
        </button>
      </form>

      <div style="text-align:center;margin-top:1.8rem;font-size:0.85rem;color:var(--text-muted);">
        Already have an account?
        <a href="login.php" style="color:var(--brown);font-weight:500;">Sign in</a>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>