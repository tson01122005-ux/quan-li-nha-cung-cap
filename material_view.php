<?php
require_once __DIR__ . '/header.php';
require_login();

$mysqli = db();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    flash_set('Sản phẩm không hợp lệ.');
    redirect('materials.php');
}

$stmt = $mysqli->prepare('SELECT m.material_id, m.material_name, m.unit, m.unit_price, m.description, m.created_at, GROUP_CONCAT(s.supplier_name SEPARATOR ", ") AS suppliers FROM materials m LEFT JOIN supplier_materials sm ON m.material_id = sm.material_id LEFT JOIN suppliers s ON sm.supplier_id = s.supplier_id WHERE m.material_id = ? GROUP BY m.material_id');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$material = $result->fetch_assoc();
$stmt->close();

if (!$material) {
    flash_set('Không tìm thấy sản phẩm.');
    redirect('materials.php');
}
?>
<div class="page-title"><h2>Xem sản phẩm</h2></div>
<div class="detail-card">
    <p><strong>Tên sản phẩm:</strong> <?= h($material['material_name']) ?></p>
    <p><strong>Đơn vị:</strong> <?= h($material['unit']) ?></p>
    <p><strong>Giá/đơn vị:</strong> <?= number_format($material['unit_price'], 0, ',', '.') ?> đ</p>
    <p><strong>Nhà cung cấp:</strong> <?= h($material['suppliers'] ?: 'Chưa có') ?></p>
    <p><strong>Mô tả:</strong> <?= nl2br(h($material['description'] ?: 'Không có mô tả')) ?></p>
    <p><strong>Ngày tạo:</strong> <?= h($material['created_at']) ?></p>
</div>
<a class="button secondary" href="materials.php">Quay lại</a>
<?php require_once __DIR__ . '/footer.php'; ?>
