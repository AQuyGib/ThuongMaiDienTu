/**
 * Script xử lý tính năng So sánh sản phẩm tại Frontend
 * 
 * Các chức năng chính:
 * 1. Lưu trữ danh sách sản phẩm so sánh vào LocalStorage của trình duyệt.
 * 2. Đồng bộ hóa danh sách này lên server thông qua các API.
 * 3. Quản lý trạng thái các nút "So sánh" trên các card sản phẩm trên trang (đổi màu, text).
 * 4. Hiển thị Floating Badge (Badge nổi) báo số lượng sản phẩm đang so sánh ở góc màn hình.
 * 5. Lấy dữ liệu sản phẩm từ API và dựng bảng so sánh (Desktop + Mobile) động bằng JavaScript.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Khóa dùng để lưu trữ danh sách ID sản phẩm dưới LocalStorage
    const STORAGE_KEY = 'compare_products';
    // Số lượng sản phẩm tối đa được phép so sánh cùng lúc
    const MAX_ITEMS = 4;
    
    // Các endpoint API tương tác với Backend Laravel
    const PRODUCT_API = '/compare/data'; // Lấy dữ liệu sản phẩm chi tiết & ma trận so sánh
    const SYNC_API = '/compare/sync';   // Gửi danh sách ID lên server để lưu vào DB/Session
    const SEARCH_API = '/api/products/search-compare'; // Tìm kiếm nhanh sản phẩm trên bảng so sánh
    
    // Lấy các phần tử DOM trên trang giao diện so sánh
    const clearAllBtn = document.getElementById('compareClearAllBtn'); // Nút xóa tất cả sản phẩm so sánh
    const emptyState = document.getElementById('compareEmptyState');   // Khối hiển thị khi trống danh sách
    const tableWrap = document.getElementById('compareTableWrap');     // Khối chứa bảng so sánh (Desktop)
    const headEl = document.getElementById('compareHead');             // Phần tiêu đề (Header) của bảng
    const bodyEl = document.getElementById('compareBody');             // Phần nội dung (Body) của bảng
    const mobileCardsEl = document.getElementById('compareMobileCards'); // Khu vực hiển thị bảng dạng thẻ trên Mobile
    const compareMetaEl = document.getElementById('compareMeta');       // Phần tử hiển thị số lượng sản phẩm
    
    // Kiểm tra xem người dùng hiện tại đã đăng nhập hay chưa (thông qua mảng ID khởi tạo từ server)
    const isLoggedIn = Array.isArray(window.__SERVER_COMPARE_IDS__);

    /**
     * Lấy token CSRF từ thẻ meta để thực hiện các request POST/PUT/DELETE bảo mật trong Laravel
     */
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    /**
     * Lấy danh sách ID sản phẩm đang so sánh từ LocalStorage
     * Trả về một mảng chứa các số nguyên ID hợp lệ.
     */
    function getCompareIds() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            const ids = raw ? JSON.parse(raw) : [];
            return Array.isArray(ids) ? ids.map((id) => Number(id)).filter(Boolean) : [];
        } catch (e) {
            return [];
        }
    }

    /**
     * Đồng bộ hóa danh sách ID sản phẩm hiện tại lên Server (DB hoặc Session Laravel)
     */
    async function syncCompareIds(ids) {
        if (!isLoggedIn) return; // Chỉ đồng bộ qua API nếu là thành viên đã đăng nhập

        try {
            await fetch(SYNC_API, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ ids }),
            });
        } catch (e) {
            // Vẫn giữ lại trạng thái cục bộ LocalStorage của người dùng ngay cả khi mất kết nối mạng
            console.error('Lỗi đồng bộ danh sách so sánh lên server:', e);
        }
    }

    /**
     * Lưu danh sách ID sản phẩm so sánh vào LocalStorage và cập nhật lại toàn bộ giao diện
     */
    function saveCompareIds(ids) {
        // Loại bỏ ID trùng lặp, lọc các giá trị không hợp lệ và giới hạn số lượng tối đa
        const uniqueIds = Array.from(new Set(ids.map((id) => Number(id)).filter(Boolean))).slice(0, MAX_ITEMS);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(uniqueIds));
        
        // Vẽ lại trạng thái giao diện và đồng bộ lên server
        renderCompareState();
        syncCompareIds(uniqueIds);
        return uniqueIds;
    }

    /**
     * Chuyển trạng thái nút So sánh (bật/tắt trạng thái loading khi đang xử lý)
     */
    function setCompareButtonLoading(productId, loading) {
        const btn = document.querySelector(`.compare-card-btn[data-product-id="${productId}"]`);
        if (!btn) return;

        btn.classList.toggle('is-loading', loading);
        btn.disabled = loading;
        const icon = btn.querySelector('.compare-card-btn-icon');
        const label = btn.querySelector('.compare-card-btn-label');
        if (icon) icon.classList.toggle('hidden', loading);
        if (label) label.textContent = loading ? 'Đang so sánh...' : (btn.dataset.compareStateLabel || 'So sánh');
    }

    /**
     * Tạo hiệu ứng nảy (pop animation) khi nhấn nút thêm vào so sánh
     */
    function animateCompareButton(productId) {
        const btn = document.querySelector(`.compare-card-btn[data-product-id="${productId}"]`);
        if (!btn) return;
        btn.classList.add('compare-btn-pop');
        setTimeout(() => btn.classList.remove('compare-btn-pop'), 180);
    }

    /**
     * Hiển thị thông báo Toast (Popup tự tắt) nhỏ gọn, đẹp mắt ở góc màn hình
     */
    function showToast(message, type = 'success') {
        let toast = document.getElementById('compareToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'compareToast';
            toast.className = 'compare-global-toast success';
            toast.innerHTML = '<i class="fa-solid fa-circle-check"></i><span></span>';
            document.body.appendChild(toast);
        }
        toast.classList.remove('success', 'error');
        toast.classList.add(type);
        toast.querySelector('i').className = type === 'success'
            ? 'fa-solid fa-circle-check'
            : 'fa-solid fa-circle-exclamation';
        toast.querySelector('span').textContent = message;
        toast.classList.add('show');
        // Tự động ẩn thông báo sau 2.5 giây
        setTimeout(() => toast.classList.remove('show'), 2500);
    }

    /**
     * Xác định xem cột sản phẩm nào có chứa thuộc tính có sự khác biệt so với các sản phẩm khác.
     * Dùng để tô màu hoặc đánh dấu cột có thông số độc đáo.
     */
    function computeColumnDiffs(rows, products) {
        const result = {};
        products.forEach((product) => {
            result[product.product_id] = false;
        });

        rows.forEach((row) => {
            if (!row.is_different) return;
            products.forEach((product) => {
                const value = row.values?.[products.findIndex((p) => p.product_id === product.product_id)];
                if (value !== '—' && value !== null && value !== undefined) {
                    result[product.product_id] = true;
                }
            });
        });

        return result;
    }

    /**
     * Vẽ lại Floating Badge hiển thị số lượng sản phẩm so sánh nổi ở góc màn hình
     */
    function renderFloatingBadge() {
        const ids = getCompareIds();
        const badge = document.getElementById('compareFloatingBadge');
        const badgeCount = document.getElementById('compareFloatingBadgeCount');
        if (badgeCount) badgeCount.textContent = ids.length;
        // Ẩn badge nếu không có sản phẩm nào trong danh sách so sánh
        if (badge) badge.classList.toggle('hidden', ids.length === 0);
        renderCompareButtons();
    }

    /**
     * Đồng bộ hóa và cập nhật lại giao diện hiển thị của toàn bộ các nút "So sánh" trên trang.
     * Nếu sản phẩm đã được chọn: Chuyển nút sang trạng thái hoạt động (màu xanh, text "Đã so sánh").
     * Nếu chưa được chọn: Chuyển nút về trạng thái bình thường (màu trắng, viền xám, text "So sánh").
     */
    function renderCompareButtons() {
        const ids = getCompareIds();
        document.querySelectorAll('.compare-card-btn').forEach((btn) => {
            const productId = Number(btn.dataset.productId);
            const isAdded = ids.includes(productId);
            const status = btn.closest('.product-card')?.querySelector('.compare-status-badge');
            const compareText = btn.querySelector('.compare-card-btn-label');
            const icon = btn.querySelector('.compare-card-btn-icon');

            // Toggle các class CSS Tailwind tương ứng với trạng thái Đã thêm / Chưa thêm
            btn.classList.toggle('bg-blue-600', isAdded);
            btn.classList.toggle('text-white', isAdded);
            btn.classList.toggle('shadow-md', isAdded);
            btn.classList.toggle('ring-2', isAdded);
            btn.classList.toggle('ring-blue-200', isAdded);
            btn.classList.toggle('bg-white', !isAdded);
            btn.classList.toggle('text-blue-600', !isAdded);
            btn.classList.toggle('border', !isAdded);
            btn.classList.toggle('border-gray-200', !isAdded);
            btn.title = isAdded ? 'Đã so sánh' : 'So sánh';
            btn.dataset.compareStateLabel = isAdded ? 'Đã so sánh' : 'So sánh';

            if (status) {
                status.classList.toggle('hidden', !isAdded);
            }
            if (compareText) {
                compareText.textContent = isAdded ? 'Đã so sánh' : 'So sánh';
            }
            if (icon) {
                icon.classList.toggle('text-white', isAdded);
            }
        });
    }

    /**
     * Thêm một sản phẩm vào danh sách so sánh
     */
    async function addToCompare(productId) {
        const id = Number(productId);
        if (!id) return;

        const ids = getCompareIds();
        // Kiểm tra xem sản phẩm đã có trong danh sách chưa
        if (ids.includes(id)) {
            showToast('Sản phẩm đã có trong danh sách so sánh.', 'error');
            return;
        }
        // Kiểm tra số lượng sản phẩm so sánh tối đa cho phép
        if (ids.length >= MAX_ITEMS) {
            showToast(`Chỉ có thể so sánh tối đa ${MAX_ITEMS} sản phẩm.`, 'error');
            return;
        }

        setCompareButtonLoading(id, true);
        animateCompareButton(id);
        const updatedIds = saveCompareIds([...ids, id]);
        showToast('Đã thêm vào danh sách so sánh.');
        await syncCompareIds(updatedIds);
        setCompareButtonLoading(id, false);
        // Tự động load lại bảng so sánh nếu đang đứng ở trang so sánh
        await loadComparePage();
    }

    /**
     * Xóa một sản phẩm khỏi danh sách so sánh
     */
    async function removeCompareId(productId) {
        const id = Number(productId);
        const ids = getCompareIds().filter((item) => item !== id);
        saveCompareIds(ids);
        await loadComparePage();
    }

    /**
     * Xóa toàn bộ danh sách sản phẩm so sánh
     */
    async function clearCompare() {
        localStorage.removeItem(STORAGE_KEY);
        await syncCompareIds([]);
        renderCompareState();
        await loadComparePage();
    }

    /**
     * Định dạng số thành chuỗi tiền tệ VND (Ví dụ: 15000000 -> 15.000.000 ₫)
     */
    function formatCurrency(value) {
        const num = Number(value || 0);
        return num ? `${num.toLocaleString('vi-VN')} ₫` : '—';
    }

    /**
     * Hàm escape chống tấn công XSS khi render dữ liệu động lấy từ API vào DOM
     */
    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    /**
     * Định dạng giá trị thông số kỹ thuật để hiển thị đẹp mắt
     */
    function formatSpecValue(value) {
        if (value === null || value === undefined || value === '') return '—';
        if (Array.isArray(value)) return escapeHtml(value.join(', '));
        if (typeof value === 'object') return escapeHtml(JSON.stringify(value));
        return escapeHtml(value);
    }

    /**
     * Render bảng so sánh ở dạng danh sách các Card (thẻ) tối ưu riêng cho màn hình di động (Mobile)
     */
    function renderComparePageMobile(products, rows) {
        if (!mobileCardsEl) return;
        compareMetaEl && (compareMetaEl.textContent = `${products.length} sản phẩm`);
        const diffColumns = computeColumnDiffs(rows, products);

        mobileCardsEl.innerHTML = products.map((product) => {
            // Render 6 thông số đầu tiên lên giao diện mobile để tránh quá dài dòng
            const specItems = rows.slice(0, 6).map((row) => {
                const isDifferent = row.is_different;
                const value = row.values?.[products.findIndex((p) => p.product_id === product.product_id)] ?? '—';
                return `
                    <div class="flex items-start justify-between gap-4 py-2 border-b border-gray-100 last:border-b-0 ${isDifferent ? 'bg-blue-50/70 -mx-3 px-3 rounded-lg' : ''}">
                        <span class="text-xs font-medium ${isDifferent ? 'text-blue-700' : 'text-gray-500'} w-1/3">${escapeHtml(row.label)}</span>
                        <span class="text-xs ${isDifferent ? 'text-blue-900 font-semibold' : 'text-gray-700'} text-right flex-1">${formatSpecValue(value)}</span>
                    </div>
                `;
            }).join('');

            return `
                <article class="rounded-2xl border ${diffColumns[product.product_id] ? 'border-blue-200 ring-1 ring-blue-100' : 'border-gray-200'} bg-white shadow-sm overflow-hidden">
                    <div class="p-4 flex gap-3 items-start">
                        <img src="${product.thumbnail || ''}" alt="${escapeHtml(product.name)}" class="h-20 w-20 rounded-xl border border-gray-100 bg-gray-50 object-contain p-2 flex-shrink-0">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-gray-900 leading-6 line-clamp-2">${escapeHtml(product.name)}</h3>
                                <button type="button" class="text-red-500 hover:text-red-700 transition-transform hover:scale-110" data-remove-id="${product.product_id}">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <p class="mt-1 text-sm font-bold text-red-600">${formatCurrency(product.base_price)}</p>
                            <p class="mt-1 text-xs text-gray-500">${escapeHtml(product.category_name || '')}</p>
                            ${diffColumns[product.product_id] ? '<span class="mt-2 inline-flex rounded-full bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700">Có khác biệt</span>' : ''}
                        </div>
                    </div>
                    <div class="px-4 pb-4">
                        <div class="rounded-xl bg-gray-50 p-3">
                            ${specItems}
                        </div>
                    </div>
                </article>
            `;
        }).join('');

        // Lắng nghe sự kiện click nút Xóa trên giao diện Mobile
        mobileCardsEl.querySelectorAll('[data-remove-id]').forEach((btn) => {
            btn.addEventListener('click', () => removeCompareId(btn.dataset.removeId));
        });
    }

    /**
     * Hàm chính tải và render bảng so sánh sản phẩm chi tiết (Desktop)
     */
    async function loadComparePage() {
        if (!window.__COMPARE_PAGE__) return; // Chỉ chạy nếu đang ở đúng trang So sánh (/compare)

        const ids = getCompareIds();
        if (ids.length === 0) {
            emptyState?.classList.remove('hidden');
            tableWrap?.classList.add('hidden');
            return;
        }

        // Gọi API Backend để lấy ma trận thông tin so sánh của các ID sản phẩm
        const response = await fetch(`${PRODUCT_API}?ids=${ids.join(',')}`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await response.json();
        const products = data.products || [];
        const rows = data.comparison_data || [];

        if (!products.length) {
            emptyState?.classList.remove('hidden');
            tableWrap?.classList.add('hidden');
            return;
        }

        emptyState?.classList.add('hidden');
        tableWrap?.classList.remove('hidden');

        // Dựng phần Header của bảng so sánh (Chứa Tên, Ảnh, Giá, Danh mục của sản phẩm)
        headEl.innerHTML = `
            <tr>
                <th class="sticky left-0 bg-gray-50 p-4 text-left font-semibold text-gray-700 w-56">Thuộc tính</th>
                ${products.map((product) => `
                    <th class="p-4 text-left min-w-72 align-top ${products.length > 1 ? 'bg-gradient-to-b from-blue-50 to-white' : ''}">
                        <div class="space-y-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-transform duration-200 hover:-translate-y-0.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="font-semibold text-gray-900 leading-6">${escapeHtml(product.name)}</div>
                                <button type="button" class="text-red-500 hover:text-red-700 transition-transform hover:scale-110" data-remove-id="${product.product_id}">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <img src="${product.thumbnail || ''}" alt="${escapeHtml(product.name)}" class="h-32 w-full object-contain rounded-xl bg-gray-50 p-3 border border-gray-100">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-red-600 font-bold">${formatCurrency(product.base_price)}</div>
                                <span class="rounded-full bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700">${escapeHtml(product.category_name || '')}</span>
                            </div>
                        </div>
                    </th>
                `).join('')}
            </tr>
        `;

        // Tổ chức các thuộc tính cần so sánh (Các thông tin chung + Các thông số kỹ thuật đặc thù)
        const specKeys = new Map();
        rows.forEach((row) => specKeys.set(row.key, row.label));
        
        // Khai báo các dòng thuộc tính chung
        const generalRows = [
            { label: 'Tên sản phẩm', getter: (p, i) => escapeHtml(p.name), raw: (p) => p.name },
            { label: 'Giá hiện tại', getter: (p) => formatCurrency(p.base_price), raw: (p) => p.base_price },
            { label: 'Giá cũ', getter: (p) => formatCurrency(p.old_price), raw: (p) => p.old_price },
            { label: 'Giảm giá', getter: (p) => (p.discount_percent ? `${Number(p.discount_percent)}%` : '—'), raw: (p) => p.discount_percent },
            { label: 'Đánh giá', getter: (p) => (p.rating ? Number(p.rating).toFixed(1) : '—'), raw: (p) => p.rating },
            { label: 'Số review', getter: (p) => (p.review_count ?? '—'), raw: (p) => p.review_count },
            { label: 'Danh mục', getter: (p) => escapeHtml(p.category_name || '—'), raw: (p) => p.category_name },
        ];
        
        // Khai báo các dòng thông số kỹ thuật (được phân tích tự động từ JSON specifications ở backend)
        const specsRows = Array.from(specKeys.entries()).map(([key, label]) => ({
            label,
            getter: (p) => formatSpecValue(p.specifications?.[key]),
            raw: (p) => p.specifications?.[key],
            key,
        }));
        
        const allRows = [...generalRows, ...specsRows];
        const diffRows = new Set(rows.filter((row) => row.is_different).map((row) => row.label));

        // Dựng phần Body của bảng, tô màu nền xanh nhạt cho những hàng có sự khác biệt (diffRows)
        bodyEl.innerHTML = allRows.map((row) => `
            <tr class="${diffRows.has(row.label) ? 'bg-blue-50/60' : ''}">
                <th class="sticky left-0 bg-white p-4 text-left font-medium text-gray-700 align-top border-r border-gray-100 ${diffRows.has(row.label) ? 'text-blue-700' : ''}">${escapeHtml(row.label)}</th>
                ${products.map((product) => `
                    <td class="p-4 align-top text-gray-700 border-r border-gray-50 ${diffRows.has(row.label) ? 'bg-blue-50/30' : ''}">${row.getter(product)}</td>
                `).join('')}
            </tr>
        `).join('');

        // Gọi tiếp hàm render bảng ở dạng mobile
        renderComparePageMobile(products, rows);

        // Lắng nghe sự kiện click nút Xóa trên tiêu đề bảng Desktop
        bodyEl.querySelectorAll('[data-remove-id]').forEach((btn) => {
            btn.addEventListener('click', () => removeCompareId(btn.dataset.removeId));
        });
        headEl.querySelectorAll('[data-remove-id]').forEach((btn) => {
            btn.addEventListener('click', () => removeCompareId(btn.dataset.removeId));
        });
    }

    /**
     * Cập nhật trạng thái tổng thể của module so sánh
     */
    function renderCompareState() {
        renderFloatingBadge();
        renderCompareButtons();
    }

    /**
     * Nạp dữ liệu ban đầu từ Server khi tải trang (Nếu là user đã đăng nhập)
     * Giúp gộp danh sách so sánh đã lưu trên tài khoản vào LocalStorage hiện tại.
     */
    async function hydrateFromServer() {
        if (!isLoggedIn) return;
        try {
            const serverIds = Array.isArray(window.__SERVER_COMPARE_IDS__)
                ? window.__SERVER_COMPARE_IDS__.map((id) => Number(id)).filter(Boolean)
                : [];
            const localIds = getCompareIds();
            // Gộp danh sách từ server và local storage lại với nhau
            const merged = Array.from(new Set([...serverIds, ...localIds])).slice(0, MAX_ITEMS);
            if (merged.length) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(merged));
                await syncCompareIds(merged);
            }
            renderCompareState();
        } catch (e) {
            renderCompareState();
        }
    }

    // Đăng ký các hàm ra biến toàn cục window để gọi trực tiếp từ mã HTML Blade
    clearAllBtn?.addEventListener('click', clearCompare);
    window.addToCompare = addToCompare;
    window.removeFromCompare = removeCompareId;
    window.clearCompare = clearCompare;
    
    // Lắng nghe sự kiện khi lưới sản phẩm được thay đổi (ví dụ: do phân trang AJAX hoặc lọc sản phẩm)
    // để vẽ lại trạng thái các nút "So sánh"
    window.addEventListener('product-grid:updated', renderCompareState);

    // Khởi tạo hiển thị ban đầu
    renderCompareState();
    hydrateFromServer().then(() => loadComparePage());
});
