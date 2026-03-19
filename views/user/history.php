<?php
// views/user/history.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/mailer.php';
requireUser();

$pdo = getPDO();
$uid = $_SESSION['user_id'];

$cancelError = $cancelSuccess = $depositError = $depositSuccess = '';

// ✅ Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $id     = (int)$_POST['appt_id'];
    $reason = clean($_POST['reason'] ?? '');

    if (!$reason) {
        $cancelError = 'Please provide a cancellation reason.';
    } else {
        $apptStmt = $pdo->prepare("
            SELECT a.*, s.name AS service_name, p.name AS pet_name 
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            JOIN pets p ON a.pet_id = p.id
            WHERE a.id = ? AND a.user_id = ? AND a.status IN ('pending','confirmed')
        ");
        $apptStmt->execute([$id, $uid]);
        $appt = $apptStmt->fetch();

        if (!$appt) {
            $cancelError = 'Appointment not found or cannot be cancelled.';
        } else {
            $apptDateTime = strtotime($appt['appt_date'] . ' ' . $appt['appt_time']);
            $hoursLeft    = ($apptDateTime - time()) / 3600;

            if ($hoursLeft < 24) {
                $cancelError = 'Cancellations must be made at least 24 hours before the appointment.';
            } else {
                $pdo->prepare("
                    UPDATE appointments 
                    SET status='cancelled', cancellation_reason=?, cancelled_at=NOW() 
                    WHERE id=? AND user_id=?
                ")->execute([$reason, $id, $uid]);

                $userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
                $userStmt->execute([$uid]);
                $userData = $userStmt->fetch();

                $formattedDate = date('F j, Y', strtotime($appt['appt_date']));
                $formattedTime = date('g:i A', strtotime($appt['appt_time']));

                $subject = 'PawCare – Appointment Cancelled';
                $body    = "
                    <div style='font-family:sans-serif;max-width:520px;margin:auto;'>
                        <h2 style='color:#c0392b;'>Appointment Cancelled 🐾</h2>
                        <p>Hi <strong>{$userData['name']}</strong>,</p>
                        <p>Your appointment has been successfully cancelled.</p>
                        <div style='background:#f9f5f0;border-radius:8px;padding:20px;margin:20px 0;'>
                            <table style='width:100%;font-size:0.9rem;'>
                                <tr><td style='color:#888;padding:4px 0;'>Pet:</td><td><strong>{$appt['pet_name']}</strong></td></tr>
                                <tr><td style='color:#888;padding:4px 0;'>Service:</td><td><strong>{$appt['service_name']}</strong></td></tr>
                                <tr><td style='color:#888;padding:4px 0;'>Date:</td><td><strong>{$formattedDate}</strong></td></tr>
                                <tr><td style='color:#888;padding:4px 0;'>Time:</td><td><strong>{$formattedTime}</strong></td></tr>
                                <tr><td style='color:#888;padding:4px 0;'>Reason:</td><td><strong>{$reason}</strong></td></tr>
                            </table>
                        </div>
                        <p style='color:#888;font-size:0.85rem;'>If you'd like to rebook, you can do so anytime through your PawCare account.</p>
                        <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'/>
                        <p style='color:#aaa;font-size:0.75rem;text-align:center;'>PawCare Grooming Studio</p>
                    </div>
                ";
                sendMail($userData['email'], $userData['name'], $subject, $body);
                $cancelSuccess = 'Appointment cancelled successfully.';
            }
        }
    }
}

// ✅ Handle deposit upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'deposit') {
    $id     = (int)$_POST['appt_id'];
    $amount = (float)($_POST['deposit_amount'] ?? 0);

    $apptStmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ? AND status = 'confirmed' AND deposit_status = 'unpaid'");
    $apptStmt->execute([$id, $uid]);
    $appt = $apptStmt->fetch();

    if (!$appt) {
        $depositError = 'Invalid appointment or deposit already uploaded.';
    } elseif ($amount <= 0) {
        $depositError = 'Please enter a valid deposit amount.';
    } elseif (empty($_FILES['screenshot']['name'])) {
        $depositError = 'Please upload your GCash screenshot.';
    } else {
        $file    = $_FILES['screenshot'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowed)) {
            $depositError = 'Invalid file type. Use JPG, PNG or WEBP.';
        } elseif ($file['size'] > $maxSize) {
            $depositError = 'File too large. Max 5MB.';
        } else {
            $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('deposit_', true) . '.' . $ext;
            $uploadDir = __DIR__ . '/../../assets/uploads/deposits/';
            move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
            $path = 'assets/uploads/deposits/' . $filename;

            $pdo->prepare("
                UPDATE appointments 
                SET deposit_amount=?, deposit_screenshot=?, deposit_status='uploaded', deposit_uploaded_at=NOW()
                WHERE id=? AND user_id=?
            ")->execute([$amount, $path, $id, $uid]);

            $depositSuccess = 'Deposit screenshot uploaded! Waiting for admin verification.';
        }
    }
}

