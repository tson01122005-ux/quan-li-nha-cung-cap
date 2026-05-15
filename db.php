<?php
require_once __DIR__ . '/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($mysqli->connect_errno) {
    die('Lỗi kết nối CSDL: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

function db() {
    global $mysqli;
    return $mysqli;
}
