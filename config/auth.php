<?php
// config/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(string $redirect = '/login.php'): void {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireAdmin(): void {
    requireLogin('../login.php');
    if (!isAdmin()) {
        header('Location: ../views/user/dashboard.php');
        exit;
    }
}

function requireUser(): void {
    requireLogin('../login.php');
    if (isAdmin()) {
        header('Location: ../views/admin/dashboard.php');
        exit;
    }
}

function currentUser(PDO $pdo): ?array {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function clean(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)));
}
