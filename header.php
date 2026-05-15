<?php
require_once __DIR__ . '/functions.php';
?><!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <header class="site-header">
        <div class="header-left">
            <div class="brand-mark">QL</div>
            <div>
                <h1><?= h(APP_NAME) ?></h1>
                <p class="brand-subtitle">Quản lý kho, nhà cung cấp và đơn hàng dễ dàng</p>
            </div>
        </div>
        <?php if (is_logged_in()): ?>
            <div class="header-right">
                <span class="header-greet">Xin chào, <strong><?= h($_SESSION['user']['full_name'] ?: $_SESSION['user']['username']) ?></strong></span>
                <a class="button secondary" href="index.php">Vào dashboard</a>
                <a class="button small" href="logout.php">Đăng xuất</a>
            </div>
            <nav class="main-nav">
                <a href="index.php">Dashboard</a>
                <a href="suppliers.php">Nhà cung cấp</a>
                <a href="materials.php">Sản phẩm</a>
                <a href="orders.php">Phiếu nhập</a>
                <?php if (is_admin()): ?>
                    <a href="users.php">Tài khoản</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </header>
    <main class="page-content">
        <?php if ($message = flash()): ?>
            <div class="flash-message"><?= h($message) ?></div>
        <?php endif; ?>
