<?php
// views/admin/appointments.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/mailer.php';
requireAdmin();

$pdo = getPDO();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)$_POST['appt_id'];
    $action = $_POST['action'] ?? '';

    // Get appointment details
    $apptStmt = $pdo->prepare("
        SELECT a.*, u.name AS client, u.email AS client_email, 
               u.id AS client_id, u.no_show_count,
               p.name AS pet_name, s.name AS service_name
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        JOIN pets p ON a.pet_id = p.id
        JOIN services s ON a.service_id = s.id
        WHERE a.id = ?
    ");
    $apptStmt->execute([$id]);
    $appt = $apptStmt->fetch();

    if ($appt) {
        $formattedDate = date('F j, Y', strtotime($appt['appt_date']));
        $formattedTime = date('g:i A', strtotime($appt['appt_time']));

        if ($action === 'confirmed') {
            $pdo->prepare('UPDATE appointments SET status=? WHERE id=?')
                ->execute(['confirmed', $id]);

            $subject = 'PawCare – Booking Confirmed! 🎉';
            $body = "
                <div style='font-family:sans-serif;max-width:520px;margin:auto;'>
                    <h2 style='color:#27ae60;'>Booking Confirmed! 🐾</h2>
                    <p>Hi <strong>{$appt['client']}</strong>,</p>
                    <p>Great news! Your grooming appointment has been confirmed.</p>
                    <div style='background:#f9f5f0;border-radius:8px;padding:20px;margin:20px 0;'>
                        <h3 style='color:#7c5c3e;margin-top:0;'>Appointment Details</h3>
                        <table style='width:100%;font-size:0.9rem;'>
                            <tr><td style='color:#888;padding:4px 0;'>Pet:</td><td><strong>{$appt['pet_name']}</strong></td></tr>
                            <tr><td style='color:#888;padding:4px 0;'>Service:</td><td><strong>{$appt['service_name']}</strong></td></tr>
                            <tr><td style='color:#888;padding:4px 0;'>Date:</td><td><strong>{$formattedDate}</strong></td></tr>
                            <tr><td style='color:#888;padding:4px 0;'>Time:</td><td><strong>{$formattedTime}</strong></td></tr>
                            <tr><td style='color:#888;padding:4px 0;'>Status:</td><td><strong style='color:#27ae60;'>Confirmed ✓</strong></td></tr>
                        </table>
                    </div>
                    <p style='color:#888;font-size:0.85rem;'>Please arrive <strong>10 minutes early</strong>. For cancellations, notify us at least <strong>24 hours</strong> before your appointment.</p>
                    <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'/>
                    <p style='color:#aaa;font-size:0.75rem;text-align:center;'>PawCare Grooming Studio</p>
                </div>
            ";
            sendMail($appt['client_email'], $appt['client'], $subject, $body);
            $success = "Appointment #{$id} confirmed. Email sent to {$appt['client']}.";

        } elseif ($action === 'verify_deposit') {
            // ✅ ACTION: VERIFY DEPOSIT
            $pdo->prepare("UPDATE appointments SET deposit_status='verified' WHERE id=?")
                ->execute([$id]);

            $subject = 'PawCare – Deposit Verified! ✓';
            $body = "
                <div style='font-family:sans-serif;max-width:520px;margin:auto;'>
                    <h2 style='color:#27ae60;'>Deposit Verified! 🐾</h2>
                    <p>Hi <strong>{$appt['client']}</strong>,</p>
                    <p>Your GCash deposit has been verified. Your booking is now fully secured!</p>
                    <div style='background:#f9f5f0;border-radius:8px;padding:20px;margin:20px 0;'>
                        <table style='width:100%;font-size:0.9rem;'>
                            <tr><td style='color:#888;padding:4px 0;'>Pet:</td><td><strong>{$appt['pet_name']}</strong></td></tr>
                            <tr><td style='color:#888;padding:4px 0;'>Service:</td><td><strong>{$appt['service_name']}</strong></td></tr>
                            <tr><td style='color:#888;padding:4px 0;'>Date:</td><td><strong>{$formattedDate}</strong></td></tr>
                            <tr><td style='color:#888;padding:4px 0;'>Time:</td><td><strong>{$formattedTime}</strong></td></tr>
                        </table>
                    </div>
                    <p style='color:#888;font-size:0.85rem;'>See you on your appointment day! Please arrive 10 minutes early. 🐾</p>
                    <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'/>
                    <p style='color:#aaa;font-size:0.75rem;text-align:center;'>PawCare Grooming Studio</p>
                </div>
            ";
            sendMail($appt['client_email'], $appt['client'], $subject, $body);
            $success = "Deposit verified for Appointment #{$id}. Email sent to {$appt['client']}.";

        } elseif ($action === 'cancelled') {
            $pdo->prepare('UPDATE appointments SET status=?, cancelled_at=NOW() WHERE id=?')
                ->execute(['cancelled', $id]);

            $subject = 'PawCare – Appointment Rejected';
            $body = "
                <div style='font-family:sans-serif;max-width:520px;margin:auto;'>
                    <h2 style='color:#c0392b;'>Appointment Not Approved 🐾</h2>
                    <p>Hi <strong>{$appt['client']}</strong>,</p>
                    <p>We're sorry, but we were unable to approve your grooming appointment.</p>
                    <div style='background:#f9f5f0;border-radius:8px;padding:20px;margin:20px 0;'>
                        <h3 style='color:#7c5c3e;margin-top:0;'>Appointment Details</h3>
                        <table style='width:100%;font-size:0.9rem;'>
                            <tr><td style='color:#888;padding:4px 0;'>Pet:</td><td><strong>{$appt['pet_name']}</strong></td></tr>
                            <tr><td style='color:#888;padding:4px 0;'>Service:</td><td><strong>{$appt['service_name']}</strong></td></tr>
                            <tr><td style='color:#888;padding:4px 0;'>Date:</td><td><strong>{$formattedDate}</strong></td></tr>
                            <tr><td style='color:#888;padding:4px 0;'>Time:</td><td><strong>{$formattedTime}</strong></td></tr>
                        </table>
                    </div>
                    <p style='color:#888;font-size:0.85rem;'>Please try booking another available slot. We apologize for any inconvenience.</p>
                    <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'/>
                    <p style='color:#aaa;font-size:0.75rem;text-align:center;'>PawCare Grooming Studio</p>
                </div>
            ";
            sendMail($appt['client_email'], $appt['client'], $subject, $body);
            $success = "Appointment #{$id} rejected. Email sent to {$appt['client']}.";

        } elseif ($action === 'done') {
            $pdo->prepare('UPDATE appointments SET status=? WHERE id=?')
                ->execute(['done', $id]);
            $success = "Appointment #{$id} marked as done.";

        } elseif ($action === 'no_show') {
            $newCount = $appt['no_show_count'] + 1;
            $pdo->prepare('UPDATE appointments SET status=? WHERE id=?')
                ->execute(['no_show', $id]);
            $pdo->prepare('UPDATE users SET no_show_count=? WHERE id=?')
                ->execute([$newCount, $appt['client_id']]);

            if ($newCount >= 2) {
                $pdo->prepare('UPDATE users SET is_suspended=1 WHERE id=?')
                    ->execute([$appt['client_id']]);

                $subject = 'PawCare – Account Suspended';
                $body = "
                    <div style='font-family:sans-serif;max-width:520px;margin:auto;'>
                        <h2 style='color:#c0392b;'>Account Suspended 🚫</h2>
                        <p>Hi <strong>{$appt['client']}</strong>,</p>
                        <p>Due to <strong>2 consecutive no-shows</strong>, your PawCare account has been suspended.</p>
                        <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'/>
                        <p style='color:#aaa;font-size:0.75rem;text-align:center;'>PawCare Grooming Studio</p>
                    </div>
                ";
                sendMail($appt['client_email'], $appt['client'], $subject, $body);
                $success = "Appointment #{$id} marked as no-show. Account suspended.";
            } else {
                // 1st no-show warning email logic...
                $success = "Appointment #{$id} marked as no-show. Warning email sent.";
            }
        }
    }
}

