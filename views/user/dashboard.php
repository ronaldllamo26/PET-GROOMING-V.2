<?php
// views/user/dashboard.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
requireUser();

$pdo = getPDO();
$uid = $_SESSION['user_id'];

$petsCount  = $pdo->prepare('SELECT COUNT(*) FROM pets WHERE user_id = ?');
$petsCount->execute([$uid]);
$pets = $petsCount->fetchColumn();

$apptCount  = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE user_id = ?');
$apptCount->execute([$uid]);
$total = $apptCount->fetchColumn();

$upcoming = $pdo->prepare("
    SELECT a.*, s.name AS service_name, p.name AS pet_name
    FROM appointments a
    JOIN services s ON a.service_id = s.id
    JOIN pets p ON a.pet_id = p.id
    WHERE a.user_id = ? AND a.status IN ('pending','confirmed') AND a.appt_date >= CURDATE()
    ORDER BY a.appt_date ASC, a.appt_time ASC LIMIT 6
");
$upcoming->execute([$uid]);
$appts = $upcoming->fetchAll();

$statusMap = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed','done'=>'badge-done','cancelled'=>'badge-cancelled'];

// ✅ Get user's no-show count and suspension status
$userStmt = $pdo->prepare('SELECT no_show_count, is_suspended FROM users WHERE id = ?');
$userStmt->execute([$uid]);
$userData = $userStmt->fetch();

$pageTitle = 'Dashboard';
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
      <span class="topbar-title">Dashboard</span>
      <div class="user-meta">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=EDE6DC&color=3B2F2F&size=64" alt="avatar" class="user-avatar"/>
        <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
      </div>
    </div>

    <div class="content-pad">
<?php if ($userData['is_suspended']): ?>
<div style="background:#fdecea;border:1px solid #f5c6cb;border-radius:var(--radius);padding:1rem 1.2rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:0.8rem;">
  <span style="font-size:1.3rem;">🚫</span>
  <div>
    <strong style="color:#c0392b;font-size:0.9rem;">Account Suspended</strong>
    <p style="color:#922b21;font-size:0.83rem;margin:0.2rem 0 0;">Your account has been suspended due to repeated no-shows. You cannot make new bookings. Please contact us to appeal.</p>
  </div>
</div>
<?php elseif ($userData['no_show_count'] === 1): ?>
<div style="background:#fff8e1;border:1px solid #ffe082;border-radius:var(--radius);padding:1rem 1.2rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:0.8rem;">
  <span style="font-size:1.3rem;">⚠️</span>
  <div>
    <strong style="color:#7c5c3e;font-size:0.9rem;">No-Show Warning</strong>
    <p style="color:#7c5c3e;font-size:0.83rem;margin:0.2rem 0 0;">You have <strong>1 no-show</strong> on record. A 2nd no-show will result in account suspension. Please cancel at least 24 hours in advance if you cannot attend.</p>
  </div>
</div>
<?php endif; ?> 
 
      <div class="mb-4">
        <h3 style="font-family:var(--font-display);font-size:1.7rem;font-weight:600;">Good day, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>.</h3>
        <p style="color:var(--text-muted);font-size:0.9rem;margin:0;">Here is a summary of your PawCare account.</p>
      </div>

      <!-- Stats -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-icon">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
            </div>
            <div>
              <div class="stat-num"><?= $pets ?></div>
              <div class="stat-label">My Pets</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-icon">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
            </div>
            <div>
              <div class="stat-num"><?= count($appts) ?></div>
              <div class="stat-label">Upcoming</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-icon">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
            </div>
            <div>
              <div class="stat-num"><?= $total ?></div>
              <div class="stat-label">Total Bookings</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Upcoming Appointments -->
      <div class="card-site mb-4">
        <div class="card-header-site">
          <h5>Upcoming Appointments</h5>
          <a href="book.php" class="btn-primary-site btn-sm-site">+ Book New</a>
        </div>
        <?php if (empty($appts)): ?>
        <div class="card-body-site text-center py-5">
          <svg width="48" height="48" fill="none" stroke="var(--gray-light)" stroke-width="1" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
          <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:1.2rem;">No upcoming appointments.</p>
          <a href="book.php" class="btn-primary-site btn-sm-site">Book Your First Session</a>
        </div>
        <?php else: ?>
        <table class="table-site">
          <thead>
            <tr>
              <th>Pet</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($appts as $a): ?>
            <tr>
              <td><strong><?= htmlspecialchars($a['pet_name']) ?></strong></td>
              <td><?= htmlspecialchars($a['service_name']) ?></td>
              <td><?= date('M j, Y', strtotime($a['appt_date'])) ?></td>
              <td><?= date('g:i A', strtotime($a['appt_time'])) ?></td>
              <td><span class="badge-site <?= $statusMap[$a['status']] ?? '' ?>"><?= ucfirst($a['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

      <!-- Quick Actions -->
      <div class="row g-3">
        <div class="col-md-6">
          <div class="card-site">
            <div class="card-header-site"><h5>Quick Actions</h5></div>
            <div class="card-body-site d-flex flex-column gap-2">
              <a href="book.php" class="btn-primary-site text-center" style="padding:0.7rem;">Book an Appointment</a>
              <a href="my-pets.php" class="btn-outline-site text-center" style="padding:0.7rem;">Add a Pet</a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card-site">
            <div class="card-header-site"><h5>Contact Us</h5></div>
            <div class="card-body-site">
              <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:1rem;">Need to reschedule or have a question? We're here to help.</p>
              <p style="font-size:0.88rem;color:var(--text);margin-bottom:0.4rem;font-weight:500;">0917-123-4567</p>
              <p style="font-size:0.88rem;color:var(--text);margin:0;font-weight:500;">hello@pawcare.ph</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
