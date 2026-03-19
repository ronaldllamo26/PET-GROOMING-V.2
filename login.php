<?php
// login.php
require_once 'config/database.php';
require_once 'config/auth.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'views/admin/dashboard.php' : 'views/user/dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $pdo  = getPDO();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
    
    // ✅ Check if email is verified
    if (!$user['is_verified'] && $user['role'] !== 'admin') {
    $error = 'Please verify your email first before logging in.';
    
    // ✅ Check if account is suspended
    } elseif ($user['is_suspended']) {
        $error = 'Your account has been suspended due to repeated no-shows. Please contact us to appeal.';
    
    } else {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role']      = $user['role'];
        header('Location: ' . ($user['role'] === 'admin' ? 'views/admin/dashboard.php' : 'views/user/dashboard.php'));
        exit;
    }

} else {
    $error = 'Invalid email address or password.';
}
        } catch (Exception $e) {
    $error = $e->getMessage(); // ← palitan ito
}
    }
}

$rootPath = '';
$pageTitle = 'Sign In';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/head.php'; ?>
</head>
<body>

<div class="auth-page">

  <!-- LEFT PANEL -->
  <div class="auth-left d-none d-lg-flex">
    <img
      src="https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=900&q=80&auto=format&fit=crop"
      alt="Dog grooming"
      class="auth-left-img"
    />
    <div class="auth-left-content">
      <p style="font-size:0.7rem;letter-spacing:2.5px;text-transform:uppercase;color:var(--tan);margin-bottom:0.8rem;">PetMalu Grooming Studio</p>
      <h2>Your pet's comfort is our priority.</h2>
      <p class="mt-3">Sign in to manage appointments, view your pet profiles, and track grooming history all in one place.</p>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="auth-right">
    <div class="auth-box">

      <a href="index.php" class="auth-logo">PetMalu</a>

      <h2 class="auth-title">Welcome back</h2>
      <p class="auth-sub">Sign in to your account to continue</p>

      <?php if ($error): ?>
      <div class="alert-site alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="mb-3">
          <label class="form-label-site">Email Address</label>
          <input
            type="email"
            name="email"
            class="form-control-site"
            placeholder="your@email.com"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            required
            autocomplete="email"
          />
        </div>

        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label-site" style="margin:0;">Password</label>
          </div>
          <div style="position:relative;">
            <input
              type="password"
              name="password"
              id="pwField"
              class="form-control-site"
              placeholder="Enter your password"
              required
              autocomplete="current-password"
              style="padding-right: 2.8rem;"
            />
            <button type="button" onclick="togglePw()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;">
              <svg id="pwIcon" fill="none" stroke="var(--gray)" stroke-width="1.5" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-primary-site w-100" style="padding: 0.8rem; font-size: 0.82rem;">
          Sign In
        </button>
      </form>

      <div style="text-align:center;margin-top:1.8rem;font-size:0.85rem;color:var(--text-muted);">
        Don't have an account?
<a href="register.php" style="color:var(--brown);font-weight:500;">Create one</a>
</div>

<div style="text-align:center;margin-top:0.8rem;font-size:0.85rem;color:var(--text-muted);">
  Forgot your password?
  <a href="forgot-password.php" style="color:var(--brown);font-weight:500;">Reset it</a>

      <div style="text-align:center;margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid var(--gray-light);font-size:0.72rem;color:var(--gray);letter-spacing:0.5px;">
        By signing in, you agree to our Terms of Service and Privacy Policy.
      </div>

    </div>
  </div>

</div>

<script>
function togglePw() {
  const f = document.getElementById('pwField');
  f.type = f.type === 'password' ? 'text' : 'password';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