$filter = $_GET['status'] ?? 'all';
$params = [$uid];
$sql = "SELECT a.*, s.name AS service_name, s.price, p.name AS pet_name
        FROM appointments a
        JOIN services s ON a.service_id = s.id
        JOIN pets p ON a.pet_id = p.id
        WHERE a.user_id = ?";
if ($filter !== 'all') { $sql .= ' AND a.status = ?'; $params[] = $filter; }
$sql .= ' ORDER BY a.appt_date DESC, a.appt_time DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appts = $stmt->fetchAll();

$tabs      = ['all','pending','confirmed','done','cancelled'];
$statusMap = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed','done'=>'badge-done','cancelled'=>'badge-cancelled'];

$pageTitle = 'Appointment History';
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
      <span class="topbar-title">Appointment History</span>
      <div class="d-flex align-items-center gap-3">
        <a href="book.php" class="btn-primary-site btn-sm-site">+ New Booking</a>
        <div class="user-meta">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=EDE6DC&color=3B2F2F&size=64" class="user-avatar"/>
          <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
        </div>
      </div>
    </div>

    <div class="content-pad">

      <?php if ($cancelError): ?>
      <div class="alert-site alert-error mb-3"><?= htmlspecialchars($cancelError) ?></div>
      <?php endif; ?>
      <?php if ($cancelSuccess): ?>
      <div class="alert-site alert-success mb-3"><?= htmlspecialchars($cancelSuccess) ?></div>
      <?php endif; ?>
      <?php if ($depositError): ?>
      <div class="alert-site alert-error mb-3"><?= htmlspecialchars($depositError) ?></div>
      <?php endif; ?>
      <?php if ($depositSuccess): ?>
      <div class="alert-site alert-success mb-3"><?= htmlspecialchars($depositSuccess) ?></div>
      <?php endif; ?>

      <!-- Filter tabs -->
      <div class="d-flex gap-2 mb-4 flex-wrap">
        <?php foreach ($tabs as $tab): ?>
        <a href="?status=<?= $tab ?>"
           style="padding:0.4rem 1rem;border-radius:var(--radius);font-size:0.78rem;font-weight:500;letter-spacing:0.8px;text-transform:uppercase;text-decoration:none;transition:var(--transition);
           <?= $filter===$tab ? 'background:var(--brown);color:#fff;border:1px solid var(--brown);' : 'background:var(--white);color:var(--text-muted);border:1px solid var(--gray-light);' ?>">
          <?= ucfirst($tab) ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="card-site">
        <div class="card-header-site">
          <h5>All Appointments</h5>
          <span style="font-size:0.8rem;color:var(--text-muted);"><?= count($appts) ?> record<?= count($appts)!==1?'s':'' ?></span>
        </div>
        <?php if (empty($appts)): ?>
        <div class="card-body-site text-center py-5">
          <p style="color:var(--text-muted);">No appointments found.</p>
          <a href="book.php" class="btn-primary-site btn-sm-site mt-2">Book Now</a>
        </div>
        <?php else: ?>
        <table class="table-site">
          <thead>
            <tr><th>#</th><th>Pet</th><th>Service</th><th>Date &amp; Time</th><th>Price</th><th>Status</th><th>Deposit</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach ($appts as $i => $a): ?>
          <tr>
            <td style="color:var(--gray);"><?= $i+1 ?></td>
            <td><strong><?= htmlspecialchars($a['pet_name']) ?></strong></td>
            <td><?= htmlspecialchars($a['service_name']) ?></td>
            <td>
              <?= date('M j, Y', strtotime($a['appt_date'])) ?><br/>
              <span style="font-size:0.78rem;color:var(--text-muted);"><?= date('g:i A', strtotime($a['appt_time'])) ?></span>
            </td>
            <td>₱<?= number_format($a['price'],2) ?></td>
            <td>
              <span class="badge-site <?= $statusMap[$a['status']]??'' ?>"><?= ucfirst($a['status']) ?></span>
              <?php if ($a['status']==='cancelled' && $a['cancellation_reason']): ?>
              <br/><span style="font-size:0.75rem;color:var(--text-muted);">Reason: <?= htmlspecialchars($a['cancellation_reason']) ?></span>
              <?php endif; ?>
            </td>

            <!-- ✅ Deposit Column -->
            <td>
              <?php if ($a['status'] === 'confirmed'): ?>
                <?php if ($a['deposit_status'] === 'unpaid'): ?>
                  <span style="font-size:0.78rem;color:#e6a817;font-weight:600;">⚠ Unpaid</span>
                <?php elseif ($a['deposit_status'] === 'uploaded'): ?>
                  <span style="font-size:0.78rem;color:#2980b9;font-weight:600;">⏳ Verifying</span>
                <?php elseif ($a['deposit_status'] === 'verified'): ?>
                  <span style="font-size:0.78rem;color:#27ae60;font-weight:600;">✓ Verified</span>
                  <?php if ($a['deposit_amount']): ?>
                  <br/><span style="font-size:0.72rem;color:var(--text-muted);">₱<?= number_format($a['deposit_amount'],2) ?></span>
                  <?php endif; ?>
                <?php endif; ?>
              <?php else: ?>
                <span style="font-size:0.78rem;color:var(--text-muted);">—</span>
              <?php endif; ?>
            </td>

            <td>
              <?php
                $apptDateTime = strtotime($a['appt_date'] . ' ' . $a['appt_time']);
                $hoursLeft    = ($apptDateTime - time()) / 3600;
                $canCancel    = in_array($a['status'], ['pending','confirmed']) && $hoursLeft >= 24;
              ?>

              <div class="d-flex flex-column gap-1">
                <?php if ($a['status'] === 'confirmed' && $a['deposit_status'] === 'unpaid'): ?>
                <button type="button" class="btn-primary-site btn-sm-site"
                  onclick="openDepositModal(<?= $a['id'] ?>, '<?= htmlspecialchars($a['pet_name']) ?>', '<?= htmlspecialchars($a['service_name']) ?>')">
                  Pay Deposit
                </button>
                <?php endif; ?>

                <?php if ($canCancel): ?>
                <button type="button" class="btn-danger-site"
                  onclick="openCancelModal(<?= $a['id'] ?>, '<?= htmlspecialchars($a['pet_name']) ?>', '<?= date('M j, Y', strtotime($a['appt_date'])) ?>')">
                  Cancel
                </button>
                <?php elseif (in_array($a['status'], ['pending','confirmed']) && $hoursLeft < 24): ?>
                <span style="font-size:0.75rem;color:var(--text-muted);">Cannot cancel<br/>within 24hrs</span>
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

