<?php
// views/admin/users.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/mailer.php';
requireAdmin();

$pdo = getPDO();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid    = (int)$_POST['user_id'];
    $action = $_POST['action'] ?? '';

    // Get user details
    $userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $userStmt->execute([$uid]);
    $user = $userStmt->fetch();

    if ($user) {
        if ($action === 'lift_suspension') {
            $pdo->prepare('UPDATE users SET is_suspended=0 WHERE id=?')
                ->execute([$uid]);

            $subject = 'PawCare – Account Reinstated ✓';
            $body = "
                <div style='font-family:sans-serif;max-width:520px;margin:auto;'>
                    <h2 style='color:#27ae60;'>Account Reinstated! 🐾</h2>
                    <p>Hi <strong>{$user['name']}</strong>,</p>
                    <p>Good news! Your PawCare account has been reinstated. You can now make new bookings again.</p>
                    <div style='background:#e8f8f0;border:1px solid #a9dfbf;border-radius:8px;padding:16px;margin:20px 0;font-size:0.88rem;color:#1e8449;'>
                        ✓ Please note that you still have <strong>{$user['no_show_count']} no-show(s)</strong> on record. 
                        Any further no-shows may result in permanent suspension.
                    </div>
                    <p style='color:#888;font-size:0.85rem;'>Please remember to cancel at least <strong>24 hours in advance</strong> if you cannot attend your appointment.</p>
                    <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'/>
                    <p style='color:#aaa;font-size:0.75rem;text-align:center;'>PawCare Grooming Studio</p>
                </div>
            ";
            sendMail($user['email'], $user['name'], $subject, $body);
            $success = "{$user['name']}'s account has been reinstated. Email notification sent.";

        } elseif ($action === 'reset_noshow') {
            $pdo->prepare('UPDATE users SET no_show_count=0 WHERE id=?')
                ->execute([$uid]);

            $subject = 'PawCare – No-Show Record Cleared';
            $body = "
                <div style='font-family:sans-serif;max-width:520px;margin:auto;'>
                    <h2 style='color:#7c5c3e;'>No-Show Record Cleared 🐾</h2>
                    <p>Hi <strong>{$user['name']}</strong>,</p>
                    <p>Your no-show record has been cleared by our admin team. You're starting fresh!</p>
                    <p style='color:#888;font-size:0.85rem;'>Please remember to cancel at least <strong>24 hours in advance</strong> if you cannot attend your appointment.</p>
                    <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'/>
                    <p style='color:#aaa;font-size:0.75rem;text-align:center;'>PawCare Grooming Studio</p>
                </div>
            ";
            sendMail($user['email'], $user['name'], $subject, $body);
            $success = "{$user['name']}'s no-show record has been reset.";

        } elseif ($action === 'suspend') {
            $pdo->prepare('UPDATE users SET is_suspended=1 WHERE id=?')
                ->execute([$uid]);

            $subject = 'PawCare – Account Suspended';
            $body = "
                <div style='font-family:sans-serif;max-width:520px;margin:auto;'>
                    <h2 style='color:#c0392b;'>Account Suspended 🚫</h2>
                    <p>Hi <strong>{$user['name']}</strong>,</p>
                    <p>Your PawCare account has been suspended by our admin team.</p>
                    <p style='color:#888;font-size:0.85rem;'>To appeal your suspension, please contact us directly.</p>
                    <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'/>
                    <p style='color:#aaa;font-size:0.75rem;text-align:center;'>PawCare Grooming Studio</p>
                </div>
            ";
            sendMail($user['email'], $user['name'], $subject, $body);
            $success = "{$user['name']}'s account has been suspended.";
        }
    }
}

$filter = $_GET['filter'] ?? 'all';
$sql = "
    SELECT u.*,
           COUNT(DISTINCT a.id) AS total_appts,
           COUNT(DISTINCT p.id) AS total_pets
    FROM users u
    LEFT JOIN appointments a ON u.id = a.user_id
    LEFT JOIN pets p ON u.id = p.user_id
    WHERE u.role = 'user'
