<?php
require_once __DIR__ . '/header.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mysqli = db();

$stmt = $mysqli->prepare('SELECT po.order_id, po.order_date, po.expected_date, po.actual_date, po.total_amount, po.order_status, s.supplier_name, u.full_name FROM purchase_orders po JOIN suppliers s ON po.supplier_id = s.supplier_id JOIN users u ON po.admin_id = u.admin_id WHERE po.order_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    flash_set('Phiếu nhập không tồn tại.');
    redirect('orders.php');
}

$stmt = $mysqli->prepare('SELECT od.material_id, m.material_name, od.quantity, od.unit_price FROM order_details od JOIN materials m ON od.material_id = m.material_id WHERE od.order_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<div class="page-title"><h2>Chi tiết Phiếu nhập</h2></div>
<div class="detail-card">
    <p><strong>Mã phiếu:</strong> <?= h($order['order_id']) ?></p>
    <p><strong>Nhà cung cấp:</strong> <?= h($order['supplier_name']) ?></p>
    <p><strong>Người tạo:</strong> <?= h($order['full_name']) ?></p>
    <p><strong>Ngày nhập:</strong> <?= h($order['order_date']) ?></p>
    <p><strong>Ngày dự kiến:</strong> <?= h($order['expected_date']) ?></p>
    <p><strong>Ngày thực tế:</strong> <?= h($order['actual_date']) ?></p>
    <p><strong>Trạng thái:</strong> <?= h($order['order_status']) ?></p>
    <p><strong>Tổng tiền:</strong> <?= number_format($order['total_amount'], 0, ',', '.') ?> đ</p>
</div>
<div class="table-card">
    <h3>Chi tiết sản phẩm</h3>
    <table>
        <thead>
            <tr><th>Sản phẩm</th><th>Số lượng</th><th>Giá nhập</th><th>Thành tiền</th></tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= h($item['material_name']) ?></td>
                    <td><?= h($item['quantity']) ?></td>
                    <td><?= number_format($item['unit_price'], 0, ',', '.') ?> đ</td>
                    <td><?= number_format($item['quantity'] * $item['unit_price'], 0, ',', '.') ?> đ</td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
                <tr><td colspan="4">Không có sản phẩm trong phiếu.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<a class="button secondary" href="orders.php">Quay lại</a>
<?php require_once __DIR__ . '/footer.php'; ?>
