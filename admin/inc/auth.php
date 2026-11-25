<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['admin_id']) && $current_page !== 'index.php') {
    header('Location: index.php');
    exit;
}

$adminRole = $_SESSION['admin_role'] ?? 'superadmin';
$adminAssocId = (int)($_SESSION['admin_assoc_id'] ?? 0);
$isSuperAdmin = ($adminRole === 'superadmin');
$adminFullName = $_SESSION['admin_fullname'] ?? $_SESSION['admin_user'] ?? 'کاربر';

if (!function_exists('requireSuperAdmin')) {
    function requireSuperAdmin() {
        if (empty($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superadmin') {
            header('Location: index.php');
            exit;
        }
    }
}

// دریافت نام انجمن دبیر در صورت وجود
$currentAssocName = '';
if (!$isSuperAdmin && $adminAssocId > 0 && isset($pdo)) {
    try {
        $stA = $pdo->prepare("SELECT name FROM associations WHERE id = ?");
        $stA->execute([$adminAssocId]);
        $currentAssocName = $stA->fetchColumn() ?: '';
    } catch (Exception $e) {}
}
