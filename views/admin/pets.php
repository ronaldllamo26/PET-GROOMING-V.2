<?php
// views/admin/pets.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
requireAdmin();

$pdo = getPDO();

$filter = $_GET['type'] ?? 'all';

$sql = "
    SELECT p.*, u.name AS owner_name, u.email AS owner_email, u.phone AS owner_phone,
           COUNT(DISTINCT a.id) AS total_sessions,
           MAX(a.appt_date) AS last_groomed
    FROM pets p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN appointments a ON p.id = a.pet_id AND a.status = 'done'
";

$params = [];
if ($filter !== 'all') {
    $sql .= " WHERE p.pet_type = ?";
    $params[] = $filter;
}
$sql .= " GROUP BY p.id ORDER BY p.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pets = $stmt->fetchAll();

// ✅ Count per type
$typeCounts = $pdo->query("
    SELECT pet_type, COUNT(*) AS total 
    FROM pets 
    GROUP BY pet_type
")->fetchAll(PDO::FETCH_KEY_PAIR);

$totalPets = array_sum($typeCounts);

$petImgs = [
    'Dog'    => 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400&q=80&auto=format&fit=crop',
    'Cat'    => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&q=80&auto=format&fit=crop',
    'Bird'   => 'https://images.unsplash.com/photo-1552728089-57bdde30beb3?w=400&q=80&auto=format&fit=crop',
    'Rabbit' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=400&q=80&auto=format&fit=crop',
    'Others' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400&q=80&auto=format&fit=crop',
];

$pageTitle = 'Pets Overview';
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
      <span class="topbar-title">Pets Overview</span>
      <div class="user-meta">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=3B2F2F&color=C4A882&size=64" class="user-avatar"/>
        <span class="badge-site badge-admin">Admin</span>
      </div>
    </div>

    <div class="content-pad">

      <!-- ✅ Stats -->
      <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
          <div class="stat-card">
            <div class="stat-icon">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
              </svg>
            </div>
            <div>
              <div class="stat-num"><?= $totalPets ?></div>
              <div class="stat-label">Total Pets</div>
            </div>
          </div>
        </div>
        <?php foreach ($typeCounts as $type => $count):
  $faIcon = [
    'Dog'    => 'fa-dog',
    'Cat'    => 'fa-cat',
    'Bird'   => 'fa-dove',
    'Rabbit' => 'fa-paw',
    'Others' => 'fa-paw',
  ][$type] ?? 'fa-paw';
?>
<div class="col-md-2 col-6">
  <div class="stat-card">
    <div class="stat-icon">
      <i class="fa-solid <?= $faIcon ?>" style="font-size:1rem;color:var(--brown-light);"></i>
    </div>
    <div>
      <div class="stat-num"><?= $count ?></div>
      <div class="stat-label"><?= $type ?>s</div>
    </div>
  </div>
</div>
<?php endforeach; ?>
      </div>

      <!-- ✅ Filter tabs -->
      <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="?type=all"
           style="padding:0.4rem 1rem;border-radius:var(--radius);font-size:0.78rem;font-weight:500;letter-spacing:0.8px;text-transform:uppercase;text-decoration:none;transition:var(--transition);
           <?= $filter==='all' ? 'background:var(--brown);color:#fff;border:1px solid var(--brown);' : 'background:var(--white);color:var(--text-muted);border:1px solid var(--gray-light);' ?>">
          All (<?= $totalPets ?>)
        </a>
        <?php foreach ($typeCounts as $type => $count): ?>
        <a href="?type=<?= urlencode($type) ?>"
           style="padding:0.4rem 1rem;border-radius:var(--radius);font-size:0.78rem;font-weight:500;letter-spacing:0.8px;text-transform:uppercase;text-decoration:none;transition:var(--transition);
           <?= $filter===$type ? 'background:var(--brown);color:#fff;border:1px solid var(--brown);' : 'background:var(--white);color:var(--text-muted);border:1px solid var(--gray-light);' ?>">
          <?= $type ?> (<?= $count ?>)
        </a>
        <?php endforeach; ?>
      </div>

      <!-- ✅ Pets Table -->
      <div class="card-site">
        <div class="card-header-site">
          <h5>All Pets</h5>
          <span style="font-size:0.8rem;color:var(--text-muted);"><?= count($pets) ?> record<?= count($pets) !== 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($pets)): ?>
        <div class="card-body-site text-center py-4">
          <p style="color:var(--text-muted);">No pets found.</p>
        </div>
        <?php else: ?>
        <table class="table-site">
          <thead>
            <tr>
              <th>#</th>
              <th>Pet</th>
              <th>Type</th>
              <th>Breed</th>
              <th>Size</th>
              <th>Age</th>
              <th>Weight</th>
              <th>Owner</th>
              <th>Sessions</th>
              <th>Last Groomed</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($pets as $i => $p):
            $img = !empty($p['photo'])
              ? $rootPath . $p['photo']
              : ($petImgs[$p['pet_type']] ?? $petImgs['Others']);

            $ageStr = '—';
            if (!empty($p['birthday'])) {
                $bd   = new DateTime($p['birthday']);
                $now  = new DateTime();
                $diff = $now->diff($bd);
                $ageStr = $diff->y > 0
                    ? $diff->y . ' yr' . ($diff->y > 1 ? 's' : '')
                    : $diff->m . ' mo';
            }
          ?>
          <tr>
            <td style="color:var(--gray);"><?= $i + 1 ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <img src="<?= htmlspecialchars($img) ?>"
                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:1px solid var(--gray-light);"
                     alt="<?= htmlspecialchars($p['name']) ?>"/>
                <strong><?= htmlspecialchars($p['name']) ?></strong>
              </div>
            </td>
            <td>
              <span class="badge-site badge-confirmed"><?= htmlspecialchars($p['pet_type']) ?></span>
            </td>
            <td><?= htmlspecialchars($p['breed'] ?: '—') ?></td>
            <td><?= htmlspecialchars($p['size'] ?: '—') ?></td>
            <td><?= $ageStr ?></td>
            <td><?= $p['weight'] ? $p['weight'] . ' kg' : '—' ?></td>
            <td>
              <strong><?= htmlspecialchars($p['owner_name']) ?></strong><br/>
              <span style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($p['owner_email']) ?></span>
              <?php if ($p['owner_phone']): ?>
              <br/><span style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($p['owner_phone']) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <span style="font-weight:600;color:var(--brown);"><?= $p['total_sessions'] ?></span>
              <span style="font-size:0.75rem;color:var(--text-muted);"> session<?= $p['total_sessions'] != 1 ? 's' : '' ?></span>
            </td>
            <td style="font-size:0.82rem;">
              <?= $p['last_groomed'] ? date('M j, Y', strtotime($p['last_groomed'])) : '<span style="color:var(--text-muted);">Never</span>' ?>
            </td>
            <td style="font-size:0.82rem;max-width:150px;">
              <?= $p['notes'] ? htmlspecialchars($p['notes']) : '<span style="color:var(--text-muted);">—</span>' ?>
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