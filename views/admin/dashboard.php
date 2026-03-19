<?php
// views/admin/dashboard.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
requireAdmin();

$pdo = getPDO();

$totalUsers   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalAppts   = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$todayAppts   = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appt_date=CURDATE()")->fetchColumn();
$pendingAppts = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();

$recent = $pdo->query("
    SELECT a.*, u.name AS client, p.name AS pet_name, s.name AS service_name
    FROM appointments a
    JOIN users u ON a.user_id=u.id
    JOIN pets p ON a.pet_id=p.id
    JOIN services s ON a.service_id=s.id
    ORDER BY a.created_at DESC LIMIT 8
")->fetchAll();

$statusMap = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed','done'=>'badge-done','cancelled'=>'badge-cancelled'];

$pageTitle = 'Admin Dashboard';
$rootPath  = '../../';
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include '../../includes/head.php'; ?></head>
<body>
<div class="layout">
  <?php include '../../includes/sidebar_admin.php'; ?>
  <div class="main-wrap">
    <div class="topbar">
      <span class="topbar-title">Dashboard</span>
      <div class="user-meta">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=3B2F2F&color=C4A882&size=64" class="user-avatar"/>
        <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
        <span class="badge-site badge-admin" style="margin-left:4px;">Admin</span>
      </div>
    </div>

    <div class="content-pad">
      <div class="mb-4">
        <h3 style="font-family:var(--font-display);font-size:1.7rem;font-weight:600;">Good day, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>.</h3>
        <p style="color:var(--text-muted);font-size:0.9rem;margin:0;">Overview of PawCare operations.</p>
      </div>

      <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['num'=>$totalUsers,   'label'=>'Total Customers',    'icon'=>'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
            ['num'=>$totalAppts,   'label'=>'Total Appointments', 'icon'=>'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
            ['num'=>$todayAppts,   'label'=>"Today's Appointments",'icon'=>'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['num'=>$pendingAppts, 'label'=>'Pending Approval',   'icon'=>'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'],
        ];
        foreach ($cards as $c): ?>
        <div class="col-md-3 col-6">
          <div class="stat-card">
            <div class="stat-icon">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" d="<?= $c['icon'] ?>"/>
              </svg>
            </div>
            <div>
              <div class="stat-num"><?= $c['num'] ?></div>
              <div class="stat-label"><?= $c['label'] ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="card-site">
        <div class="card-header-site">
          <h5>Recent Appointments</h5>
          <a href="appointments.php" class="btn-outline-site btn-sm-site">View All</a>
        </div>
        <?php if (empty($recent)): ?>
        <div class="card-body-site text-center py-4">
          <p style="color:var(--text-muted);">No appointments yet.</p>
        </div>
        <?php else: ?>
        <table class="table-site">
          <thead>
            <tr><th>Client</th><th>Pet</th><th>Service</th><th>Date</th><th>Status</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach ($recent as $a): ?>
          <tr>
            <td><strong><?= htmlspecialchars($a['client']) ?></strong></td>
            <td><?= htmlspecialchars($a['pet_name']) ?></td>
            <td><?= htmlspecialchars($a['service_name']) ?></td>
            <td><?= date('M j, Y', strtotime($a['appt_date'])) ?></td>
            <td><span class="badge-site <?= $statusMap[$a['status']]??'' ?>"><?= ucfirst($a['status']) ?></span></td>
            <td><a href="appointments.php" class="btn-ghost" style="font-size:0.72rem;padding:0.3rem 0.8rem;">Manage</a></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
