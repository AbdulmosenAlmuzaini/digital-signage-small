<?php
// config.php - إعداد الاتصال بقاعدة البيانات + التحقق من الجلسة

// قيم افتراضية تناسب تشغيل المشروع عبر Docker
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'signage_db';
$user = getenv('DB_USER') ?: 'signage_user';
$pass = getenv('DB_PASS') ?: 'signage_pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("DB Error: " . $e->getMessage());
}

// أثناء التطوير
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function is_admin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}
?>
