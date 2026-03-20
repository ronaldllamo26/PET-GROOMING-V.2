<?php
// includes/head.php
// Usage: include with $pageTitle set beforehand
$pageTitle = $pageTitle ?? 'PawCare';
$rootPath  = $rootPath  ?? '../';
?>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($pageTitle) ?> — PawCare Grooming</title>
<link rel="icon" type="image/svg+xml" href="<?= $rootPath ?>assets/images/favicon.svg"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="<?= $rootPath ?>assets/css/style.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>