<!-- Cancel Modal -->
<div id="cancelModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:var(--radius);padding:2rem;max-width:420px;width:90%;box-shadow:0 10px 40px rgba(0,0,0,0.15);">
    <h5 style="color:var(--brown);margin-bottom:0.5rem;">Cancel Appointment</h5>
    <p id="cancelModalDesc" style="font-size:0.88rem;color:var(--text-muted);margin-bottom:1.2rem;"></p>
    <form method="POST">
      <input type="hidden" name="action" value="cancel"/>
      <input type="hidden" name="appt_id" id="cancelApptId"/>
      <div class="mb-3">
        <label class="form-label-site">Reason for cancellation *</label>
        <textarea name="reason" class="form-control-site" rows="3"
                  placeholder="Please tell us why you're cancelling..."
                  style="resize:vertical;" required></textarea>
      </div>
      <div class="d-flex gap-2 justify-content-end">
        <button type="button" onclick="closeCancelModal()"
                style="padding:0.6rem 1.2rem;border:1px solid var(--gray-light);border-radius:var(--radius);background:#fff;cursor:pointer;font-size:0.85rem;">
          Keep Appointment
        </button>
        <button type="submit" class="btn-danger-site">Confirm Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- ✅ Deposit Modal -->
<div id="depositModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:var(--radius);padding:2rem;max-width:460px;width:90%;box-shadow:0 10px 40px rgba(0,0,0,0.15);">
    <h5 style="color:var(--brown);margin-bottom:0.3rem;">Upload GCash Deposit</h5>
    <p id="depositModalDesc" style="font-size:0.88rem;color:var(--text-muted);margin-bottom:1.2rem;"></p>

    <div style="background:#f0f8ff;border:1px solid #bee3f8;border-radius:var(--radius);padding:0.8rem 1rem;margin-bottom:1.2rem;font-size:0.83rem;color:#2c5282;">
      📱 Send your deposit to:<br/>
      <strong>GCash: 0917-123-4567 (PawCare Grooming)</strong><br/>
      Then upload the screenshot below.
    </div>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="deposit"/>
      <input type="hidden" name="appt_id" id="depositApptId"/>
      <div class="mb-3">
        <label class="form-label-site">Amount Sent (₱) *</label>
        <input type="number" name="deposit_amount" class="form-control-site"
               placeholder="e.g., 200" step="0.01" min="1" required/>
      </div>
      <div class="mb-4">
        <label class="form-label-site">GCash Screenshot *</label>
        <input type="file" name="screenshot" class="form-control-site"
               accept="image/jpeg,image/png,image/webp" required/>
        <p style="font-size:0.75rem;color:var(--text-muted);margin-top:0.3rem;">JPG, PNG or WEBP. Max 5MB.</p>
      </div>
      <div class="d-flex gap-2 justify-content-end">
        <button type="button" onclick="closeDepositModal()"
                style="padding:0.6rem 1.2rem;border:1px solid var(--gray-light);border-radius:var(--radius);background:#fff;cursor:pointer;font-size:0.85rem;">
          Cancel
        </button>
        <button type="submit" class="btn-primary-site">Submit Deposit</button>
      </div>
    </form>
  </div>
</div>

<script>
function openCancelModal(id, pet, date) {
  document.getElementById('cancelApptId').value = id;
  document.getElementById('cancelModalDesc').textContent =
    'You are about to cancel the appointment for ' + pet + ' on ' + date + '.';
  document.getElementById('cancelModal').style.display = 'flex';
}
function closeCancelModal() {
  document.getElementById('cancelModal').style.display = 'none';
}
function openDepositModal(id, pet, service) {
  document.getElementById('depositApptId').value = id;
  document.getElementById('depositModalDesc').textContent =
    'Appointment for ' + pet + ' — ' + service;
  document.getElementById('depositModal').style.display = 'flex';
}
function closeDepositModal() {
  document.getElementById('depositModal').style.display = 'none';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>