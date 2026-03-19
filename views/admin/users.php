<?php
// views/admin/users.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
requireAdmin();

$pdo = getPDO();

$users = $pdo->query("
    SELECT u.*, COUNT(a.id) AS total_appts
    FROM users u
    LEFT JOIN appointments a ON u.id = a.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll();

$pageTitle = 'Users';
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
      <span class="topbar-title">Manage Users</span>
      <div class="user-meta">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=3B2F2F&color=C4A882&size=64" class="user-avatar"/>
        <span class="badge-site badge-admin">Admin</span>
      </div>
    </div>
    <div class="content-pad">
      <div class="card-site">
        <div class="card-header-site">
          <h5>All Registered Users</h5>
          <span style="font-size:0.8rem;color:var(--text-muted);"><?= count($users) ?> total</span>
        </div>
        <table class="table-site">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Bookings</th>
              <th>Joined</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($users as $i => $u): ?>
          <tr>
            <td style="color:var(--gray);"><?= $i + 1 ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['name']) ?>&background=EDE6DC&color=3B2F2F&size=64"
                     style="width:32px;height:32px;border-radius:50%;border:1px solid var(--gray-light);" alt="avatar"/>
                <strong><?= htmlspecialchars($u['name']) ?></strong>
              </div>
            </td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
            <td>
              <span class="badge-site <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                <?= ucfirst($u['role']) ?>
              </span>
            </td>
            <td><?= $u['total_appts'] ?></td>
            <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
