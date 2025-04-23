<?php
require_once __DIR__.'/../config/config.php';

// 数据库连接
function db_connect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// 生成订单号
function generate_order_no() {
    return date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

// 易支付签名
function epay_sign($params) {
    ksort($params);
    $sign_str = urldecode(http_build_query($params)).EPAY_KEY;
    return md5($sign_str);
}

// 获取商品信息
function get_product($id) {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// 分配卡密
function assign_card($product_id) {
    $conn = db_connect();
    
    // 开启事务
    $conn->begin_transaction();
    
    try {
        // 锁定并获取一条未使用的卡密
        $stmt = $conn->prepare("SELECT * FROM cards WHERE product_id = ? AND status = 0 LIMIT 1 FOR UPDATE");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $card = $stmt->get_result()->fetch_assoc();
        
        if (!$card) {
            throw new Exception("卡密库存不足");
        }
        
        // 标记卡密为已使用
        $update = $conn->prepare("UPDATE cards SET status = 1 WHERE id = ?");
        $update->bind_param("i", $card['id']);
        $update->execute();
        
        // 减少商品库存
        $conn->query("UPDATE products SET stock = stock - 1 WHERE id = $product_id");
        
        $conn->commit();
        return $card;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}