document.addEventListener('DOMContentLoaded', function() {
    // Lắng nghe sự thay đổi trên tất cả các checkbox có class 'filter-checkbox' và input giá
    const filterInputs = document.querySelectorAll('.filter-checkbox, .price-input');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', fetchFilteredProducts);
    });

    function fetchFilteredProducts() {
        // Thu thập dữ liệu từ Form lọc
        const form = document.getElementById('filter-form');
        if (!form) return;

        const formData = new FormData(form);
        const queryString = new URLSearchParams(formData).toString();

        // Hiển thị icon Loading...
        const container = document.getElementById('product-list-container');
        if (container) {
            container.innerHTML = '<div class="flex justify-center items-center py-10"><p class="text-gray-500 animate-pulse">Đang lọc sản phẩm...</p></div>';
        }

        // Gọi API lên Laravel
        fetch(`/products/filter?${queryString}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest' // Khai báo đây là request AJAX
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(html => {
            // Thay thế mã HTML cũ bằng lưới sản phẩm mới đã lọc
            if (container) {
                container.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Lỗi khi lọc:', error);
            if (container) {
                container.innerHTML = '<p class="text-red-500 text-center py-10">Có lỗi xảy ra khi tải sản phẩm. Vui lòng thử lại.</p>';
            }
        });
    }
});
