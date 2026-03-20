<?php
// views/admin/dashboard.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
requireAdmin();

$pdo = getPDO();

$totalUsers      = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalAppts      = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$todayAppts      = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appt_date=CURDATE()")->fetchColumn();
$pendingAppts    = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();
$suspendedUsers  = $pdo->query("SELECT COUNT(*) FROM users WHERE is_suspended=1")->fetchColumn();
$totalPets       = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();

$totalRevenue = $pdo->query("
    SELECT COALESCE(SUM(s.price), 0) FROM appointments a 
    JOIN services s ON a.service_id = s.id WHERE a.status = 'done'
")->fetchColumn();

$monthRevenue = $pdo->query("
    SELECT COALESCE(SUM(s.price), 0) FROM appointments a 
    JOIN services s ON a.service_id = s.id 
    WHERE a.status = 'done' AND MONTH(a.appt_date) = MONTH(CURDATE()) AND YEAR(a.appt_date) = YEAR(CURDATE())
")->fetchColumn();

$todayRevenue = $pdo->query("
    SELECT COALESCE(SUM(s.price), 0) FROM appointments a 
    JOIN services s ON a.service_id = s.id 
    WHERE a.status = 'done' AND a.appt_date = CURDATE()
")->fetchColumn();

$pendingDeposits = $pdo->query("
    SELECT COUNT(*) FROM appointments WHERE deposit_status = 'uploaded' AND status = 'confirmed'
")->fetchColumn();

$topServices = $pdo->query("
    SELECT s.name, COUNT(a.id) AS total, SUM(s.price) AS revenue
    FROM appointments a JOIN services s ON a.service_id = s.id
    WHERE a.status = 'done' GROUP BY s.id, s.name ORDER BY total DESC LIMIT 5
")->fetchAll();

$monthlyData = $pdo->query("
    SELECT DATE_FORMAT(appt_date, '%b %Y') AS month, COUNT(*) AS total,
           SUM(CASE WHEN status='done' THEN 1 ELSE 0 END) AS completed
    FROM appointments WHERE appt_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(appt_date, '%Y-%m') ORDER BY MIN(appt_date) ASC
")->fetchAll();

$recent = $pdo->query("
    SELECT a.*, u.name AS client, p.name AS pet_name, s.name AS service_name
    FROM appointments a JOIN users u ON a.user_id=u.id
    JOIN pets p ON a.pet_id=p.id JOIN services s ON a.service_id=s.id
    ORDER BY a.created_at DESC LIMIT 8
")->fetchAll();

$statusMap = [
    'pending'   => 'badge-pending',
    'confirmed' => 'badge-confirmed',
    'done'      => 'badge-done',
    'cancelled' => 'badge-cancelled',
    'no_show'   => 'badge-noshow',
];

$pageTitle = 'Admin Dashboard';
$rootPath  = '../../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include '../../includes/head.php'; ?>

</head>
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

      <!-- Greeting -->
      <div class="mb-4 dashboard-greeting">
        <h3 style="font-family:var(--font-display);font-size:1.7rem;font-weight:600;">Good day, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>.</h3>
        <p style="color:var(--text-muted);font-size:0.9rem;margin:0;">Overview of PetMalu operations — <?= date('F j, Y') ?></p>
      </div>

      <!-- Pending Deposits Alert -->
      <?php if ($pendingDeposits > 0): ?>
      <div class="deposit-alert" style="background:#fff8e1;border:1px solid #ffe082;border-radius:var(--radius);padding:1rem 1.2rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:0.8rem;">
        <div style="display:flex;align-items:center;gap:0.8rem;">
          <svg fill="none" stroke="#8B6914" stroke-width="1.5" viewBox="0 0 24 24" width="18" height="18" style="flex-shrink:0;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span style="color:#7c5c3e;font-size:0.88rem;font-weight:600;"><?= $pendingDeposits ?> deposit<?= $pendingDeposits > 1 ? 's' : '' ?> waiting for verification</span>
        </div>
        <a href="appointments.php?status=confirmed" class="btn-primary-site btn-sm-site" style="white-space:nowrap;">Review Now</a>
      </div>
      <?php endif; ?>

      <!-- Revenue Cards -->
      <div class="row g-3 mb-3 revenue-cards">
        <div class="col-md-4 col-12">
          <div class="stat-card" style="border-left:4px solid #27ae60;">
            <div class="stat-icon" style="background:#e8f8f0;color:#27ae60;">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <div class="stat-num" style="color:#27ae60;font-size:1.4rem;">₱<?= number_format($totalRevenue, 2) ?></div>
              <div class="stat-label">Total Revenue</div>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-12">
          <div class="stat-card" style="border-left:4px solid #2980b9;">
            <div class="stat-icon" style="background:#ebf5fb;color:#2980b9;">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
              </svg>
            </div>
            <div>
              <div class="stat-num" style="color:#2980b9;font-size:1.4rem;">₱<?= number_format($monthRevenue, 2) ?></div>
              <div class="stat-label">This Month's Revenue</div>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-12">
          <div class="stat-card" style="border-left:4px solid #7c5c3e;">
            <div class="stat-icon" style="background:#f5efe8;color:#7c5c3e;">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <div class="stat-num" style="color:#7c5c3e;font-size:1.4rem;">₱<?= number_format($todayRevenue, 2) ?></div>
              <div class="stat-label">Today's Revenue</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Operations Cards -->
      <div class="row g-3 mb-4 ops-cards">
        <?php
        $cards = [
          ['num'=>$totalUsers,    'label'=>'Total Customers',     'color'=>'#7c5c3e','bg'=>'#f5efe8','icon'=>'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
          ['num'=>$totalPets,     'label'=>'Registered Pets',     'color'=>'#27ae60','bg'=>'#e8f8f0','icon'=>'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z'],
          ['num'=>$todayAppts,    'label'=>"Today's Appts",       'color'=>'#2980b9','bg'=>'#ebf5fb','icon'=>'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
          ['num'=>$pendingAppts,  'label'=>'Pending Approval',    'color'=>'#e6a817','bg'=>'#fff8e1','icon'=>'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'],
          ['num'=>$totalAppts,    'label'=>'Total Appointments',  'color'=>'#7c5c3e','bg'=>'#f5efe8','icon'=>'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
          ['num'=>$suspendedUsers,'label'=>'Suspended Accounts',  'color'=>'#c0392b','bg'=>'#fdecea','icon'=>'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
        ];
        foreach ($cards as $c): ?>
        <div class="col-md-2 col-6">
          <div class="stat-card" style="border-left:3px solid <?= $c['color'] ?>;">
            <div class="stat-icon" style="background:<?= $c['bg'] ?>;color:<?= $c['color'] ?>;">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" d="<?= $c['icon'] ?>"/>
              </svg>
            </div>
            <div>
              <div class="stat-num" style="font-size:1.3rem;"><?= $c['num'] ?></div>
              <div class="stat-label" style="font-size:0.72rem;"><?= $c['label'] ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Chart + Top Services -->
      <div class="row g-3 mb-4 chart-row">
        <div class="col-md-8 col-12">
          <div class="card-site">
            <div class="card-header-site"><h5>Monthly Bookings (Last 6 Months)</h5></div>
            <div class="card-body-site" style="padding:1.5rem;">
              <canvas id="bookingsChart" height="120"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-12">
          <div class="card-site">
            <div class="card-header-site"><h5>Top Services</h5></div>
            <div class="card-body-site" style="padding:1rem;">
              <?php if (empty($topServices)): ?>
              <p style="color:var(--text-muted);font-size:0.85rem;text-align:center;padding:1rem 0;">No data yet.</p>
              <?php else: ?>
              <?php $maxTotal = max(array_column($topServices, 'total')); ?>
              <?php foreach ($topServices as $svc): ?>
              <div style="margin-bottom:1rem;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span style="font-size:0.83rem;font-weight:500;color:var(--text);"><?= htmlspecialchars($svc['name']) ?></span>
                  <span style="font-size:0.78rem;color:var(--text-muted);"><?= $svc['total'] ?> bookings</span>
                </div>
                <div style="background:var(--gray-light);border-radius:10px;height:6px;">
                  <div style="background:var(--brown);border-radius:10px;height:6px;width:<?= round(($svc['total']/$maxTotal)*100) ?>%;"></div>
                </div>
                <div style="font-size:0.75rem;color:#27ae60;margin-top:2px;">₱<?= number_format($svc['revenue'],2) ?> earned</div>
              </div>
              <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Appointments -->
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
        <div style="overflow-x:auto;">
          <table class="table-site" style="min-width:500px;">
            <thead>
              <tr><th>Client</th><th>Pet</th><th>Service</th><th>Date</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($recent as $a): ?>
            <tr>
              <td><strong><?= htmlspecialchars($a['client']) ?></strong></td>
              <td><?= htmlspecialchars($a['pet_name']) ?></td>
              <td><?= htmlspecialchars($a['service_name']) ?></td>
              <td style="white-space:nowrap;"><?= date('M j, Y', strtotime($a['appt_date'])) ?></td>
              <td><span class="badge-site <?= $statusMap[$a['status']]??'' ?>"><?= ucfirst(str_replace('_',' ',$a['status'])) ?></span></td>
              <td><a href="appointments.php" class="btn-ghost" style="font-size:0.72rem;padding:0.3rem 0.8rem;white-space:nowrap;">Manage</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode(array_column($monthlyData, 'month')) ?>;
const totals = <?= json_encode(array_column($monthlyData, 'total')) ?>;
const done   = <?= json_encode(array_column($monthlyData, 'completed')) ?>;

new Chart(document.getElementById('bookingsChart'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      { label: 'Total Bookings', data: totals, backgroundColor: 'rgba(124,92,62,0.15)', borderColor: 'rgba(124,92,62,0.8)', borderWidth: 2, borderRadius: 6 },
      { label: 'Completed',      data: done,   backgroundColor: 'rgba(39,174,96,0.2)',  borderColor: 'rgba(39,174,96,0.8)',  borderWidth: 2, borderRadius: 6 }
    ]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
  }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>