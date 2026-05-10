/**
 * Compare Feature - Quản lý Floating Bar & tương tác so sánh sản phẩm
 * Dùng Session qua API, hoạt động trên mọi trang.
 */
(function () {
    'use strict';

    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let compareProducts = [];
    let compareCategoryId = null;
    let searchSlotIndex = null;
    let searchTimer = null;

    // ===== INIT =====
    document.addEventListener('DOMContentLoaded', () => {
        loadCompareData();
    });

    // ===== API: Load dữ liệu khay so sánh =====
    function loadCompareData() {
        fetch('/compare/data')
            .then(r => r.json())
            .then(data => {
                compareProducts = data.products || [];
                compareCategoryId = data.category_id;
                renderBar();
            })
            .catch(() => {});
    }

    // ===== RENDER: Hiển thị floating bar =====
    function renderBar() {
        const bar = document.getElementById('compareBar');
        if (!bar) return;

        if (compareProducts.length === 0) {
            bar.style.display = 'none';
            return;
        }

        bar.style.display = 'block';

        // Render từng slot
        for (let i = 0; i < 3; i++) {
            const slot = document.getElementById(`compareSlot${i}`);
            if (!slot) continue;

            const emptyEl = slot.querySelector('.compare-slot-empty');
            const filledEl = slot.querySelector('.compare-slot-filled');
            const product = compareProducts[i];

            if (product) {
                emptyEl.style.display = 'none';
                filledEl.style.display = 'flex';
                filledEl.querySelector('.compare-slot-img').src = product.thumbnail || 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=100';
                filledEl.querySelector('.compare-slot-name').textContent = product.name;
                filledEl.querySelector('.compare-slot-price').textContent = Number(product.base_price).toLocaleString('vi-VN') + 'đ';
                filledEl.querySelector('.compare-slot-remove').dataset.productId = product.product_id;
            } else {
                emptyEl.style.display = 'flex';
                filledEl.style.display = 'none';
            }
        }

        // Cập nhật badge count
        const badge = document.getElementById('compareCountBadge');
        if (badge) badge.textContent = compareProducts.length;

        // Disable nút nếu < 2 sản phẩm
        const goBtn = document.getElementById('compareGoBtn');
        if (goBtn) {
            if (compareProducts.length < 2) {
                goBtn.style.opacity = '0.5';
                goBtn.style.pointerEvents = 'none';
            } else {
                goBtn.style.opacity = '1';
                goBtn.style.pointerEvents = 'auto';
            }
        }
    }

    // ===== ACTION: Thêm sản phẩm vào khay (gọi từ trang sản phẩm) =====
    window.addToCompare = function (productId) {
        fetch('/compare/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showCompareToast(data.message, 'success');
                loadCompareData();
            } else {
                showCompareToast(data.message, 'error');
            }
        })
        .catch(() => showCompareToast('Có lỗi xảy ra, vui lòng thử lại.', 'error'));
    };

    // ===== ACTION: Xóa sản phẩm khỏi khay =====
    window.removeFromCompare = function (btn) {
        const productId = btn.dataset.productId;
        if (!productId) return;

        fetch(`/compare/remove/${productId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadCompareData();
            }
        });
    };

    // ===== ACTION: Xóa tất cả =====
    window.clearCompare = function () {
        fetch('/compare/clear', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(() => loadCompareData());
    };

    // ===== MODAL: Mở tìm kiếm nhanh =====
    window.openCompareSearch = function (slotIndex) {
        searchSlotIndex = slotIndex;
        const modal = document.getElementById('compareSearchModal');
        if (modal) {
            modal.style.display = 'flex';
            const input = document.getElementById('compareSearchInput');
            if (input) {
                input.value = '';
                input.focus();
            }
            document.getElementById('compareSearchResults').innerHTML = '';
        }
    };

    window.closeCompareSearch = function () {
        const modal = document.getElementById('compareSearchModal');
        if (modal) modal.style.display = 'none';
    };

    // ===== LIVE SEARCH =====
    const searchInput = document.getElementById('compareSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            const keyword = this.value.trim();
            const resultsBox = document.getElementById('compareSearchResults');

            if (keyword.length < 2) {
                resultsBox.innerHTML = '<div style="padding:20px; text-align:center; color:#aaa; font-size:13px;">Nhập ít nhất 2 ký tự để tìm kiếm...</div>';
                return;
            }

            searchTimer = setTimeout(() => {
                const excludeIds = compareProducts.map(p => p.product_id).join(',');
                let url = `/api/products/search-compare?keyword=${encodeURIComponent(keyword)}&exclude=${excludeIds}`;
                if (compareCategoryId) url += `&category_id=${compareCategoryId}`;

                fetch(url)
                    .then(r => r.json())
                    .then(products => {
                        if (products.length === 0) {
                            resultsBox.innerHTML = '<div style="padding:20px; text-align:center; color:#aaa; font-size:13px;">Không tìm thấy sản phẩm phù hợp</div>';
                        } else {
                            resultsBox.innerHTML = products.map(p => `
                                <div class="compare-search-result-item" onclick="selectCompareProduct(${p.product_id})">
                                    <img src="${p.thumbnail || 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=100'}" alt="${p.name}">
                                    <div class="compare-search-result-info">
                                        <div class="compare-search-result-name">${p.name}</div>
                                        <div class="compare-search-result-price">${Number(p.base_price).toLocaleString('vi-VN')}đ</div>
                                    </div>
                                </div>
                            `).join('');
                        }
                    });
            }, 300);
        });
    }

    // Click chọn sản phẩm từ search
    window.selectCompareProduct = function (productId) {
        closeCompareSearch();
        addToCompare(productId);
    };

    // ===== TOAST =====
    function showCompareToast(msg, type) {
        // Tạo toast tạm nếu chưa có
        let toast = document.getElementById('globalCompareToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'globalCompareToast';
            toast.innerHTML = '<i></i><span></span>';
            document.body.appendChild(toast);
        }

        toast.className = 'compare-global-toast ' + type;
        toast.querySelector('i').className = type === 'success'
            ? 'fa-solid fa-circle-check'
            : 'fa-solid fa-circle-exclamation';
        toast.querySelector('span').textContent = msg;

        // Trigger animation
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // Đóng modal khi click overlay
    const modal = document.getElementById('compareSearchModal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeCompareSearch();
        });
    }

})();
