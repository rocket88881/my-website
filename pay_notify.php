<?php
require_once __DIR__.'/includes/functions.php';

// 验证签名
$params = $_GET;
$sign = $params['sign'] ?? '';
unset($params['sign'], $params['sign_type']);

if (epay_sign($params) !== $sign) {
    die('fail');
}

$order_no = $params['out_trade_no'];
$trade_no = $params['trade_no'];
$money = $params['money'];

// 处理订单
$conn = db_connect();
$order = $conn->query("SELECT * FROM orders WHERE order_no = '$order_no' FOR UPDATE")->fetch_assoc();

if ($order && $order['status'] == 0) {
    // 验证金额
    if (abs($order['amount'] - $money) > 0.01) {
        die('fail');
    }
    
    // 分配卡密
    $card = assign_card($order['product_id']);
    
    if ($card) {
        // 更新订单
        $update = $conn->prepare("UPDATE orders SET status = 1, pay_time = NOW(), card_id = ? WHERE id = ?");
        $update->bind_param("ii", $card['id'], $order['id']);
        $update->execute();
        
        // 发送卡密给用户（示例：邮件发送）
        send_card_to_user($order['contact'], $card, $order);
        
        echo 'success';
        exit;
    }
}

echo 'fail';

// 发送卡密函数
function send_card_to_user($contact, $card, $order) {
    // 这里实现发送逻辑，可以是邮件、短信等
    // 示例：如果是邮箱
    if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
        $subject = "您的订单{$order['order_no']}购买成功";
        $message = "卡号: {$card['card_no']}\n卡密: {$card['card_pwd']}";
        @mail($contact, $subject, $message);
    }
    // 如果是QQ号，可以通过QQ邮件发送
}