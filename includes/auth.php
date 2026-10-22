<?php
/**
 * Authentication & Session Management
 * Sistem Penilaian Munaqosyah & Cetak Syahadah RTQ Utsmaniyyah
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function is_logged_in() {
    return isset($_SESSION['current_user']) && !empty($_SESSION['current_user']['id']);
}

function get_current_user_data() {
    return $_SESSION['current_user'] ?? null;
}

function is_developer() {
    $u = get_current_user_data();
    if (!$u) return false;
    return (in_array($u['role'] ?? '', ['DEVELOPER', 'SUPER_ADMIN']) || ($u['username'] ?? '') === 'Maul');
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_developer() {
    require_login();
    if (!is_developer()) {
        header('Location: dashboard.php?error=unauthorized');
        exit;
    }
}
