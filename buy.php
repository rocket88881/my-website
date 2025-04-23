<?php
require_once __DIR__.'/includes/functions.php';

$product_id = intval($_GET['id'] ?? 0);
$product = get_product($product_id);

if (!$product) {
    die("商品不存在或已下架");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact = trim($_POST['contact'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if (empty($contact)) {
        $error = "请输入联系方式";
    } elseif ($quantity < 1 || $quantity > 10) {
        $error = "购买数量不合法";
    } else {
        // 创建订单
        $conn = db_connect();
        $order_no = generate_order_no();
        $amount = $product['price'] * $quantity;
        
        $stmt = $conn->prepare("INSERT INTO orders (order_no, product_id, quantity, amount, contact) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siids", $order_no, $product_id, $quantity, $amount, $contact);
        
        if ($stmt->execute()) {
            // 跳转到支付
            $pay_params = [
                'pid' => EPAY_PID,
                'type' => 'alipay', // 支付方式
                'out_trade_no' => $order_no,
                'notify_url' => SITE_URL.'/pay_notify.php',
                'return_url' => SITE_URL.'/query.php',
                'name' => $product['name'],
                'money' => $amount
            ];
            
            $pay_params['sign'] = epay_sign($pay_params);
            $pay_params['sign_type'] = 'MD5';
            
            $pay_url = EPAY_API.'?'.http_build_query($pay_params);
            header("Location: $pay_url");
            exit;
        } else {
            $error = "订单创建失败: ".$conn->error;
        }
    }
}

include __DIR__.'/templates/header.php';
?>

<div class="buy-container">
    <h2>购买 <?= htmlspecialchars($product['name']) ?></h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label>单价</label>
            <div class="form-control-static">¥<?= number_format($product['price'], 2) ?></div>
        </div>
        
        <div class="form-group">
            <label for="quantity">数量</label>
            <input type="number" id="quantity" name="quantity" value="1" min="1" max="10" class="form-control">
        </div>
        
        <div class="form-group">
            <label for="contact">联系方式</label>
            <input type="text" id="contact" name="contact" placeholder="邮箱/QQ号" required class="form-control">
            <small class="text-muted">用于接收卡密</small>
        </div>
        
        <div class="form-group">
            <label>应付金额</label>
            <div class="form-control-static total-amount">¥<span id="totalAmount"><?= number_format($product['price'], 2) ?></span></div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">去支付</button>
    </form>
</div>

<script>
// 实时计算总金额
document.getElementById('quantity').addEventListener('input', function() {
    var quantity = parseInt(this.value) || 1;
    var price = <?= $product['price'] ?>;
    document.getElementById('totalAmount').textContent = (price * quantity).toFixed(2);
});
</script>

<?php include __DIR__.'/templates/footer.php'; ?>