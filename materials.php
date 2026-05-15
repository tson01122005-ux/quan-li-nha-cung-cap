<?php
require_once __DIR__ . '/header.php';
require_login();

$mysqli = db();

if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $mysqli->prepare('DELETE FROM materials WHERE material_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    flash_set('Xóa sản phẩm thành công.');
    redirect('materials.php');
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $mysqli->prepare('SELECT m.material_id, m.material_name, m.unit, m.unit_price, m.description, m.created_at, GROUP_CONCAT(s.supplier_name SEPARATOR ", ") AS suppliers FROM materials m LEFT JOIN supplier_materials sm ON m.material_id = sm.material_id LEFT JOIN suppliers s ON sm.supplier_id = s.supplier_id WHERE m.material_name LIKE ? GROUP BY m.material_id ORDER BY m.created_at DESC');
    $stmt->bind_param('s', $like);
} else {
    $stmt = $mysqli->prepare('SELECT m.material_id, m.material_name, m.unit, m.unit_price, m.description, m.created_at, GROUP_CONCAT(s.supplier_name SEPARATOR ", ") AS suppliers FROM materials m LEFT JOIN supplier_materials sm ON m.material_id = sm.material_id LEFT JOIN suppliers s ON sm.supplier_id = s.supplier_id GROUP BY m.material_id ORDER BY m.created_at DESC');
}
$stmt->execute();
$result = $stmt->get_result();
$materials = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<div class="page-title"><h2>Quản lý Sản phẩm</h2></div>
<div class="toolbar">
    <form class="search-form" method="get" action="materials.php">
        <input type="text" name="q" placeholder="Tìm theo tên sản phẩm" value="<?= h($search) ?>">
        <button type="submit">Tìm kiếm</button>
    </form>
    <a class="button" href="material_form.php">Thêm sản phẩm</a>
</div>
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Mã</th>
                <th>Tên sản phẩm</th>
                <th>Đơn vị</th>
                <th>Giá/đơn vị</th>
                <th>Nhà cung cấp</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($materials as $material): ?>
                <tr>
                    <td><?= h($material['material_id']) ?></td>
                    <td><?= h($material['material_name']) ?></td>
                    <td><?= h($material['unit']) ?></td>
                    <td><?= number_format($material['unit_price'], 0, ',', '.') ?> đ</td>
                    <td><?= h($material['suppliers'] ?? 'Chưa có') ?></td>
                    <td><?= h($material['created_at']) ?></td>
                    <td>
                        <div class="actions">
                            <a class="action-btn view" href="material_view.php?id=<?= h($material['material_id']) ?>">Xem</a>
                            <?php if (is_admin()): ?>
                                <a class="action-btn edit" href="material_form.php?id=<?= h($material['material_id']) ?>">Sửa</a>
                                <a class="action-btn delete danger" href="materials.php?action=delete&id=<?= h($material['material_id']) ?>" onclick="return confirm('Xóa sản phẩm này?');">Xóa</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($materials)): ?>
                <tr><td colspan="7">Không có sản phẩm.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
