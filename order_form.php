<?php
require_once __DIR__ . '/header.php';
require_login();

$mysqli = db();

$suppliers = $mysqli->query('SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name')->fetch_all(MYSQLI_ASSOC);
$materials = $mysqli->query('SELECT material_id, material_name, unit, stock, unit_price FROM materials ORDER BY material_name')->fetch_all(MYSQLI_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplierId = (int)($_POST['supplier_id'] ?? 0);
    $materialId = (int)($_POST['material_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $unitPrice = (float)($_POST['unit_price'] ?? 0);
    $orderStatus = 'Completed';
    $orderDate = date('Y-m-d H:i:s');
    $expectedDate = date('Y-m-d H:i:s', strtotime('+7 days'));

    if ($supplierId <= 0 || $materialId <= 0 || $quantity <= 0 || $unitPrice <= 0) {
        $error = 'Vui lòng chọn nhà cung cấp, sản phẩm, số lượng và giá nhập hợp lệ.';
    } else {
        $totalAmount = $quantity * $unitPrice;
        $adminId = $_SESSION['user']['admin_id'];

        $stmt = $mysqli->prepare('INSERT INTO purchase_orders (supplier_id, admin_id, order_date, expected_date, actual_date, total_amount, order_status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iissdss', $supplierId, $adminId, $orderDate, $expectedDate, $orderDate, $totalAmount, $orderStatus);
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();

        $stmt = $mysqli->prepare('INSERT INTO order_details (order_id, material_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiid', $orderId, $materialId, $quantity, $unitPrice);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare('UPDATE materials SET stock = stock + ? WHERE material_id = ?');
        $stmt->bind_param('ii', $quantity, $materialId);
        $stmt->execute();
        $stmt->close();

        flash_set('Tạo phiếu nhập thành công.');
        redirect('orders.php');
    }
}
?>
<div class="page-title"><h2>Tạo Phiếu nhập</h2></div>
<div class="form-card">
    <?php if ($error): ?>
        <div class="flash-message error"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="post" action="order_form.php">
        <label>Nhà cung cấp</label>
        <select name="supplier_id" required>
            <option value="0">-- Chọn nhà cung cấp --</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= h($supplier['supplier_id']) ?>"<?= (int)($_POST['supplier_id'] ?? 0) === $supplier['supplier_id'] ? ' selected' : '' ?>><?= h($supplier['supplier_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Sản phẩm</label>
        <select id="material_id" name="material_id" required>
            <option value="0" data-unit-price="">-- Chọn sản phẩm --</option>
            <?php foreach ($materials as $material): ?>
                <option value="<?= h($material['material_id']) ?>" data-unit-price="<?= h($material['unit_price']) ?>"<?= (int)($_POST['material_id'] ?? 0) === $material['material_id'] ? ' selected' : '' ?>><?= h($material['material_name']) ?> (<?= h($material['unit']) ?>)</option>
            <?php endforeach; ?>
        </select>
        <label>Số lượng</label>
        <input type="number" name="quantity" value="<?= h($_POST['quantity'] ?? '') ?>" min="1" required>
        <label>Giá nhập (đ)</label>
        <input id="unit_price" type="text" name="unit_price" value="<?= h($_POST['unit_price'] ?? '') ?>" required>
        <button type="submit">Tạo phiếu</button>
        <a class="button secondary" href="orders.php">Quay lại</a>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var materialSelect = document.getElementById('material_id');
            var unitPriceInput = document.getElementById('unit_price');
            if (!materialSelect || !unitPriceInput) return;

            function updateUnitPrice() {
                var selectedOption = materialSelect.selectedOptions[0];
                if (selectedOption && selectedOption.dataset.unitPrice) {
                    unitPriceInput.value = selectedOption.dataset.unitPrice;
                } else if (!materialSelect.value || materialSelect.value === '0') {
                    unitPriceInput.value = '';
                }
            }

            materialSelect.addEventListener('change', updateUnitPrice);
            updateUnitPrice();
        });
    </script>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
