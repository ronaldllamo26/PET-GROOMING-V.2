<?php
// views/user/book.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/mailer.php';
requireUser();

$pdo = getPDO();
$uid = $_SESSION['user_id'];

$pets = $pdo->prepare('SELECT * FROM pets WHERE user_id = ? ORDER BY name');
$pets->execute([$uid]);
$myPets = $pets->fetchAll();

$services = $pdo->query('SELECT * FROM services WHERE is_active = 1 ORDER BY name')->fetchAll();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $petId     = (int)($_POST['pet_id']     ?? 0);
    $serviceId = (int)($_POST['service_id'] ?? 0);
    $date      = $_POST['appt_date']        ?? '';
    $time      = $_POST['appt_time']        ?? '';
    $notes     = clean($_POST['notes']      ?? '');

    if (!$petId || !$serviceId || !$date || !$time) {
        $error = 'Please fill in all required fields.';
    } elseif ($date < date('Y-m-d')) {
        $error = 'Please select a future date.';
    } else {

        // ✅ Check max 2 active bookings
        $activeCheck = $pdo->prepare("
            SELECT COUNT(*) FROM appointments 
            WHERE user_id = ? AND status IN ('pending', 'confirmed')
        ");
        $activeCheck->execute([$uid]);
        $activeCount = $activeCheck->fetchColumn();

        if ($activeCount >= 2) {
            $error = 'You already have 2 active bookings. Please wait for them to be completed or cancelled before booking again.';
        } else {

            // ✅ Check if slot is taken
            $check = $pdo->prepare("
                SELECT id FROM appointments 
                WHERE appt_date = ? AND appt_time = ? AND status != 'cancelled'
            ");
            $check->execute([$date, $time]);

            if ($check->fetch()) {
                $error = 'That time slot is already taken. Please choose another.';
            } else {

                // ✅ Insert booking
                $pdo->prepare("
                    INSERT INTO appointments (user_id, pet_id, service_id, appt_date, appt_time, notes, status) 
                    VALUES (?,?,?,?,?,?,'pending')
                ")->execute([$uid, $petId, $serviceId, $date, $time, $notes]);

                // ✅ Get user details for email
                $userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
                $userStmt->execute([$uid]);
                $userData = $userStmt->fetch();

                // ✅ Get pet details for email
                $petStmt = $pdo->prepare('SELECT * FROM pets WHERE id = ?');
                $petStmt->execute([$petId]);
                $petData = $petStmt->fetch();

                // ✅ Get service details for email
                $svcStmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
                $svcStmt->execute([$serviceId]);
                $svcData = $svcStmt->fetch();

                $formattedDate = date('F j, Y', strtotime($date));
                $formattedTime = date('g:i A', strtotime($time));

                // ✅ Send confirmation email
                $subject = 'PawCare – Booking Received!';
                $body    = "
                    <div style='font-family:sans-serif;max-width:520px;margin:auto;'>
                        <h2 style='color:#7c5c3e;'>Booking Received! 🐾</h2>
                        <p>Hi <strong>{$userData['name']}</strong>,</p>
                        <p>We've received your grooming appointment request. Please wait for our admin to confirm your booking.</p>
                        <div style='background:#f9f5f0;border-radius:8px;padding:20px;margin:20px 0;'>
                            <h3 style='color:#7c5c3e;margin-top:0;'>Booking Details</h3>
                            <table style='width:100%;font-size:0.9rem;'>
                                <tr><td style='color:#888;padding:4px 0;'>Pet:</td><td><strong>{$petData['name']}</strong></td></tr>
                                <tr><td style='color:#888;padding:4px 0;'>Service:</td><td><strong>{$svcData['name']}</strong></td></tr>
                                <tr><td style='color:#888;padding:4px 0;'>Date:</td><td><strong>{$formattedDate}</strong></td></tr>
                                <tr><td style='color:#888;padding:4px 0;'>Time:</td><td><strong>{$formattedTime}</strong></td></tr>
                                <tr><td style='color:#888;padding:4px 0;'>Status:</td><td><strong style='color:#e6a817;'>Pending Approval</strong></td></tr>
                            </table>
                        </div>
                        <p style='color:#888;font-size:0.85rem;'>You will receive another email once your booking has been confirmed.</p>
                        <p style='color:#888;font-size:0.85rem;'>For cancellations, please notify us at least <strong>24 hours</strong> before your appointment.</p>
                        <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'/>
                        <p style='color:#aaa;font-size:0.75rem;text-align:center;'>PawCare Grooming Studio</p>
                    </div>
                ";

                sendMail($userData['email'], $userData['name'], $subject, $body);
                $success = 'Your appointment has been submitted! Please check your email for confirmation.';
            }
        }
    }
}

$slots = ['09:00','09:30','10:00','10:30','11:00','11:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30'];

$pageTitle = 'Book Appointment';
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
      <span class="topbar-title">Book an Appointment</span>
      <div class="user-meta">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=EDE6DC&color=3B2F2F&size=64" class="user-avatar"/>
        <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
      </div>
    </div>

    <div class="content-pad">
      <div class="row justify-content-center">
        <div class="col-lg-7">

          <div class="card-site">
            <div class="card-header-site"><h5>Schedule a Grooming Session</h5></div>
            <div class="card-body-site">

              <?php if ($error): ?>
              <div class="alert-site alert-error"><?= htmlspecialchars($error) ?></div>
              <?php endif; ?>
              <?php if ($success): ?>
              <div class="alert-site alert-success"><?= htmlspecialchars($success) ?></div>
              <?php endif; ?>

              <?php if (empty($myPets)): ?>
              <div class="text-center py-4">
                <p style="color:var(--text-muted);margin-bottom:1rem;">You need to add a pet before booking.</p>
                <a href="my-pets.php" class="btn-primary-site btn-sm-site">Add a Pet First</a>
              </div>
              <?php else: ?>

              <form method="POST">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label-site">Select Pet *</label>
                    <select name="pet_id" class="form-control-site" required>
                      <option value="">— Choose your pet —</option>
                      <?php foreach ($myPets as $p): ?>
                      <option value="<?= $p['id'] ?>" <?= ($_POST['pet_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['breed'] ?: $p['pet_type']) ?>)
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label-site">Select Service *</label>
                    <select name="service_id" id="svcSel" class="form-control-site" required onchange="showPrice()">
                      <option value="">— Choose a service —</option>
                      <?php foreach ($services as $s): ?>
                      <option value="<?= $s['id'] ?>" data-price="<?= $s['price'] ?>" <?= ($_POST['service_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?> — ₱<?= number_format($s['price'],2) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-12" id="priceBox" style="display:none;">
                    <div style="background:var(--cream);border:1px solid var(--gray-light);border-radius:var(--radius);padding:0.8rem 1rem;font-size:0.88rem;color:var(--text-muted);">
                      Service price: <strong id="priceVal" style="color:var(--brown);"></strong>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label-site">Date *</label>
                    <input type="date" name="appt_date" class="form-control-site"
                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           value="<?= htmlspecialchars($_POST['appt_date'] ?? '') ?>" required/>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label-site">Time Slot *</label>
                    <select name="appt_time" class="form-control-site" required>
                      <option value="">— Choose a time —</option>
                      <?php foreach ($slots as $t): ?>
                      <option value="<?= $t ?>:00" <?= ($_POST['appt_time'] ?? '') === "$t:00" ? 'selected' : '' ?>>
                        <?= date('g:i A', strtotime($t)) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-12">
                    <label class="form-label-site">Special Notes</label>
                    <textarea name="notes" class="form-control-site" rows="3"
                              placeholder="Any allergies, behavioral notes, or special requests..."
                              style="resize:vertical;"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                  </div>

                  <div class="col-12 mt-2">
                    <button type="submit" class="btn-primary-site" style="padding:0.75rem 2rem;">Confirm Booking</button>
                  </div>
                </div>
              </form>

              <?php endif; ?>
            </div>
          </div>

          <div class="card-site mt-3">
            <div class="card-body-site">
              <p style="font-size:0.83rem;color:var(--text-muted);margin:0;">
                Open Monday–Saturday, 9:00 AM – 5:00 PM. Please arrive 10 minutes before your scheduled time.
                For cancellations, notify us at least <strong>24 hours</strong> in advance.
              </p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
function showPrice() {
  const sel = document.getElementById('svcSel');
  const box = document.getElementById('priceBox');
  const val = document.getElementById('priceVal');
  const price = sel.options[sel.selectedIndex]?.dataset?.price;
  if (price) {
    box.style.display = 'block';
    val.textContent = '₱' + parseFloat(price).toFixed(2);
  } else {
    box.style.display = 'none';
  }
}
showPrice();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>