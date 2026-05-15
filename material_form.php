<?php
require_once __DIR__ . '/header.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0 && !is_admin()) {
    flash_set('Chỉ quản trị viên mới có quyền sửa sản phẩm.');
    redirect('materials.php');
}

$mysqli = db();
$material = [
    'material_name' => '',
    'unit' => '',
    'unit_price' => 0.00,
    'description' => '',
];
$selectedSupplierId = 0;
$supplyPrice = '';

$suppliers = [];
$result = $mysqli->query('SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name');
if ($result) {
    $suppliers = $result->fetch_all(MYSQLI_ASSOC);
}

if ($id > 0) {
    $stmt = $mysqli->prepare('SELECT material_name, unit, unit_price, description FROM materials WHERE material_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $material = $row;
    }
    $stmt->close();

    $stmt = $mysqli->prepare('SELECT supplier_id, supply_price FROM supplier_materials WHERE material_id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $selectedSupplierId = $row['supplier_id'];
        $supplyPrice = $row['supply_price'];
    }
    $stmt->close();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['material_name'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $unitPrice = (float)($_POST['unit_price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $selectedSupplierId = (int)($_POST['supplier_id'] ?? 0);
    $supplyPrice = trim($_POST['supply_price'] ?? '');

    if ($name === '' || $unit === '') {
        $error = 'Tên sản phẩm và đơn vị không được để trống.';
    } else {
        if ($id > 0) {
            $stmt = $mysqli->prepare('UPDATE materials SET material_name = ?, unit = ?, unit_price = ?, description = ? WHERE material_id = ?');
            $stmt->bind_param('ssdsi', $name, $unit, $unitPrice, $description, $id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $mysqli->prepare('INSERT INTO materials (material_name, unit, unit_price, description) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssds', $name, $unit, $unitPrice, $description);
            $stmt->execute();
            $id = $stmt->insert_id;
            $stmt->close();
        }

        if ($selectedSupplierId > 0 && $supplyPrice !== '') {
            $stmt = $mysqli->prepare('REPLACE INTO supplier_materials (supplier_id, material_id, supply_price) VALUES (?, ?, ?)');
            $stmt->bind_param('iid', $selectedSupplierId, $id, $supplyPrice);
            $stmt->execute();
            $stmt->close();
        }

        flash_set($id > 0 ? 'Cập nhật sản phẩm thành công.' : 'Thêm sản phẩm thành công.');
        redirect('materials.php');
    }
}
?>
<div class="page-title"><h2><?= $id > 0 ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></h2></div>
<div class="form-card">
    <?php if ($error): ?>
        <div class="flash-message error"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="post" action="material_form.php<?= $id > 0 ? '?id=' . h($id) : '' ?>">
        <label>Tên sản phẩm</label>
        <input type="text" name="material_name" value="<?= h($_POST['material_name'] ?? $material['material_name']) ?>" required>
        <label>Đơn vị</label>
        <input type="text" name="unit" value="<?= h($_POST['unit'] ?? $material['unit']) ?>" required>
        <label>Giá/đơn vị (đ)</label>
        <input type="text" name="unit_price" value="<?= h($_POST['unit_price'] ?? $material['unit_price']) ?>" required>
        <label>Nhà cung cấp (tùy chọn)</label>
        <select name="supplier_id">
            <option value="0">-- Chọn nhà cung cấp --</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= h($supplier['supplier_id']) ?>"<?= $selectedSupplierId == $supplier['supplier_id'] ? ' selected' : '' ?>><?= h($supplier['supplier_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Giá nhập (đ)</label>
        <input type="text" name="supply_price" value="<?= h($_POST['supply_price'] ?? $supplyPrice) ?>" placeholder="Nếu chọn nhà cung cấp">
        <label>Mô tả</label>
        <textarea name="description"><?= h($_POST['description'] ?? $material['description']) ?></textarea>
        <button type="submit">Lưu</button>
        <a class="button secondary" href="materials.php">Quay lại</a>
    </form>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