$filter = $_GET['status'] ?? 'all';
$params = [];
$sql = "SELECT a.*, u.name AS client, u.email AS client_email, u.phone AS client_phone,
               u.no_show_count, u.is_suspended,
               p.name AS pet_name, p.breed, s.name AS service_name, s.price
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        JOIN pets p ON a.pet_id = p.id
        JOIN services s ON a.service_id = s.id";
if ($filter !== 'all') { $sql .= ' WHERE a.status=?'; $params[] = $filter; }
$sql .= ' ORDER BY a.appt_date ASC, a.appt_time ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appts = $stmt->fetchAll();

$tabs      = ['all','pending','confirmed','done','cancelled','no_show'];
$statusMap = [
    'pending'   => 'badge-pending',
    'confirmed' => 'badge-confirmed',
    'done'      => 'badge-done',
    'cancelled' => 'badge-cancelled',
    'no_show'   => 'badge-noshow',
];

$pageTitle = 'Appointments';
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
      <span class="topbar-title">Manage Appointments</span>
      <div class="user-meta">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=3B2F2F&color=C4A882&size=64" class="user-avatar"/>
        <span class="badge-site badge-admin">Admin</span>
      </div>
    </div>

    <div class="content-pad">
      <?php if ($success): ?>
      <div class="alert-site alert-success mb-3"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <div class="d-flex gap-2 mb-4 flex-wrap">
        <?php foreach ($tabs as $tab): ?>
        <a href="?status=<?= $tab ?>" style="padding:0.4rem 1rem;border-radius:var(--radius);font-size:0.78rem;font-weight:500;letter-spacing:0.8px;text-transform:uppercase;text-decoration:none;transition:var(--transition);
           <?= $filter===$tab ? 'background:var(--brown);color:#fff;border:1px solid var(--brown);' : 'background:var(--white);color:var(--text-muted);border:1px solid var(--gray-light);' ?>">
          <?= ucfirst(str_replace('_', ' ', $tab)) ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="card-site">
        <div class="card-header-site">
          <h5>Appointments</h5>
          <span style="font-size:0.8rem;color:var(--text-muted);"><?= count($appts) ?> record<?= count($appts)!==1?'s':'' ?></span>
        </div>
        <table class="table-site">
          <thead>
            <tr><th>#</th><th>Client</th><th>Pet</th><th>Service</th><th>Date &amp; Time</th><th>Price</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach ($appts as $i => $a): ?>
          <tr>
            <td style="color:var(--gray);"><?= $i+1 ?></td>
            <td>
              <strong><?= htmlspecialchars($a['client']) ?></strong>
              <?php if ($a['is_suspended']): ?>
              <br/><span style="font-size:0.72rem;color:#c0392b;font-weight:600;">⚠ Suspended</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($a['pet_name']) ?></td>
            <td><?= htmlspecialchars($a['service_name']) ?></td>
            <td><?= date('M j, Y', strtotime($a['appt_date'])) ?><br/><span style="font-size:0.78rem;color:var(--text-muted);"><?= date('g:i A', strtotime($a['appt_time'])) ?></span></td>
            <td>₱<?= number_format($a['price'],2) ?></td>
            <td>
              <span class="badge-site <?= $statusMap[$a['status']]??'' ?>">
                <?= ucfirst(str_replace('_', ' ', $a['status'])) ?>
              </span>
              <?php if ($a['deposit_status'] === 'verified'): ?>
                <br/><span style="font-size:0.72rem;color:#27ae60;">✓ Deposit Verified</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
              <?php if ($a['status']==='pending'): ?>
                <form method="POST">
                  <input type="hidden" name="appt_id" value="<?= $a['id'] ?>"/>
                  <input type="hidden" name="action" value="confirmed"/>
                  <button type="submit" class="btn-success-site">Confirm</button>
                </form>
                <form method="POST">
                  <input type="hidden" name="appt_id" value="<?= $a['id'] ?>"/>
                  <input type="hidden" name="action" value="cancelled"/>
                  <button type="submit" class="btn-danger-site">Reject</button>
                </form>
              <?php elseif ($a['status']==='confirmed'): ?>
                <form method="POST">
                  <input type="hidden" name="appt_id" value="<?= $a['id'] ?>"/>
                  <input type="hidden" name="action" value="done"/>
                  <button type="submit" class="btn-ghost" style="font-size:0.72rem;padding:0.35rem 0.8rem;">Mark Done</button>
                </form>
                <form method="POST">
                  <input type="hidden" name="appt_id" value="<?= $a['id'] ?>"/>
                  <input type="hidden" name="action" value="no_show"/>
                  <button type="submit" class="btn-danger-site" style="font-size:0.72rem;padding:0.35rem 0.8rem;">No-Show</button>
                </form>
                
                <?php if ($a['deposit_status'] === 'uploaded'): ?>
                <button type="button" class="btn-success-site" style="font-size:0.72rem;padding:0.35rem 0.8rem; background: #27ae60; color: #fff;"
                  onclick="openDepositModal('<?= $a['deposit_screenshot'] ?>', <?= $a['id'] ?>)">
                  Verify Deposit
                </button>
                <?php endif; ?>

              <?php else: ?>
                <span style="color:var(--gray);font-size:0.8rem;">—</span>
              <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div id="adminDepositModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:var(--radius);padding:2rem;max-width:520px;width:90%;box-shadow:0 10px 40px rgba(0,0,0,0.15);">
    <h5 style="color:var(--brown);margin-bottom:1rem;">Verify GCash Deposit</h5>
    <img id="depositScreenshot" src="" style="width:100%;border-radius:var(--radius);margin-bottom:1.2rem;border:1px solid var(--gray-light);"/>
    <form method="POST">
      <input type="hidden" name="action" value="verify_deposit"/>
      <input type="hidden" name="appt_id" id="adminDepositApptId"/>
      <div class="d-flex gap-2 justify-content-end">
        <button type="button" onclick="closeAdminDepositModal()" 
                style="padding:0.6rem 1.2rem;border:1px solid var(--gray-light);border-radius:var(--radius);background:#fff;cursor:pointer;font-size:0.85rem;">
          Close
        </button>
        <button type="submit" class="btn-success-site">✓ Verify Deposit</button>
      </div>
    </form>
  </div>
</div>

<script>
function openDepositModal(screenshot, id) {
  // Siguraduhin na tama yung path ng image base sa root folder mo
  document.getElementById('depositScreenshot').src = '../../' + screenshot;
  document.getElementById('adminDepositApptId').value = id;
  document.getElementById('adminDepositModal').style.display = 'flex';
}
function closeAdminDepositModal() {
  document.getElementById('adminDepositModal').style.display = 'none';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>