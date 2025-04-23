<?php
require_once __DIR__.'/../includes/functions.php';

// 简单认证（实际项目应该使用更安全的认证方式）
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'rocket431022') {
        $_SESSION['admin_logged_in'] = true;
    } else {
        // 显示登录表单
        include __DIR__.'/../templates/admin/login.php';
        exit;
    }
}

// 后台主页面
$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'products':
        // 商品管理
        $conn = db_connect();
        $products = $conn->query("SELECT * FROM products ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
        include __DIR__.'/../templates/admin/products.php';
        break;
        
    case 'orders':
        // 订单管理
        $conn = db_connect();
        $orders = $conn->query("SELECT o.*, p.name as product_name FROM orders o LEFT JOIN products p ON o.product_id = p.id ORDER BY o.id DESC")->fetch_all(MYSQLI_ASSOC);
        include __DIR__.'/../templates/admin/orders.php';
        break;
        
    case 'cards':
        // 卡密管理
        $product_id = intval($_GET['product_id'] ?? 0);
        $conn = db_connect();
        
        $where = $product_id ? "WHERE product_id = $product_id" : "";
        $cards = $conn->query("SELECT c.*, p.name as product_name FROM cards c LEFT JOIN products p ON c.product_id = p.id $where ORDER BY c.id DESC")->fetch_all(MYSQLI_ASSOC);
        
        $products = $conn->query("SELECT id, name FROM products")->fetch_all(MYSQLI_ASSOC);
        include __DIR__.'/../templates/admin/cards.php';
        break;
        
    default:
        // 仪表盘
        $conn = db_connect();
        $stats = [
            'product_count' => $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0],
            'order_count' => $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0],
            'today_sales' => $conn->query("SELECT SUM(amount) FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_row()[0] ?? 0,
        ];
        include __DIR__.'/../templates/admin/dashboard.php';
}