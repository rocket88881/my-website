<?php
require_once __DIR__.'/includes/functions.php';

$order = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_no = trim($_POST['order_no'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    
    if (!empty($order_no) && !empty($contact)) {
        $conn = db_connect();
        $stmt = $conn->prepare("SELECT o.*, p.name as product_name, c.card_no, c.card_pwd 
                              FROM orders o 
                              LEFT JOIN products p ON o.product_id = p.id 
                              LEFT JOIN cards c ON o.card_id = c.id 
                              WHERE o.order_no = ? AND o.contact = ?");
        $stmt->bind_param("ss", $order_no, $contact);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
    }
}

include __DIR__.'/templates/header.php';
?>

<div class="query-container">
    <h2>订单查询</h2>
    
    <form method="post">
        <div class="form-group">
            <label for="order_no">订单号</label>
            <input type="text" id="order_no" name="order_no" required class="form-control">
        </div>
        
        <div class="form-group">
            <label for="contact">联系方式</label>
            <input type="text" id="contact" name="contact" required class="form-control">
            <small class="text-muted">请输入下单时填写的联系方式</small>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">查询订单</button>
    </form>
    
    <?php if ($order): ?>
        <div class="order-result">
            <h3>订单信息</h3>
            <table class="table">
                <tr>
                    <th>订单号</th>
                    <td><?= htmlspecialchars($order['order_no']) ?></td>
                </tr>
                <tr>
                    <th>商品名称</th>
                    <td><?= htmlspecialchars($order['product_name']) ?></td>
                </tr>
                <tr>
                    <th>金额</th>
                    <td>¥<?= number_format($order['amount'], 2) ?></td>
                </tr>
                <tr>
                    <th>状态</th>
                    <td>
                        <?php 
                        switch($order['status']) {
                            case 0: echo '未支付'; break;
                            case 1: echo '已支付'; break;
                            case 2: echo '已发货'; break;
                            default: echo '未知';
                        }
                        ?>
                    </td>
                </tr>
                <?php if ($order['status'] == 1 || $order['status'] == 2): ?>
                    <tr>
                        <th>卡号</th>
                        <td><?= htmlspecialchars($order['card_no'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>卡密</th>
                        <td><?= htmlspecialchars($order['card_pwd'] ?? '') ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="alert alert-warning">未找到相关订单信息</div>
    <?php endif; ?>
</div>

<?php include __DIR__.'/templates/footer.php'; ?>