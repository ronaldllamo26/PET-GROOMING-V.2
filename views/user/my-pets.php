<?php
// views/user/my-pets.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
requireUser();

$pdo = getPDO();
$uid = $_SESSION['user_id'];

$error = $success = '';

// ✅ Handle photo upload
function uploadPetPhoto($file) {
    $allowed   = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize   = 3 * 1024 * 1024;
    $uploadDir = __DIR__ . '/../../assets/uploads/pets/';

    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if (!in_array($file['type'], $allowed))  return null;
    if ($file['size'] > $maxSize)            return null;

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('pet_', true) . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
    return 'assets/uploads/pets/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name     = clean($_POST['name']     ?? '');
        $type     = clean($_POST['pet_type'] ?? '');
        $breed    = clean($_POST['breed']    ?? '');
        $size     = clean($_POST['size']     ?? '');
        $notes    = clean($_POST['notes']    ?? '');
        $birthday = $_POST['birthday']       ?? null;
        $weight   = $_POST['weight']         ?? null;
        $photo    = null;

        if (!empty($_FILES['photo']['name'])) {
            $photo = uploadPetPhoto($_FILES['photo']);
            if (!$photo) $error = 'Invalid photo. Use JPG/PNG/WEBP under 3MB.';
        }

        if (!$error) {
            if (!$name || !$type) {
                $error = 'Pet name and type are required.';
            } else {
                $pdo->prepare('INSERT INTO pets (user_id, name, pet_type, breed, size, notes, birthday, weight, photo) VALUES (?,?,?,?,?,?,?,?,?)')
                    ->execute([$uid, $name, $type, $breed, $size, $notes, $birthday ?: null, $weight ?: null, $photo]);
                $success = "{$name} has been added to your profile.";
            }
        }
    }

    if ($action === 'edit') {
        $pid      = (int)($_POST['pet_id'] ?? 0);
        $name     = clean($_POST['name']     ?? '');
        $type     = clean($_POST['pet_type'] ?? '');
        $breed    = clean($_POST['breed']    ?? '');
        $size     = clean($_POST['size']     ?? '');
        $notes    = clean($_POST['notes']    ?? '');
        $birthday = $_POST['birthday']       ?? null;
        $weight   = $_POST['weight']         ?? null;

        $currStmt = $pdo->prepare('SELECT photo FROM pets WHERE id = ? AND user_id = ?');
        $currStmt->execute([$pid, $uid]);
        $currPhoto = $currStmt->fetchColumn();
        $photo = $currPhoto;

        if (!empty($_FILES['photo']['name'])) {
            $newPhoto = uploadPetPhoto($_FILES['photo']);
            if (!$newPhoto) {
                $error = 'Invalid photo. Use JPG/PNG/WEBP under 3MB.';
            } else {
                if ($currPhoto && file_exists(__DIR__ . '/../../' . $currPhoto)) {
                    unlink(__DIR__ . '/../../' . $currPhoto);
                }
                $photo = $newPhoto;
            }
        }

        if (!$error) {
            if (!$name || !$type) {
                $error = 'Pet name and type are required.';
            } else {
                $pdo->prepare('UPDATE pets SET name=?, pet_type=?, breed=?, size=?, notes=?, birthday=?, weight=?, photo=? WHERE id=? AND user_id=?')
                    ->execute([$name, $type, $breed, $size, $notes, $birthday ?: null, $weight ?: null, $photo, $pid, $uid]);
                $success = "{$name}'s profile has been updated.";
            }
        }
    }

    if ($action === 'delete') {
        $pid = (int)($_POST['pet_id'] ?? 0);
        $currStmt = $pdo->prepare('SELECT photo FROM pets WHERE id = ? AND user_id = ?');
        $currStmt->execute([$pid, $uid]);
        $currPhoto = $currStmt->fetchColumn();
        if ($currPhoto && file_exists(__DIR__ . '/../../' . $currPhoto)) {
            unlink(__DIR__ . '/../../' . $currPhoto);
        }
        $pdo->prepare('DELETE FROM pets WHERE id = ? AND user_id = ?')->execute([$pid, $uid]);
        $success = 'Pet removed.';
    }
}

