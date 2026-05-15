<?php
require_once __DIR__ . '/header.php';
require_login();

if (!is_admin()) {
    flash_set('Chỉ quản trị viên mới có quyền truy cập trang này.');
    redirect('index.php');
}

$mysqli = db();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $role = in_array($_POST['role'] ?? '', ['Admin', 'Staff']) ? $_POST['role'] : 'Staff';

    if ($username === '' || $password === '') {
        $error = 'Tên đăng nhập và mật khẩu không được để trống.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare('INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $username, $hash, $fullName, $role);
        if ($stmt->execute()) {
            flash_set('Thêm tài khoản thành công.');
            redirect('users.php');
        } else {
            $error = 'Lỗi khi tạo tài khoản, có thể tên đăng nhập đã tồn tại.';
        }
        $stmt->close();
    }
}
?>
<div class="page-title"><h2>Thêm Tài khoản</h2></div>
<div class="form-card">
    <?php if ($error): ?>
        <div class="flash-message error"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="post" action="user_form.php">
        <label>Tên đăng nhập</label>
        <input type="text" name="username" value="<?= h($_POST['username'] ?? '') ?>" required>
        <label>Mật khẩu</label>
        <input type="password" name="password" required>
        <label>Họ và tên</label>
        <input type="text" name="full_name" value="<?= h($_POST['full_name'] ?? '') ?>">
        <label>Vai trò</label>
        <select name="role">
            <option value="Staff">Staff</option>
            <option value="Admin">Admin</option>
        </select>
        <button type="submit">Lưu</button>
        <a class="button secondary" href="users.php">Quay lại</a>
    </form>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
