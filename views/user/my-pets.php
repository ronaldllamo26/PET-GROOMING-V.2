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
    $maxSize   = 3 * 1024 * 1024; // 3MB
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

        // Get current photo
        $currStmt = $pdo->prepare('SELECT photo FROM pets WHERE id = ? AND user_id = ?');
        $currStmt->execute([$pid, $uid]);
        $currPhoto = $currStmt->fetchColumn();
        $photo = $currPhoto;

        if (!empty($_FILES['photo']['name'])) {
            $newPhoto = uploadPetPhoto($_FILES['photo']);
            if (!$newPhoto) {
                $error = 'Invalid photo. Use JPG/PNG/WEBP under 3MB.';
            } else {
                // ✅ Delete old photo if exists
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
        // ✅ Delete photo file too
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

$groomCount = [];
foreach ($myPets as $pet) {
    $gc = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE pet_id = ? AND status = 'done'");
    $gc->execute([$pet['id']]);
    $groomCount[$pet['id']] = $gc->fetchColumn();
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
          // ✅ Use uploaded photo or fallback
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
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="pet-card">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($pet['name']) ?>" class="pet-card-img"
                 style="object-fit:cover;"/>
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
                <span style="font-size:0.75rem;background:var(--cream);color:var(--text-muted);padding:0.2rem 0.6rem;border-radius:20px;">✂️ <?= $groomCount[$pet['id']] ?> session<?= $groomCount[$pet['id']] != 1 ? 's' : '' ?></span>
              </div>

              <?php if ($pet['notes']): ?>
              <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem;padding:0.6rem 0.8rem;background:var(--cream);border-radius:var(--radius);">
                <?= htmlspecialchars($pet['notes']) ?>
              </p>
              <?php endif; ?>

              <div class="d-flex gap-2 mt-2">
                <a href="book.php" class="btn-outline-site btn-sm-site flex-fill text-center">Book Session</a>
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

            <!-- ✅ Photo Upload -->
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

            <!-- ✅ Photo Upload -->
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

  // ✅ Show current photo in edit modal
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
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>