";
if ($filter === 'suspended') $sql .= " AND u.is_suspended = 1";
if ($filter === 'noshow')    $sql .= " AND u.no_show_count > 0";
if ($filter === 'unverified') $sql .= " AND u.is_verified = 0";
$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$users = $pdo->query($sql)->fetchAll();

$totalAll        = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalSuspended  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user' AND is_suspended=1")->fetchColumn();
$totalNoShow     = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user' AND no_show_count > 0")->fetchColumn();
$totalUnverified = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user' AND is_verified=0")->fetchColumn();

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

      <?php if ($success): ?>
      <div class="alert-site alert-success mb-3"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
      <div class="alert-site alert-error mb-3"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <!-- ✅ Filter tabs -->
      <div class="d-flex gap-2 mb-4 flex-wrap">
        <?php
        $tabs = [
            'all'        => "All Users ({$totalAll})",
            'suspended'  => "Suspended ({$totalSuspended})",
            'noshow'     => "Has No-Show ({$totalNoShow})",
            'unverified' => "Unverified ({$totalUnverified})",
        ];
        foreach ($tabs as $key => $label): ?>
        <a href="?filter=<?= $key ?>"
           style="padding:0.4rem 1rem;border-radius:var(--radius);font-size:0.78rem;font-weight:500;letter-spacing:0.8px;text-transform:uppercase;text-decoration:none;transition:var(--transition);
           <?= $filter===$key ? 'background:var(--brown);color:#fff;border:1px solid var(--brown);' : 'background:var(--white);color:var(--text-muted);border:1px solid var(--gray-light);' ?>">
          <?= $label ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="card-site">
        <div class="card-header-site">
          <h5>Users</h5>
          <span style="font-size:0.8rem;color:var(--text-muted);"><?= count($users) ?> record<?= count($users) !== 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($users)): ?>
        <div class="card-body-site text-center py-4">
          <p style="color:var(--text-muted);">No users found.</p>
        </div>
        <?php else: ?>
        <table class="table-site">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Pets</th>
              <th>Bookings</th>
              <th>No-Shows</th>
              <th>Status</th>
              <th>Joined</th>
              <th>Actions</th>
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
                <div>
                  <strong><?= htmlspecialchars($u['name']) ?></strong>
                  <?php if (!$u['is_verified']): ?>
                  <br/><span style="font-size:0.7rem;color:#e6a817;">⚠ Unverified</span>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
            <td><?= $u['total_pets'] ?></td>
            <td><?= $u['total_appts'] ?></td>
            <td>
              <?php if ($u['no_show_count'] > 0): ?>
              <span style="color:#e6a817;font-weight:600;"><?= $u['no_show_count'] ?>/2 ⚠️</span>
              <?php else: ?>
              <span style="color:#27ae60;">0/2 ✓</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($u['is_suspended']): ?>
              <span class="badge-site badge-noshow">Suspended</span>
              <?php else: ?>
              <span class="badge-site badge-confirmed">Active</span>
              <?php endif; ?>
            </td>
            <td style="font-size:0.82rem;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <?php if ($u['is_suspended']): ?>
                <form method="POST" onsubmit="return confirm('Lift suspension for <?= htmlspecialchars($u['name']) ?>?');">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>"/>
                  <input type="hidden" name="action" value="lift_suspension"/>
                  <button type="submit" class="btn-success-site" style="font-size:0.72rem;padding:0.35rem 0.8rem;">Lift Ban</button>
                </form>
                <?php else: ?>
                <form method="POST" onsubmit="return confirm('Suspend <?= htmlspecialchars($u['name']) ?>?');">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>"/>
                  <input type="hidden" name="action" value="suspend"/>
                  <button type="submit" class="btn-danger-site" style="font-size:0.72rem;padding:0.35rem 0.8rem;">Suspend</button>
                </form>
                <?php endif; ?>

                <?php if ($u['no_show_count'] > 0): ?>
                <form method="POST" onsubmit="return confirm('Reset no-show count for <?= htmlspecialchars($u['name']) ?>?');">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>"/>
                  <input type="hidden" name="action" value="reset_noshow"/>
                  <button type="submit" class="btn-ghost" style="font-size:0.72rem;padding:0.35rem 0.8rem;">Reset No-Show</button>
                </form>
                <?php endif; ?>
              </div>
            </td>
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