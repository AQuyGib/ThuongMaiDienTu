document.addEventListener('DOMContentLoaded', function() {
    // Lắng nghe sự thay đổi trên tất cả các input lọc
    const filterInputs = document.querySelectorAll('.filter-checkbox, .price-input, .filter-input');
    const resetBtn = document.getElementById('reset-filters');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', fetchFilteredProducts);
        // Đối với input text/number, lắng nghe sự kiện 'input' để lọc realtime hoặc 'blur'
        if (input.tagName === 'INPUT' && input.type !== 'checkbox') {
            input.addEventListener('blur', fetchFilteredProducts);
        }
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            const form = document.getElementById('filter-form');
            if (form) {
                form.reset();
                // Trigger fetch sau khi reset
                setTimeout(fetchFilteredProducts, 100);
            }
        });
    }

    // Xử lý phân trang AJAX
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('a[data-paginate]');
        if (paginationLink) {
            e.preventDefault();
            const url = paginationLink.getAttribute('href');
            fetchProductsByUrl(url);
        }
    });

    function fetchFilteredProducts() {
        const form = document.getElementById('filter-form');
        if (!form) return;

        const formData = new FormData(form);
        const queryString = new URLSearchParams(formData).toString();
        const url = `/products/filter?${queryString}`;
        
        fetchProductsByUrl(url);
    }

    function fetchProductsByUrl(url) {
        const container = document.getElementById('product-list-container');
        const countDisplay = document.getElementById('product-count');

        if (container) {
            container.innerHTML = `
                <div class="flex justify-center items-center py-20">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-gray-500 animate-pulse">Đang cập nhật sản phẩm...</p>
                    </div>
                </div>`;
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(html => {
            if (container) {
                container.innerHTML = html;
            }
            
            // Cập nhật số lượng sản phẩm (giả định server trả về thông tin hoặc parse từ HTML)
            // Trong thực tế, nên trả về JSON { html: '...', total: 123 }
            updateProductCount(html);
        })
        .catch(error => {
            console.error('Lỗi khi lọc:', error);
            if (container) {
                container.innerHTML = '<p class="text-red-500 text-center py-10">Có lỗi xảy ra khi tải sản phẩm. Vui lòng thử lại.</p>';
            }
        });
    }

    function updateProductCount(html) {
        const countDisplay = document.getElementById('product-count');
        if (!countDisplay) return;

        // Tạo một element tạm để parse HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const products = doc.querySelectorAll('.product-card').length;
        
        countDisplay.innerText = products;
    }
});