$stmt = $pdo->prepare('SELECT * FROM pets WHERE user_id = ? ORDER BY name');
$stmt->execute([$uid]);
$myPets = $stmt->fetchAll();

// ✅ Grooming history per pet
$groomHistory = [];
$groomCount   = [];
foreach ($myPets as $pet) {
    $gh = $pdo->prepare("
        SELECT a.appt_date, a.appt_time, s.name AS service_name, s.price
        FROM appointments a
        JOIN services s ON a.service_id = s.id
        WHERE a.pet_id = ? AND a.status = 'done'
        ORDER BY a.appt_date DESC, a.appt_time DESC
    ");
    $gh->execute([$pet['id']]);
    $groomHistory[$pet['id']] = $gh->fetchAll();
    $groomCount[$pet['id']]   = count($groomHistory[$pet['id']]);
}

$petImgs = [
    'Dog'    => 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400&q=80&auto=format&fit=crop',
    'Cat'    => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&q=80&auto=format&fit=crop',
    'Bird'   => 'https://images.unsplash.com/photo-1552728089-57bdde30beb3?w=400&q=80&auto=format&fit=crop',
    'Rabbit' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=400&q=80&auto=format&fit=crop',
    'Others' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400&q=80&auto=format&fit=crop',
];

$pageTitle = 'My Pets';
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
      <span class="topbar-title">My Pets</span>
      <div class="d-flex align-items-center gap-3">
        <button class="btn-primary-site btn-sm-site" data-bs-toggle="modal" data-bs-target="#addPetModal">+ Add Pet</button>
        <div class="user-meta">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=EDE6DC&color=3B2F2F&size=64" class="user-avatar"/>
          <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
        </div>
      </div>
    </div>

    <div class="content-pad">

      <?php if ($error): ?>
      <div class="alert-site alert-error mb-3"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
      <div class="alert-site alert-success mb-3"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if (empty($myPets)): ?>
      <div class="card-site">
        <div class="card-body-site text-center py-5">
          <svg width="52" height="52" fill="none" stroke="var(--gray-light)" stroke-width="1" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
          </svg>
          <p style="color:var(--text-muted);margin-bottom:1.2rem;">No pets added yet.</p>
          <button class="btn-primary-site btn-sm-site" data-bs-toggle="modal" data-bs-target="#addPetModal">Add Your First Pet</button>
        </div>
      </div>
      <?php else: ?>
      <div class="row g-3">
        <?php foreach ($myPets as $pet):
          $img = !empty($pet['photo'])
            ? $rootPath . $pet['photo']
            : ($petImgs[$pet['pet_type']] ?? $petImgs['Others']);

          $ageStr = '';
          if (!empty($pet['birthday'])) {
              $bd   = new DateTime($pet['birthday']);
              $now  = new DateTime();
              $diff = $now->diff($bd);
              $ageStr = $diff->y > 0 ? $diff->y . ' yr' . ($diff->y > 1 ? 's' : '') : $diff->m . ' mo';
          }

          $history = $groomHistory[$pet['id']];
          $count   = $groomCount[$pet['id']];
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="pet-card">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($pet['name']) ?>" class="pet-card-img" style="object-fit:cover;"/>
            <div class="pet-card-body">

              <div class="d-flex justify-content-between align-items-start mb-1">
                <h5 class="pet-card-name"><?= htmlspecialchars($pet['name']) ?></h5>
                <span class="badge-site badge-confirmed" style="margin-top:2px;"><?= htmlspecialchars($pet['pet_type']) ?></span>
              </div>

              <p class="pet-card-meta">
                <?= htmlspecialchars($pet['breed'] ?: '—') ?>
                <?php if ($pet['size']): ?> · <?= htmlspecialchars($pet['size']) ?><?php endif; ?>
              </p>

              <div class="d-flex gap-2 mb-2 flex-wrap">
                <?php if ($ageStr): ?>
                <span style="font-size:0.75rem;background:var(--cream);color:var(--text-muted);padding:0.2rem 0.6rem;border-radius:20px;">🎂 <?= $ageStr ?></span>
                <?php endif; ?>
                <?php if ($pet['weight']): ?>
                <span style="font-size:0.75rem;background:var(--cream);color:var(--text-muted);padding:0.2rem 0.6rem;border-radius:20px;">⚖️ <?= $pet['weight'] ?> kg</span>
                <?php endif; ?>
                <!-- ✅ Clickable grooming sessions count -->
                <button type="button"
                 style="font-size:0.75rem;background:var(--cream);color:var(--text-muted);padding:0.2rem 0.7rem;border-radius:20px;border:1px solid var(--gray-light);cursor:<?= $count > 0 ? 'pointer' : 'default' ?>;display:inline-flex;align-items:center;gap:4px;transition:var(--transition);<?= $count > 0 ? 'border-color:var(--tan-light);' : '' ?>"
                 <?= $count > 0 ? "onmouseenter=\"this.style.borderColor='var(--brown)';this.style.color='var(--brown)';\" onmouseleave=\"this.style.borderColor='var(--tan-light)';this.style.color='var(--text-muted)';\" onclick=\"openHistoryModal('" . htmlspecialchars($pet['name']) . "', " . htmlspecialchars(json_encode($history)) . ")\"" : '' ?>>
                 <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" width="13" height="13">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l2.077 1.199M7.848 15.75l1.536-.887m-1.536.887a3 3 0 11-5.196 3 3 3 0 015.196-3zm1.536-.887a2.165 2.165 0 001.083-1.838c.005-.352.054-.695.14-1.025m-1.223 2.863l2.077-1.199m0-3.328a2.165 2.165 0 010 3.328m0-3.328l-2.077-1.2m2.077 4.528l-2.077-1.199"/>
                 </svg>
                 <?= $count ?> session<?= $count != 1 ? 's' : '' ?>
                 <?php if ($count > 0): ?>
                 <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" width="11" height="11" style="opacity:0.5;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                  </svg>
                    <?php endif; ?>
                 </button>
              </div>

              <?php if ($pet['notes']): ?>
              <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem;padding:0.6rem 0.8rem;background:var(--cream);border-radius:var(--radius);">
                <?= htmlspecialchars($pet['notes']) ?>
              </p>
              <?php endif; ?>

              <div class="d-flex gap-2 mt-2 flex-wrap">
                <!-- ✅ Rebook — prefills book.php with pet_id -->
                <a href="book.php?pet_id=<?= $pet['id'] ?>" class="btn-outline-site btn-sm-site flex-fill text-center">
                  <?= $count > 0 ? 'Rebook' : 'Book Session' ?>
                </a>
                <button type="button" class="btn-ghost"
                  onclick="openEditModal(<?= htmlspecialchars(json_encode($pet)) ?>)">Edit</button>
                <form method="POST" onsubmit="return confirm('Remove <?= htmlspecialchars($pet['name']) ?>?');">
                  <input type="hidden" name="action" value="delete"/>
                  <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>"/>
                  <button type="submit" class="btn-danger-site">Remove</button>
                </form>
              </div>

            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<!-- Add Pet Modal -->
<div class="modal fade" id="addPetModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-content-site">
      <div class="modal-header modal-header-site">
        <h5>Add a New Pet</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add"/>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label-site">Pet Photo</label>
              <div id="addPhotoPreviewWrap" style="display:none;margin-bottom:0.5rem;">
                <img id="addPhotoPreview" src="" style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:2px solid var(--gray-light);"/>
              </div>
              <input type="file" name="photo" id="addPhotoInput" class="form-control-site"
                     accept="image/jpeg,image/png,image/webp"
                     onchange="previewPhoto(this, 'addPhotoPreview', 'addPhotoPreviewWrap')"/>
              <p style="font-size:0.75rem;color:var(--text-muted);margin-top:0.3rem;">JPG, PNG or WEBP. Max 3MB. Optional.</p>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Pet Name *</label>
              <input type="text" name="name" class="form-control-site" placeholder="e.g., Max" required/>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Type *</label>
              <select name="pet_type" class="form-control-site" required>
                <option value="">— Select —</option>
                <?php foreach (['Dog','Cat','Bird','Rabbit','Others'] as $t): ?>
                <option value="<?= $t ?>"><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Breed</label>
              <input type="text" name="breed" class="form-control-site" placeholder="e.g., Golden Retriever"/>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Size</label>
              <select name="size" class="form-control-site">
                <option value="">— Select —</option>
                <?php foreach (['Small','Medium','Large','Extra Large'] as $s): ?>
                <option value="<?= $s ?>"><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Birthday</label>
              <input type="date" name="birthday" class="form-control-site" max="<?= date('Y-m-d') ?>"/>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Weight (kg)</label>
              <input type="number" name="weight" class="form-control-site" placeholder="e.g., 5.5" step="0.01" min="0"/>
            </div>
            <div class="col-12">
              <label class="form-label-site">Notes</label>
              <textarea name="notes" class="form-control-site" rows="2"
                        placeholder="Allergies, behavior, or anything we should know..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="padding:1rem 1.5rem;border-top:1px solid var(--gray-light);">
          <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-primary-site btn-sm-site">Add Pet</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Pet Modal -->
<div class="modal fade" id="editPetModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-content-site">
      <div class="modal-header modal-header-site">
        <h5>Edit Pet</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit"/>
        <input type="hidden" name="pet_id" id="editPetId"/>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label-site">Pet Photo</label>
              <div id="editPhotoPreviewWrap" style="margin-bottom:0.5rem;">
                <img id="editPhotoPreview" src="" style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:2px solid var(--gray-light);"/>
              </div>
              <input type="file" name="photo" id="editPhotoInput" class="form-control-site"
                     accept="image/jpeg,image/png,image/webp"
                     onchange="previewPhoto(this, 'editPhotoPreview', null)"/>
              <p style="font-size:0.75rem;color:var(--text-muted);margin-top:0.3rem;">Leave blank to keep current photo.</p>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Pet Name *</label>
              <input type="text" name="name" id="editName" class="form-control-site" required/>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Type *</label>
              <select name="pet_type" id="editType" class="form-control-site" required>
                <option value="">— Select —</option>
                <?php foreach (['Dog','Cat','Bird','Rabbit','Others'] as $t): ?>
                <option value="<?= $t ?>"><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Breed</label>
              <input type="text" name="breed" id="editBreed" class="form-control-site"/>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Size</label>
              <select name="size" id="editSize" class="form-control-site">
                <option value="">— Select —</option>
                <?php foreach (['Small','Medium','Large','Extra Large'] as $s): ?>
                <option value="<?= $s ?>"><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Birthday</label>
              <input type="date" name="birthday" id="editBirthday" class="form-control-site" max="<?= date('Y-m-d') ?>"/>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Weight (kg)</label>
              <input type="number" name="weight" id="editWeight" class="form-control-site" step="0.01" min="0"/>
            </div>
            <div class="col-12">
              <label class="form-label-site">Notes</label>
              <textarea name="notes" id="editNotes" class="form-control-site" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="padding:1rem 1.5rem;border-top:1px solid var(--gray-light);">
          <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-primary-site btn-sm-site">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ✅ Grooming History Modal -->
<div id="historyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:var(--radius);padding:2rem;max-width:480px;width:90%;box-shadow:0 10px 40px rgba(0,0,0,0.15);max-height:85vh;overflow-y:auto;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div style="display:flex;align-items:center;gap:8px;">
  <svg fill="none" stroke="var(--brown)" stroke-width="1.5" viewBox="0 0 24 24" width="18" height="18">
    <path stroke-linecap="round" stroke-linejoin="round" d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l2.077 1.199M7.848 15.75l1.536-.887m-1.536.887a3 3 0 11-5.196 3 3 3 0 015.196-3zm1.536-.887a2.165 2.165 0 001.083-1.838c.005-.352.054-.695.14-1.025m-1.223 2.863l2.077-1.199m0-3.328a2.165 2.165 0 010 3.328m0-3.328l-2.077-1.2m2.077 4.528l-2.077-1.199"/>
  </svg>
  <h5 style="color:var(--brown);margin:0;">Grooming History — <span id="historyPetName"></span></h5>
</div>
      <button onclick="closeHistoryModal()" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:var(--gray);">✕</button>
    </div>
    <div id="historyList"></div>
    <div class="d-flex justify-content-end mt-3">
      <button onclick="closeHistoryModal()" class="btn-primary-site btn-sm-site">Close</button>
    </div>
  </div>
</div>

<script>
function previewPhoto(input, previewId, wrapId) {
  const preview = document.getElementById(previewId);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      if (wrapId) document.getElementById(wrapId).style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function openEditModal(pet) {
  document.getElementById('editPetId').value    = pet.id;
  document.getElementById('editName').value     = pet.name;
  document.getElementById('editType').value     = pet.pet_type;
  document.getElementById('editBreed').value    = pet.breed    || '';
  document.getElementById('editSize').value     = pet.size     || '';
  document.getElementById('editBirthday').value = pet.birthday || '';
  document.getElementById('editWeight').value   = pet.weight   || '';
  document.getElementById('editNotes').value    = pet.notes    || '';

  const preview = document.getElementById('editPhotoPreview');
  if (pet.photo) {
    preview.src = '../../' + pet.photo;
  } else {
    const fallbacks = {
      'Dog':    'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400&q=80',
      'Cat':    'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&q=80',
      'Bird':   'https://images.unsplash.com/photo-1552728089-57bdde30beb3?w=400&q=80',
      'Rabbit': 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=400&q=80',
      'Others': 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400&q=80',
    };
    preview.src = fallbacks[pet.pet_type] || fallbacks['Others'];
  }
  new bootstrap.Modal(document.getElementById('editPetModal')).show();
}

// ✅ Grooming History Modal
function openHistoryModal(petName, history) {
  document.getElementById('historyPetName').textContent = petName;
  const list = document.getElementById('historyList');

  if (!history || history.length === 0) {
    list.innerHTML = '<p style="color:var(--text-muted);text-align:center;">No grooming sessions yet.</p>';
  } else {
    let html = '<table style="width:100%;font-size:0.88rem;border-collapse:collapse;">';
    html += '<thead><tr style="border-bottom:2px solid var(--gray-light);">';
    html += '<th style="padding:8px 6px;color:var(--text-muted);font-weight:500;text-align:left;">Date</th>';
    html += '<th style="padding:8px 6px;color:var(--text-muted);font-weight:500;text-align:left;">Service</th>';
    html += '<th style="padding:8px 6px;color:var(--text-muted);font-weight:500;text-align:right;">Price</th>';
    html += '</tr></thead><tbody>';

    history.forEach((h, i) => {
      const date = new Date(h.appt_date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
      const time = h.appt_time.substring(0,5);
      const bg   = i % 2 === 0 ? 'background:var(--cream);' : '';
      html += `<tr style="${bg}border-bottom:1px solid var(--gray-light);">`;
      html += `<td style="padding:8px 6px;">${date}<br/><span style="font-size:0.75rem;color:var(--text-muted);">${time}</span></td>`;
      html += `<td style="padding:8px 6px;">${h.service_name}</td>`;
      html += `<td style="padding:8px 6px;text-align:right;color:var(--brown);font-weight:600;">₱${parseFloat(h.price).toFixed(2)}</td>`;
      html += '</tr>';
    });

    const total = history.reduce((sum, h) => sum + parseFloat(h.price), 0);
    html += `<tr style="border-top:2px solid var(--gray-light);">`;
    html += `<td colspan="2" style="padding:8px 6px;font-weight:600;">Total Spent</td>`;
    html += `<td style="padding:8px 6px;text-align:right;color:#27ae60;font-weight:700;">₱${total.toFixed(2)}</td>`;
    html += '</tr>';
    html += '</tbody></table>';
    list.innerHTML = html;
  }

  document.getElementById('historyModal').style.display = 'flex';
}
function closeHistoryModal() {
  document.getElementById('historyModal').style.display = 'none';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>