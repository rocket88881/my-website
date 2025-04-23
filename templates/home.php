<?php require __DIR__.'/header.php'; ?>

<div class="container">
    <!-- 分类筛选 -->
    <div class="category-filter">
        <div class="filter-group">
            <span class="filter-label">分类：</span>
            <div class="filter-options">
                <a href="?category=all" class="filter-option active">全部</a>
                <a href="?category=1" class="filter-option">游戏点卡</a>
                <a href="?category=2" class="filter-option">视频会员</a>
                <a href="?category=3" class="filter-option">软件激活</a>
            </div>
        </div>
    </div>

    <!-- 商品列表 -->
    <div class="product-grid">
        <?php
        // 获取商品数据
        $conn = db_connect();
        $category = $_GET['category'] ?? 'all';
        $where = $category !== 'all' ? "WHERE category_id = " . intval($category) : "";
        $products = $conn->query("SELECT * FROM products $where ORDER BY id DESC LIMIT 12")->fetch_all(MYSQLI_ASSOC);
        
        foreach ($products as $product): 
        ?>
        <div class="product-card">
            <div class="product-image">
                <img src="/assets/images/products/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="product-info">
                <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                <div class="product-price">¥<?= number_format($product['price'], 2) ?></div>
                <div class="product-stock">库存: <?= $product['stock'] ?>件</div>
                <a href="/product.php?id=<?= $product['id'] ?>" class="buy-btn">立即购买</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- 分页 -->
    <div class="pagination">
        <a href="#" class="page-item active">1</a>
        <a href="#" class="page-item">2</a>
        <a href="#" class="page-item">3</a>
        <span class="page-item">...</span>
        <a href="#" class="page-item">下一页</a>
    </div>
</div>

<?php require __DIR__.'/footer.php'; ?>