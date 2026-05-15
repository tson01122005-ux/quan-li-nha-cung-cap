<?php
require_once __DIR__ . '/header.php';
require_login();

$mysqli = db();
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    if (!is_admin()) {
        flash_set('Bạn không có quyền xóa nhà cung cấp.');
        redirect('suppliers.php');
    }
    $id = (int)$_GET['id'];
    $stmt = $mysqli->prepare('DELETE FROM suppliers WHERE supplier_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    flash_set('Xóa nhà cung cấp thành công.');
    redirect('suppliers.php');
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $mysqli->prepare('SELECT supplier_id, supplier_name, contact_email, phone_number, address, status, created_at FROM suppliers WHERE supplier_name LIKE ? OR contact_email LIKE ? ORDER BY created_at DESC');
    $stmt->bind_param('ss', $like, $like);
} else {
    $stmt = $mysqli->prepare('SELECT supplier_id, supplier_name, contact_email, phone_number, address, status, created_at FROM suppliers ORDER BY created_at DESC');
}
$stmt->execute();
$result = $stmt->get_result();
$suppliers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<div class="page-title"><h2>Quản lý Nhà cung cấp</h2></div>
<div class="toolbar">
    <form class="search-form" method="get" action="suppliers.php">
        <input type="text" name="q" placeholder="Tìm theo tên, email" value="<?= h($search) ?>">
        <button type="submit">Tìm kiếm</button>
    </form>
    <a class="button" href="supplier_form.php">Thêm Nhà cung cấp</a>
</div>
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Mã</th>
                <th>Tên công ty</th>
                <th>Email</th>
                <th>Điện thoại</th>
                <th>Địa chỉ</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($suppliers as $supplier): ?>
                <tr>
                    <td><?= h($supplier['supplier_id']) ?></td>
                    <td><?= h($supplier['supplier_name']) ?></td>
                    <td><?= h($supplier['contact_email']) ?></td>
                    <td><?= h($supplier['phone_number']) ?></td>
                    <td><?= h($supplier['address']) ?></td>
                    <td><?= h($supplier['status']) ?></td>
                    <td><?= h($supplier['created_at']) ?></td>
                    <td>
                        <div class="actions">
                            <a class="action-btn view" href="supplier_view.php?id=<?= h($supplier['supplier_id']) ?>">Xem</a>
                            <?php if (is_admin()): ?>
                                <a class="action-btn edit" href="supplier_form.php?id=<?= h($supplier['supplier_id']) ?>">Sửa</a>
                                <a class="action-btn delete danger" href="suppliers.php?action=delete&id=<?= h($supplier['supplier_id']) ?>" onclick="return confirm('Bạn có chắc muốn xóa nhà cung cấp này?');">Xóa</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($suppliers)): ?>
                <tr><td colspan="8">Không tìm thấy nhà cung cấp.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
