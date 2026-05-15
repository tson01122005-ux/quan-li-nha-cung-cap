<?php
require_once __DIR__ . '/header.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mysqli = db();

$stmt = $mysqli->prepare('SELECT supplier_id, supplier_name, contact_email, phone_number, address, status, created_at FROM suppliers WHERE supplier_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$supplier = $result->fetch_assoc();
$stmt->close();

if (!$supplier) {
    flash_set('Nhà cung cấp không tồn tại.');
    redirect('suppliers.php');
}

$stmt = $mysqli->prepare('SELECT order_id, order_date, total_amount, order_status FROM purchase_orders WHERE supplier_id = ? ORDER BY order_date DESC');
$stmt->bind_param('i', $id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<div class="page-title"><h2>Chi tiết Nhà cung cấp</h2></div>
<div class="detail-card">
    <p><strong>Mã nhà cung cấp:</strong> <?= h($supplier['supplier_id']) ?></p>
    <p><strong>Tên công ty:</strong> <?= h($supplier['supplier_name']) ?></p>
    <p><strong>Email:</strong> <?= h($supplier['contact_email']) ?></p>
    <p><strong>Điện thoại:</strong> <?= h($supplier['phone_number']) ?></p>
    <p><strong>Địa chỉ:</strong> <?= h($supplier['address']) ?></p>
    <p><strong>Trạng thái:</strong> <?= h($supplier['status']) ?></p>
    <p><strong>Ngày tạo:</strong> <?= h($supplier['created_at']) ?></p>
</div>
<div class="table-card">
    <h3>Phiếu nhập liên quan</h3>
    <table>
        <thead>
            <tr><th>Mã phiếu</th><th>Ngày nhập</th><th>Tổng tiền</th><th>Trạng thái</th><th></th></tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="5">Chưa có phiếu nhập hàng.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= h($order['order_id']) ?></td>
                        <td><?= h($order['order_date']) ?></td>
                        <td><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</td>
                        <td><?= h($order['order_status']) ?></td>
                        <td><a href="order_view.php?id=<?= h($order['order_id']) ?>">Xem</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
