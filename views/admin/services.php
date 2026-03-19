<?php
// views/admin/services.php
require_once '../../config/database.php';
require_once '../../config/auth.php';
requireAdmin();

$pdo = getPDO();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name  = clean($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $dur   = (int)($_POST['duration'] ?? 60);
        $desc  = clean($_POST['description'] ?? '');
        if (!$name || $price <= 0) { $error = 'Name and price are required.'; }
        else {
            $pdo->prepare('INSERT INTO services (name, description, price, duration, is_active) VALUES (?,?,?,?,1)')
                ->execute([$name, $desc, $price, $dur]);
            $success = "Service '{$name}' added.";
        }
    }
    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $pdo->prepare('UPDATE services SET is_active = NOT is_active WHERE id=?')->execute([$id]);
        $success = 'Service status updated.';
    }
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $pdo->prepare('DELETE FROM services WHERE id=?')->execute([$id]);
        $success = 'Service deleted.';
    }
}

$services = $pdo->query('SELECT * FROM services ORDER BY name')->fetchAll();

$pageTitle = 'Services';
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
      <span class="topbar-title">Manage Services</span>
      <button class="btn-primary-site btn-sm-site" data-bs-toggle="modal" data-bs-target="#addSvcModal">Add Service</button>
    </div>
    <div class="content-pad">
      <?php if ($error): ?><div class="alert-site alert-error mb-3"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert-site alert-success mb-3"><?= htmlspecialchars($success) ?></div><?php endif; ?>

      <div class="card-site">
        <div class="card-header-site">
          <h5>All Services</h5>
          <span style="font-size:0.8rem;color:var(--text-muted);"><?= count($services) ?> services</span>
        </div>
        <table class="table-site">
          <thead>
            <tr><th>#</th><th>Service Name</th><th>Description</th><th>Price</th><th>Duration</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach ($services as $i => $s): ?>
          <tr>
            <td style="color:var(--gray);"><?= $i+1 ?></td>
            <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
            <td style="color:var(--text-muted);max-width:220px;"><?= htmlspecialchars($s['description'] ?: '—') ?></td>
            <td>₱<?= number_format($s['price'],2) ?></td>
            <td><?= $s['duration'] ? $s['duration'].' min' : '—' ?></td>
            <td>
              <span class="badge-site <?= $s['is_active'] ? 'badge-confirmed' : 'badge-cancelled' ?>">
                <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <form method="POST">
                  <input type="hidden" name="action" value="toggle"/>
                  <input type="hidden" name="id" value="<?= $s['id'] ?>"/>
                  <button type="submit" class="btn-ghost" style="font-size:0.72rem;padding:0.3rem 0.8rem;"><?= $s['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                </form>
                <form method="POST" onsubmit="return confirm('Delete this service?');">
                  <input type="hidden" name="action" value="delete"/>
                  <input type="hidden" name="id" value="<?= $s['id'] ?>"/>
                  <button type="submit" class="btn-danger-site">Delete</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($services)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">No services yet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addSvcModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-content-site">
      <div class="modal-header modal-header-site">
        <h5>Add New Service</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="add"/>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label-site">Service Name *</label>
              <input type="text" name="name" class="form-control-site" placeholder="e.g., Full Spa Package" required/>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Price (₱) *</label>
              <input type="number" name="price" class="form-control-site" placeholder="0.00" step="0.01" min="1" required/>
            </div>
            <div class="col-md-6">
              <label class="form-label-site">Duration (minutes)</label>
              <input type="number" name="duration" class="form-control-site" placeholder="60" min="0"/>
            </div>
            <div class="col-12">
              <label class="form-label-site">Description</label>
              <textarea name="description" class="form-control-site" rows="2" placeholder="Brief description of the service..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="padding:1rem 1.5rem;border-top:1px solid var(--gray-light);">
          <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-primary-site btn-sm-site">Add Service</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
