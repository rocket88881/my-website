<?php
require_once __DIR__.'/includes/functions.php';

$product_id = intval($_GET['id'] ?? 0);
$product = get_product($product_id);

if (!$product) {
    die("商品不存在或已下架");
}

// 包含页头
include __DIR__.'/templates/header.php';
?>

<div class="product-container">
    <div class="product-image">
        <img src="/assets/images/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>
    <div class="product-info">
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <div class="price">¥<?= number_format($product['price'], 2) ?></div>
        <div class="description">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>
        <div class="stock">库存: <?= $product['stock'] ?>件</div>
        <a href="/buy.php?id=<?= $product['id'] ?>" class="buy-btn">立即购买</a>
    </div>
</div>

<?php
// 包含页脚
include __DIR__.'/templates/footer.php';
?>