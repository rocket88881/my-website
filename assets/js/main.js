// 分类筛选交互
document.addEventListener('DOMContentLoaded', function() {
    // 分类筛选点击效果
    const filterOptions = document.querySelectorAll('.filter-option');
    filterOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            filterOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            // 这里可以添加AJAX加载商品列表
            const category = this.getAttribute('href').split('=')[1];
            loadProducts(category);
        });
    });
    
    // 商品搜索功能
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const keyword = this.querySelector('input').value.trim();
            if (keyword) {
                // AJAX搜索商品
                searchProducts(keyword);
            }
        });
    }
});

function loadProducts(category) {
    // 这里使用AJAX加载商品列表
    fetch(`/api/products.php?category=${category}`)
        .then(response => response.json())
        .then(data => {
            const productGrid = document.querySelector('.product-grid');
            productGrid.innerHTML = '';
            
            data.forEach(product => {
                productGrid.innerHTML += `
                    <div class="product-card">
                        <div class="product-image">
                            <img src="/assets/images/products/${product.image}" alt="${product.name}">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">${product.name}</h3>
                            <div class="product-price">¥${product.price.toFixed(2)}</div>
                            <div class="product-stock">库存: ${product.stock}件</div>
                            <a href="/product.php?id=${product.id}" class="buy-btn">立即购买</a>
                        </div>
                    </div>
                `;
            });
        });
}

function searchProducts(keyword) {
    // 搜索商品实现
    console.log('搜索关键词:', keyword);
    // 实际实现需要对接后端API
}

// 移动菜单切换
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const navMenu = document.querySelector('.nav-menu');

if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function() {
        navMenu.classList.toggle('active');
    });
    
    // 点击菜单外区域关闭菜单
    document.addEventListener('click', function(e) {
        if (!navMenu.contains(e.target) && e.target !== mobileMenuBtn) {
            navMenu.classList.remove('active');
        }
    });
}