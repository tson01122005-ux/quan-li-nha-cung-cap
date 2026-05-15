<?php
require_once __DIR__ . '/header.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0 && !is_admin()) {
    flash_set('Chỉ quản trị viên mới có quyền sửa nhà cung cấp.');
    redirect('suppliers.php');
}

$mysqli = db();
$supplier = [
    'supplier_name' => '',
    'contact_email' => '',
    'phone_number' => '',
    'address' => '',
    'status' => 'Active',
];

if ($id > 0) {
    $stmt = $mysqli->prepare('SELECT supplier_name, contact_email, phone_number, address, status FROM suppliers WHERE supplier_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $supplier = $row;
    }
    $stmt->close();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplierName = trim($_POST['supplier_name'] ?? '');
    $email = trim($_POST['contact_email'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['Active', 'Inactive']) ? $_POST['status'] : 'Active';

    if ($supplierName === '' || $email === '') {
        $error = 'Tên công ty và email không được để trống.';
    } else {
        if ($id > 0) {
            $stmt = $mysqli->prepare('UPDATE suppliers SET supplier_name = ?, contact_email = ?, phone_number = ?, address = ?, status = ? WHERE supplier_id = ?');
            $stmt->bind_param('sssssi', $supplierName, $email, $phone, $address, $status, $id);
            $stmt->execute();
            $stmt->close();
            flash_set('Cập nhật nhà cung cấp thành công.');
            redirect('suppliers.php');
        } else {
            $stmt = $mysqli->prepare('INSERT INTO suppliers (supplier_name, contact_email, phone_number, address, status) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('sssss', $supplierName, $email, $phone, $address, $status);
            $stmt->execute();
            $stmt->close();
            flash_set('Thêm nhà cung cấp thành công.');
            redirect('suppliers.php');
        }
    }
}
?>
<div class="page-title"><h2><?= $id > 0 ? 'Sửa nhà cung cấp' : 'Thêm nhà cung cấp' ?></h2></div>
<div class="form-card">
    <?php if ($error): ?>
        <div class="flash-message error"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="post" action="supplier_form.php<?= $id > 0 ? '?id=' . h($id) : '' ?>">
        <label>Tên công ty</label>
        <input type="text" name="supplier_name" value="<?= h($_POST['supplier_name'] ?? $supplier['supplier_name']) ?>" required>
        <label>Email</label>
        <input type="email" name="contact_email" value="<?= h($_POST['contact_email'] ?? $supplier['contact_email']) ?>" required>
        <label>Điện thoại</label>
        <input type="text" name="phone_number" value="<?= h($_POST['phone_number'] ?? $supplier['phone_number']) ?>">
        <label>Địa chỉ</label>
        <textarea name="address"><?= h($_POST['address'] ?? $supplier['address']) ?></textarea>
        <label>Trạng thái</label>
        <select name="status">
            <option value="Active"<?= ($supplier['status'] === 'Active' ? ' selected' : '') ?>>Active</option>
            <option value="Inactive"<?= ($supplier['status'] === 'Inactive' ? ' selected' : '') ?>>Inactive</option>
        </select>
        <button type="submit">Lưu</button>
        <a class="button secondary" href="suppliers.php">Quay lại</a>
    </form>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
