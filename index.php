<?php
require_once __DIR__ . '/header.php';
require_login();
$mysqli = db();

$totalSuppliers = $mysqli->query('SELECT COUNT(*) AS count FROM suppliers')->fetch_assoc()['count'];
$totalAmount = $mysqli->query('SELECT COALESCE(SUM(total_amount), 0) AS total FROM purchase_orders')->fetch_assoc()['total'];

$topSupplierSql = 'SELECT s.supplier_name, COALESCE(SUM(po.total_amount), 0) AS total_amount FROM suppliers s LEFT JOIN purchase_orders po ON s.supplier_id = po.supplier_id GROUP BY s.supplier_id ORDER BY total_amount DESC LIMIT 1';
$topSupplierQuery = $mysqli->query($topSupplierSql);
$topSupplier = $topSupplierQuery ? $topSupplierQuery->fetch_assoc() : null;

$monthlyOrders = [];
$sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, COALESCE(SUM(total_amount), 0) AS total_amount FROM purchase_orders GROUP BY month ORDER BY month ASC LIMIT 12";
$result = $mysqli->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $monthlyOrders[] = $row;
    }
}
$maxAmount = 0;
foreach ($monthlyOrders as $row) {
    if ($row['total_amount'] > $maxAmount) {
        $maxAmount = $row['total_amount'];
    }
}
?>
<div class="hero-banner">
    <div class="hero-content">
        <p class="hero-eyebrow">Quản lý nhà cung cấp & đơn mua thông minh</p>
        <h1>Quản lý nhà cung cấp & đơn mua thông minh</h1>
        <p>Theo dõi nhà cung cấp, vật tư và tiến độ giao hàng theo thời gian thực. Dữ liệu thống kê cơ bản được công khai bên dưới, giúp bạn nắm nhanh tình hình vận hành.</p>
        <div class="hero-actions">
            <a class="button" href="suppliers.php">Mở dashboard</a>
            <a class="button secondary" href="orders.php">Tạo phiếu nhập</a>
        </div>
    </div>
</div>
<div class="stats-grid">
    <div class="card">
        <h3>Tổng số nhà cung cấp</h3>
        <p class="stat-number"><?= h($totalSuppliers) ?></p>
    </div>
    <div class="card">
        <h3>Tổng tiền nhập</h3>
        <p class="stat-number"><?= number_format($totalAmount, 0, ',', '.') ?> <span style="font-size: 0.6em;">đ</span></p>
    </div>
    <div class="card">
        <h3>Top nhà cung cấp</h3>
        <p style="font-size: 1.1rem; margin: 8px 0 0 0; font-weight: 500;"><?= h($topSupplier['supplier_name'] ?? 'Chưa có dữ liệu') ?></p>
        <p style="font-size: 1.5rem; margin: 8px 0 0 0; color: var(--accent); font-weight: 700;"><?= number_format($topSupplier['total_amount'] ?? 0, 0, ',', '.') ?> đ</p>
    </div>
</div>
<div class="table-card">
    <h3>📈 Nhập hàng theo tháng</h3>
    <table>
        <thead>
            <tr><th>Tháng</th><th>Tổng tiền</th><th>Biểu đồ nhập</th></tr>
        </thead>
        <tbody>
            <?php foreach ($monthlyOrders as $row): ?>
                <?php $bar = $maxAmount > 0 ? min(100, ($row['total_amount'] / $maxAmount) * 100) : 0; ?>
                <tr>
                    <td><strong><?= h($row['month']) ?></strong></td>
                    <td><?= number_format($row['total_amount'], 0, ',', '.') ?> đ</td>
                    <td><div class="chart-bar"><div style="width: <?= round($bar) ?>%"></div></div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($monthlyOrders)): ?>
                <tr><td colspan="3" style="text-align: center; color: var(--muted);">Chưa có dữ liệu nhập hàng</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
