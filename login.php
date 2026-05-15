<?php
require_once __DIR__ . '/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập tên đăng nhập và mật khẩu.';
    } else {
        $mysqli = db();
        $stmt = $mysqli->prepare('SELECT admin_id, username, password, full_name, role FROM users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $hash = $user['password'];
            $verified = false;
            if (password_verify($password, $hash)) {
                $verified = true;
            } elseif ($hash === $password) {
                $verified = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = $mysqli->prepare('UPDATE users SET password = ? WHERE admin_id = ?');
                $update->bind_param('si', $newHash, $user['admin_id']);
                $update->execute();
                $update->close();
            }

            if ($verified) {
                $_SESSION['user'] = [
                    'admin_id' => $user['admin_id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role'],
                ];
                redirect('index.php');
            }
        }
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <h2>Đăng nhập</h2>
        <?php if ($error): ?>
            <div class="flash-message error"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" value="<?= h($_POST['username'] ?? '') ?>" required>
            <label>Mật khẩu</label>
            <input type="password" name="password" required>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</div>
</body>
</html>
