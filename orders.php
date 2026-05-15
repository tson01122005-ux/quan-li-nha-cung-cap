<?php
require_once __DIR__ . '/header.php';
require_login();

$mysqli = db();

if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $mysqli->prepare('DELETE FROM purchase_orders WHERE order_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    flash_set('Xóa phiếu nhập thành công.');
    redirect('orders.php');
}

$sql = 'SELECT po.order_id, s.supplier_name, u.full_name, po.order_date, po.total_amount, po.order_status FROM purchase_orders po JOIN suppliers s ON po.supplier_id = s.supplier_id JOIN users u ON po.admin_id = u.admin_id ORDER BY po.order_date DESC';
$result = $mysqli->query($sql);
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<div class="page-title"><h2>Quản lý Phiếu nhập</h2></div>
<div class="toolbar">
    <a class="button" href="order_form.php">Tạo phiếu nhập</a>
</div>
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Mã phiếu</th>
                <th>Nhà cung cấp</th>
                <th>Người tạo</th>
                <th>Ngày nhập</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= h($order['order_id']) ?></td>
                    <td><?= h($order['supplier_name']) ?></td>
                    <td><?= h($order['full_name']) ?></td>
                    <td><?= h($order['order_date']) ?></td>
                    <td><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</td>
                    <td><?= h($order['order_status']) ?></td>
                    <td>
                        <div class="actions">
                            <a class="action-btn view" href="order_view.php?id=<?= h($order['order_id']) ?>">Xem</a>
                            <?php if (is_admin()): ?>
                                <a class="action-btn delete danger" href="orders.php?action=delete&id=<?= h($order['order_id']) ?>" onclick="return confirm('Bạn có chắc muốn xóa phiếu nhập này?');">Xóa</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
                <tr><td colspan="7">Chưa có phiếu nhập hàng.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
