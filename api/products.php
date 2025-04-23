<?php
require_once __DIR__.'/../includes/functions.php';

header('Content-Type: application/json');

$category = $_GET['category'] ?? 'all';
$keyword = $_GET['keyword'] ?? '';

$conn = db_connect();

// 基础查询
$where = [];
$params = [];

if ($category !== 'all') {
    $where[] = "category_id = ?";
    $params[] = intval($category);
}

if (!empty($keyword)) {
    $where[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $conn->prepare("SELECT * FROM products $whereClause ORDER BY id DESC LIMIT 12");
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'image' => $row['image'] ?? 'default.jpg',
        'stock' => $row['stock'],
        'description' => $row['description']
    ];
}

echo json_encode($products);