<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function flash_set($message) {
    $_SESSION['flash_message'] = $message;
}

function flash() {
    if (!empty($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function is_logged_in() {
    return !empty($_SESSION['user']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function is_admin() {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'Admin';
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function ensure_material_columns() {
    $mysqli = db();
    $result = $mysqli->query("SHOW COLUMNS FROM materials LIKE 'stock'");
    if ($result && $result->num_rows === 0) {
        $mysqli->query("ALTER TABLE materials ADD COLUMN stock INT NOT NULL DEFAULT 0");
    }
    $result = $mysqli->query("SHOW COLUMNS FROM materials LIKE 'unit_price'");
    if ($result && $result->num_rows === 0) {
        $mysqli->query("ALTER TABLE materials ADD COLUMN unit_price DECIMAL(15,2) NOT NULL DEFAULT 0.00");
    }
}

function get_user_by_id($id) {
    $mysqli = db();
    $stmt = $mysqli->prepare('SELECT admin_id, username, full_name, role FROM users WHERE admin_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

ensure_material_columns();
