<?php
require_once __DIR__ . '/header.php';
require_login();

if (!is_admin()) {
    flash_set('Chỉ quản trị viên mới có quyền truy cập trang này.');
    redirect('index.php');
}

$mysqli = db();

if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id !== $_SESSION['user']['admin_id']) {
        $stmt = $mysqli->prepare('DELETE FROM users WHERE admin_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        flash_set('Xóa tài khoản thành công.');
    } else {
        flash_set('Không thể xóa chính bạn.');
    }
    redirect('users.php');
}

$result = $mysqli->query('SELECT admin_id, username, full_name, role, created_at FROM users ORDER BY created_at DESC');
$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<div class="page-title"><h2>Quản lý Tài khoản</h2></div>
<div class="toolbar">
    <a class="button" href="user_form.php">Thêm tài khoản</a>
</div>
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Họ và tên</th>
                <th>Vai trò</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= h($user['admin_id']) ?></td>
                    <td><?= h($user['username']) ?></td>
                    <td><?= h($user['full_name']) ?></td>
                    <td><?= h($user['role']) ?></td>
                    <td><?= h($user['created_at']) ?></td>
                    <td>
                        <div class="actions">
                            <?php if ($user['admin_id'] !== $_SESSION['user']['admin_id']): ?>
                                <a class="action-btn delete danger" href="users.php?action=delete&id=<?= h($user['admin_id']) ?>" onclick="return confirm('Xóa tài khoản này?');">Xóa</a>
                            <?php else: ?>
                                <span class="hint">Bạn</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr><td colspan="6">Chưa có tài khoản.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
