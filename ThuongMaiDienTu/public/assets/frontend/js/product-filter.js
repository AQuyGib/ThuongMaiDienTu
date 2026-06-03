/**
 * Hệ thống lọc sản phẩm động (Dynamic Product Filter System)
 * Xử lý phía giao diện người dùng (Client-side) dùng Vanilla JS và AJAX.
 * Chức năng: 
 *   - Lấy cấu hình bộ lọc động từ máy chủ dựa vào Category ID hiện tại.
 *   - Quản lý trạng thái bộ lọc (state).
 *   - Hiển thị pop-up lọc thông số (Giá, Hãng, RAM, Màu sắc, Nhu cầu...).
 *   - Tạo các nhãn bộ lọc đang kích hoạt để người dùng dễ theo dõi và xóa nhanh.
 *   - Gửi yêu cầu AJAX tải sản phẩm đã lọc và hiển thị hiệu ứng chờ (Skeleton Loading).
 *   - Đồng bộ trạng thái bộ lọc lên thanh địa chỉ (URL query strings) và xử lý phân trang AJAX.
 */
document.addEventListener('DOMContentLoaded', function () {
    // ----------------------------------------------------
    // LẤY CÁC THÀNH PHẦN GIAO DIỆN (DOM ELEMENTS) CẦN DÙNG
    // ----------------------------------------------------
    // Form ẩn chứa các input bộ lọc để submit lên server
    const filterForm = document.getElementById('filter-form');
    // Vùng chứa các thẻ input ẩn được tạo tự động bằng JavaScript
    const dynamicInputsContainer = filterForm ? filterForm.querySelector('#dynamic-filter-inputs') : null;
    // Container chứa các cửa sổ pop-up nhỏ khi nhấn vào nút lọc
    const popupsContainer = document.getElementById('filter-popups-container');
    // Vùng hiển thị danh sách các thẻ nhãn bộ lọc đang áp dụng (ví dụ: "Hãng: Apple x")
    const activeFiltersContainer = document.getElementById('active-filters');
    // Nút nhấn để xóa sạch toàn bộ các bộ lọc đang chọn
    const clearAllBtn = document.getElementById('clear-all-filters');
    // Thẻ hiển thị tổng số lượng sản phẩm lọc được (ví dụ: "Có 25 sản phẩm phù hợp")
    const productCountDisplay = document.getElementById('product-count');
    // Vùng hiển thị lưới sản phẩm
    const productListContainer = document.getElementById('product-list-container');

    // ----------------------------------------------------
    // KHỞI TẠO ĐỐI TƯỢNG QUẢN LÝ TRẠNG THÁI BỘ LỌC (STATE)
    // ----------------------------------------------------
    const state = {
        filters: {
            category_id: '',            // ID danh mục hiện tại (ví dụ: Laptop, Điện thoại)
            min_price: '',              // Khoảng giá tối thiểu khách nhập
            max_price: '',              // Khoảng giá tối đa khách nhập
            sort: 'newest',             // Thứ tự sắp xếp (mặc định là Mới nhất - newest)
            q: '',                      // Từ khóa tìm kiếm sản phẩm
            needs: [],                  // Danh sách nhu cầu (ví dụ: Chơi game, Học tập)
            brand: [],                  // Danh sách hãng sản xuất (ví dụ: Apple, Asus)
            ram: [],                    // Danh sách dung lượng RAM (ví dụ: 8GB, 16GB)
            color: [],                  // Danh sách màu sắc sản phẩm
            size: [],                   // Danh sách kích thước màn hình hoặc kích cỡ
            high_repairability: '',     // Tiêu chí: Dễ sửa chữa (Eco) - có giá trị '1' nếu bật
            eco_friendly: '',           // Tiêu chí: Thân thiện môi trường (Eco) - có giá trị '1' nếu bật
            in_stock: '',               // Tiêu chí: Chỉ xem hàng còn trong kho - có giá trị '1' nếu bật
            new_arrival: ''             // Tiêu chí: Chỉ xem hàng mới về - có giá trị '1' nếu bật
        },
        activePopup: null,              // Tên pop-up hiện đang mở (null nếu không có pop-up nào mở)
    };

    // Kiểm tra ngôn ngữ hiện tại của trang dựa vào thẻ <html lang="..."> (để hiển thị đa ngôn ngữ Việt/Anh)
    const isEn = document.documentElement.lang === 'en';

    // Cấu hình các bộ lọc mặc định cố định ở Client-side (Lọc giá và Nhu cầu)
    let filterConfigs = {
        price: {
            label: isEn ? 'Filter by price' : 'Xem theo giá',
            type: 'range',
            fields: [
                { label: isEn ? 'From' : 'Từ', name: 'min_price', placeholder: '0' },
                { label: isEn ? 'To' : 'Đến', name: 'max_price', placeholder: '∞' },
            ],
        },
        usage: {
            label: 'Nhu cầu sử dụng',
            type: 'checkbox',
            inputName: 'needs',
            options: [
                { label: '🎮 Chơi game', value: 'gaming' },
                { label: '🎓 Học tập / Văn phòng', value: 'student' },
            ],
        },
    };

    /**
     * Hàm init(): Khởi động hệ thống lọc sản phẩm.
     * Đọc các tham số bộ lọc từ địa chỉ URL hiện tại của trình duyệt, cập nhật vào đối tượng `state`,
     * sau đó tải danh mục bộ lọc động, đồng bộ giao diện và gán các sự kiện lắng nghe.
     */
    function init() {
        // Lấy các tham số query từ URL (ví dụ: ?category_id=2&brand=Apple)
        const params = new URLSearchParams(window.location.search);
        
        // Cập nhật giá trị từ URL hoặc từ biến khởi tạo có sẵn vào state
        state.filters.category_id = window.__INITIAL_CATEGORY_ID || params.get('category_id') || '';
        state.filters.min_price = params.get('min_price') || '';
        state.filters.max_price = params.get('max_price') || '';
        state.filters.sort = params.get('sort') || 'newest';
        state.filters.q = params.get('q') || '';
        state.filters.high_repairability = params.get('high_repairability') || '';
        state.filters.eco_friendly = params.get('eco_friendly') || '';
        state.filters.in_stock = params.get('in_stock') || '';
        state.filters.new_arrival = params.get('new_arrival') || '';
        
        // Đọc các bộ lọc dạng mảng (nhiều giá trị chọn cùng lúc)
        state.filters.needs = readArrayParam(params, 'needs');
        state.filters.brand = readArrayParam(params, 'brand');
        state.filters.ram = readArrayParam(params, 'ram');
        state.filters.color = readArrayParam(params, 'color');
        state.filters.size = readArrayParam(params, 'size');

        // Gửi yêu cầu lấy bộ lọc động (ví dụ: Ram, Dung lượng) dựa vào danh mục sản phẩm hiện tại
        loadDynamicFilters(state.filters.category_id);
        // Gán các sự kiện click, nhập dữ liệu cho các nút
        bindEvents();
        // Đồng bộ dữ liệu từ state vào các input ẩn trong form
        syncFormWithState();
        // Cập nhật các thẻ nhãn bộ lọc đang kích hoạt ra màn hình
        updateActiveFilters();
        // Cập nhật giao diện trạng thái hoạt động (active) cho các nút lọc nhanh
        syncQuickButtons();
    }

    /**
     * Đọc các tham số dạng mảng trên URL.
     * Hỗ trợ cả 2 định dạng:
     *   - Định dạng mảng: ?brand[]=Apple&brand[]=Samsung
     *   - Định dạng phân cách dấu phẩy: ?brand=Apple,Samsung
     */
    function readArrayParam(params, key) {
        const values = params.getAll(`${key}[]`);
        if (values.length) return values.filter(Boolean);
        const single = params.get(key);
        if (!single) return [];
        return single.split(',').map(v => v.trim()).filter(Boolean);
    }

    /**
     * Tải danh sách bộ lọc đặc thù của danh mục sản phẩm hiện tại từ API backend.
     * Ví dụ: Danh mục Laptop sẽ có bộ lọc RAM, ổ cứng; Điện thoại sẽ có bộ lọc Dung lượng pin...
     */
    function loadDynamicFilters(categoryId) {
        const container = document.getElementById('dynamic-filter-triggers');
        if (!container) return;

        // Nếu có categoryId cụ thể, gọi API của category đó, ngược lại gọi API chung (0)
        const url = categoryId ? `/api/categories/${categoryId}/filters` : '/api/categories/0/filters';
        fetch(url)
            .then(res => res.json())
            .then(cfg => {
                // Hợp nhất cấu hình bộ lọc mặc định và cấu hình động lấy từ API
                filterConfigs = { ...filterConfigs, ...(cfg || {}) };
                // Vẽ các nút bấm bộ lọc động ra thanh công cụ
                renderDynamicTriggers(container, cfg || {});
            })
            .catch(() => renderDynamicTriggers(container, {}));
    }

    /**
     * Tạo các nút bấm trigger trên thanh công cụ cho các bộ lọc động nhận được.
     */
    function renderDynamicTriggers(container, cfg) {
        container.innerHTML = '';
        Object.keys(cfg).forEach(key => {
            // Bỏ qua các cấu hình không cần tạo nút bấm pop-up riêng biệt
            if (['price', 'category', 'highlights'].includes(key)) return;
            
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'filter-trigger px-4 py-2 bg-gray-50 text-gray-600 rounded-xl font-medium text-sm hover:bg-white hover:text-red-600 hover:border-red-100 border border-transparent hover:shadow-sm transition-all duration-200 whitespace-nowrap';
            btn.dataset.filter = key;
            btn.textContent = cfg[key].label || key;
            
            // Khi nhấn nút, mở pop-up bộ lọc tương ứng
            btn.addEventListener('click', () => openPopup(key));
            container.appendChild(btn);
        });
    }

    /**
     * Gán các sự kiện lắng nghe tương tác từ phía người dùng.
     */
    function bindEvents() {
        // Lắng nghe sự kiện click trên các nút lọc chính
        document.querySelectorAll('.filter-trigger').forEach(btn => {
            const filterType = btn.dataset.filter;
            // Nút "Sẵn hàng" và "Mới về" là dạng toggle nhanh, click là chạy lọc ngay chứ không mở pop-up
            if (filterType === 'stock') {
                btn.addEventListener('click', () => toggleQuickFilter('in_stock', '1'));
            } else if (filterType === 'new') {
                btn.addEventListener('click', () => toggleQuickFilter('new_arrival', '1'));
            } else if (filterType === 'usage') {
                btn.addEventListener('click', () => openPopup('usage'));
            } else {
                btn.addEventListener('click', e => openPopup(e.currentTarget.dataset.filter));
            }
        });

        // Lắng nghe sự kiện click trên các nút sắp xếp sản phẩm (Mới nhất, Giá tăng, Giá giảm...)
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                state.filters.sort = e.currentTarget.dataset.sort || 'newest';
                setValue('filter-sort', state.filters.sort);
                fetchFilteredProducts();
            });
        });

        // Lắng nghe click các nút lọc nhanh có sẵn trực tiếp trên giao diện
        document.querySelectorAll('.quick-filter-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                toggleQuickFilter(e.currentTarget.dataset.name, e.currentTarget.dataset.value);
            });
        });

        // Nút xóa sạch toàn bộ các bộ lọc đang áp dụng
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', clearAll);
        }

        // Đóng pop-up đang mở nếu người dùng click ra ngoài khu vực pop-up và nút kích hoạt
        document.addEventListener('click', e => {
            if (state.activePopup && !e.target.closest('.filter-popup') && !e.target.closest('.filter-trigger')) {
                closePopup();
            }
        });
    }

    /**
     * Hàm toggleQuickFilter(): Bật hoặc tắt trạng thái của một tiêu chí lọc nhanh.
     * @param {string} name - Tên trường lọc (ví dụ: 'brand', 'in_stock')
     * @param {string} value - Giá trị cần lọc (ví dụ: 'Apple', '1')
     */
    function toggleQuickFilter(name, value) {
        if (['needs', 'brand', 'ram', 'color', 'size'].includes(name)) {
            // Đối với bộ lọc đa trị (mảng): Thêm vào mảng nếu chưa có, hoặc xóa khỏi mảng nếu đã tồn tại
            state.filters[name] = toggleArray(state.filters[name], value);
        } else {
            // Đối với bộ lọc đơn trị (chuỗi): Nếu giá trị đang chọn trùng với giá trị cũ thì tắt lọc (gán rỗng), ngược lại gán giá trị mới
            state.filters[name] = state.filters[name] === value ? '' : value;
        }

        // Cập nhật lại form ẩn, vẽ nhãn hoạt động, thay đổi màu sắc nút bấm và tải dữ liệu mới
        syncFormWithState();
        updateActiveFilters();
        syncQuickButtons();
        fetchFilteredProducts();
    }

    /**
     * Thêm hoặc xóa một phần tử ra khỏi mảng dữ liệu.
     */
    function toggleArray(values, value) {
        const list = Array.isArray(values) ? [...values] : [];
        return list.includes(value) ? list.filter(v => v !== value) : [...list, value];
    }

    /**
     * Mở một cửa sổ pop-up nhỏ chứa thông số lọc chi tiết ngay dưới nút kích hoạt.
     * @param {string} type - Tên cấu hình bộ lọc cần mở (ví dụ: 'price', 'brand', 'ram')
     */
    function openPopup(type) {
        if (!popupsContainer) return;
        closePopup(); // Đóng pop-up cũ đang mở nếu có
        state.activePopup = type;

        // Tìm phần tử HTML của nút bấm vừa click để định vị vị trí pop-up
        const trigger = document.querySelector(`[data-filter="${type}"]`);
        if (!trigger) return;

        // Tạo thẻ div chứa giao diện pop-up
        const popup = document.createElement('div');
        popup.className = 'filter-popup absolute z-50 bg-white shadow-2xl border border-gray-100 rounded-2xl p-5 ring-1 ring-black/5';
        // Đặt kích thước rộng hơn cho màn hình lớn hoặc chế độ lọc tổng hợp
        popup.style.width = type === 'filter' ? 'min(750px, calc(100vw - 32px))' : '340px';
        
        // Tính toán vị trí hiển thị pop-up ngay dưới nút bấm kích hoạt
        const rect = trigger.getBoundingClientRect();
        popup.style.top = `${rect.bottom + window.scrollY + 8}px`;
        popup.style.left = `${rect.left}px`;

        // Xác định tiêu đề hiển thị của pop-up
        let label = isEn ? 'Filter' : 'Bộ lọc';
        if (type !== 'filter' && filterConfigs[type]) {
            label = filterConfigs[type].label || type;
        }

        // Tạo header cho pop-up kèm nút đóng 'x'
        let html = `<div class="flex justify-between items-center mb-4"><h3 class="font-bold text-gray-900 text-lg">${label}</h3><button class="close-popup p-1.5 rounded-full text-gray-400 hover:bg-gray-100 text-xl font-bold">×</button></div>`;

        // Tạo phần thân của pop-up dựa trên loại cấu hình lọc
        if (type === 'filter') {
            html += renderAllFilters(); // Vẽ tất cả các bộ lọc chung
        } else if (filterConfigs[type] && filterConfigs[type].type === 'range') {
            html += renderPriceRange(); // Vẽ thanh nhập khoảng giá
        } else if (filterConfigs[type] && Array.isArray(filterConfigs[type].options)) {
            html += renderPills(type, filterConfigs[type]); // Vẽ các viên thuốc tròn (Pills) để lựa chọn thông số
        } else {
            html += `<div class="text-sm text-gray-500 py-4 text-center italic">${isEn ? 'This feature is being updated...' : 'Tính năng này đang được cập nhật...'}</div>`;
        }

        // Tạo footer chứa nút Đóng và nút Xem kết quả
        html += `<div class="flex gap-3 mt-5 pt-4 border-t border-gray-100"><button type="button" class="close-popup flex-1 px-4 py-2.5 text-sm font-medium text-gray-500 bg-gray-50 rounded-xl">${isEn ? 'Close' : 'Đóng'}</button><button type="button" class="apply-filter flex-1 px-4 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl">${isEn ? 'Apply' : 'Xem kết quả'}</button></div>`;
        
        popup.innerHTML = html;
        popupsContainer.appendChild(popup);

        // Gán sự kiện click nút đóng pop-up
        popup.querySelectorAll('.close-popup').forEach(btn => btn.addEventListener('click', closePopup));
        
        // Gán sự kiện click nút áp dụng lọc "Xem kết quả"
        popup.querySelector('.apply-filter')?.addEventListener('click', () => {
            syncFormWithState();
            updateActiveFilters();
            syncQuickButtons();
            fetchFilteredProducts();
            closePopup();
        });

        // Gán sự kiện chọn/bỏ chọn cho các viên thuốc pill thuộc tính
        popup.querySelectorAll('.filter-pill').forEach(pill => pill.addEventListener('click', () => toggleFilterPill(pill)));
        
        // Gán sự kiện cập nhật giá trị trực tiếp vào state khi người dùng gõ vào ô nhập giá tiền
        popup.querySelectorAll('.popup-price-input').forEach(input => input.addEventListener('input', e => { 
            state.filters[e.target.name] = e.target.value; 
        }));
    }

    /**
     * Vẽ giao diện hiển thị danh sách tất cả các bộ lọc tổng hợp.
     */
    function renderAllFilters() {
        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-h-[50vh] overflow-y-auto pr-2 scrollbar-thin">';
        Object.keys(filterConfigs).forEach(key => {
            const config = filterConfigs[key];
            if (!config || key === 'filter') return;

            html += `<div class="bg-gray-50/70 p-4 rounded-2xl border border-gray-100">
                <h4 class="font-bold text-gray-800 text-xs mb-3 uppercase tracking-wider">${config.label || key}</h4>`;

            if (config.type === 'range') {
                html += renderPriceRangeInline();
            } else if (Array.isArray(config.options)) {
                html += renderPillsInline(key, config);
            } else {
                html += '<p class="text-xs text-gray-400 italic">Không có tùy chọn</p>';
            }
            html += '</div>';
        });
        html += '</div>';
        return html;
    }

    /**
     * Vẽ ô nhập khoảng giá trực tiếp bên trong danh sách lọc tổng hợp.
     */
    function renderPriceRangeInline() {
        return `<div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Từ (đ)</label>
                <input type="number" name="min_price" value="${state.filters.min_price || ''}" placeholder="0" class="popup-price-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:border-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Đến (đ)</label>
                <input type="number" name="max_price" value="${state.filters.max_price || ''}" placeholder="∞" class="popup-price-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:border-blue-500 outline-none">
            </div>
        </div>`;
    }

    /**
     * Vẽ các nút pill lựa chọn thuộc tính hiển thị bên trong bộ lọc tổng hợp.
     */
    function renderPillsInline(key, config) {
        const stateKey = config.inputName || key;
        return `<div class="flex flex-wrap gap-1.5">
            ${(config.options || []).map(opt => {
                const label = typeof opt === 'object' ? opt.label : opt;
                const value = typeof opt === 'object' ? opt.value : opt;
                const selected = Array.isArray(state.filters[stateKey]) 
                    ? state.filters[stateKey].includes(value) 
                    : state.filters[stateKey] === value;
                return `<button type="button" class="filter-pill px-3 py-1.5 rounded-lg border text-xs transition-all duration-150 ${
                    selected 
                        ? 'border-blue-500 bg-blue-50 text-blue-600 font-semibold shadow-sm' 
                        : 'border-gray-200 bg-white text-gray-600 hover:border-blue-300 hover:bg-blue-50/30'
                }" data-key="${stateKey}" data-value="${value}">${label}</button>`;
            }).join('')}
        </div>`;
    }

    /**
     * Bật hoặc tắt trạng thái chọn của một nút pill thuộc tính trên pop-up và thay đổi class giao diện (CSS).
     */
    function toggleFilterPill(pill) {
        const key = pill.dataset.key;
        const value = pill.dataset.value;
        if (!Array.isArray(state.filters[key])) state.filters[key] = [];
        
        // Thay đổi giá trị mảng trong state
        state.filters[key] = toggleArray(state.filters[key], value);

        // Thay đổi trực quan CSS của nút bấm
        const active = state.filters[key].includes(value);
        if (active) {
            pill.classList.remove('border-gray-200', 'bg-white', 'text-gray-700', 'text-gray-600');
            pill.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-600', 'font-semibold');
        } else {
            pill.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-600', 'font-semibold');
            pill.classList.add('border-gray-200', 'bg-white', 'text-gray-600');
        }
    }

    /**
     * Vẽ các nút pill lựa chọn thuộc tính cho các pop-up riêng lẻ.
     */
    function renderPills(key, config) {
        const stateKey = config.inputName || key;
        return `<div class="flex flex-wrap gap-2">${(config.options || []).map(opt => {
            const label = typeof opt === 'object' ? opt.label : opt;
            const value = typeof opt === 'object' ? opt.value : opt;
            const selected = Array.isArray(state.filters[stateKey]) ? state.filters[stateKey].includes(value) : state.filters[stateKey] === value;
            return `<button type="button" class="filter-pill px-4 py-2 rounded-lg border text-sm ${
                selected 
                    ? 'border-blue-500 bg-blue-50 text-blue-600 font-semibold' 
                    : 'border-gray-200 bg-white text-gray-700 hover:border-blue-300 hover:bg-blue-50/30'
            }" data-key="${stateKey}" data-value="${value}">${label}</button>`;
        }).join('')}</div>`;
    }

    /**
     * Vẽ ô nhập khoảng giá tiền tối thiểu - tối đa cho pop-up Lọc Giá riêng biệt.
     */
    function renderPriceRange() {
        return `<div class="grid grid-cols-2 gap-3">${(filterConfigs.price.fields || []).map(field => `<div><label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">${field.label}</label><input type="number" name="${field.name}" value="${state.filters[field.name] || ''}" placeholder="${field.placeholder}" class="popup-price-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:border-blue-500 outline-none"></div>`).join('')}</div>`;
    }

    /**
     * Đóng và hủy bỏ thẻ HTML của cửa sổ pop-up đang hiển thị.
     */
    function closePopup() {
        const popup = popupsContainer ? popupsContainer.querySelector('.filter-popup') : null;
        if (popup) popup.remove();
        state.activePopup = null;
    }

    /**
     * Thiết lập giá trị cho một thẻ input dựa vào ID.
     */
    function setValue(id, value) {
        const input = document.getElementById(id);
        if (input) input.value = value;
    }

    /**
     * Đồng bộ toàn bộ dữ liệu từ đối tượng `state` vào các thẻ input ẩn trong form HTML.
     * Giúp hệ thống dễ dàng thu thập và gửi dữ liệu lên máy chủ thông qua AJAX.
     */
    function syncFormWithState() {
        setValue('filter-category-id', state.filters.category_id);
        setValue('filter-min-price', state.filters.min_price);
        setValue('filter-max-price', state.filters.max_price);
        setValue('filter-sort', state.filters.sort);
        setValue('filter-q', state.filters.q);

        if (!dynamicInputsContainer) return;
        // Xóa sạch vùng input ẩn cũ để tạo lại mới hoàn toàn tránh trùng lặp
        dynamicInputsContainer.innerHTML = '';

        // Tạo thẻ input ẩn cho các thuộc tính lọc dạng mảng (multi-select)
        ['brand', 'ram', 'color', 'size', 'needs'].forEach(key => {
            (state.filters[key] || []).forEach(v => appendHidden(`${key}[]`, v));
        });
        
        // Tạo thẻ input ẩn cho các thuộc tính lọc bật/tắt (toggle)
        ['high_repairability', 'eco_friendly', 'in_stock', 'new_arrival'].forEach(key => {
            if (state.filters[key]) appendHidden(key, state.filters[key]);
        });
    }

    /**
     * Tạo một thẻ <input type="hidden"> và chèn nó vào vùng container của form ẩn.
     */
    function appendHidden(name, value) {
        if (!dynamicInputsContainer) return;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        dynamicInputsContainer.appendChild(input);
    }

    /**
     * Cập nhật và hiển thị danh sách các thẻ nhãn bộ lọc đang hoạt động ở trên lưới sản phẩm.
     * Cho phép người dùng quan sát trực quan các điều kiện đang lọc và nhấn nút 'x' để xóa nhanh.
     */
    function updateActiveFilters() {
        if (!activeFiltersContainer) return;
        activeFiltersContainer.innerHTML = ''; // Làm sạch danh sách tag cũ
        const active = [];
        
        // Chỉ hiển thị nhãn danh mục nếu nó khác danh mục gốc của trang (danh mục cố định)
        if (state.filters.category_id && !window.__INITIAL_CATEGORY_ID) {
            active.push([isEn ? 'Category' : 'Danh mục', window.__INITIAL_CATEGORY_NAME || state.filters.category_id, 'category_id']);
        }
        // Hiển thị nhãn giá nếu có nhập min hoặc max
        if (state.filters.min_price || state.filters.max_price) {
            active.push([isEn ? 'Price' : 'Giá', `${state.filters.min_price || 0}đ - ${state.filters.max_price || '∞'}đ`, 'price']);
        }

        // Định nghĩa nhãn mô tả nhu cầu sử dụng
        const labels = isEn 
            ? { gaming: 'Play Genshin smoothly', student: 'Learn Web Dev' }
            : { gaming: 'Chơi mượt Genshin', student: 'Học Web Dev' };
            
        // Đẩy thông tin của các thuộc tính đang hoạt động vào mảng active
        state.filters.needs.forEach(v => active.push([isEn ? 'Usage needs' : 'Nhu cầu', labels[v] || v, 'needs', v]));
        state.filters.brand.forEach(v => active.push([isEn ? 'Manufacturer' : 'Hãng', v, 'brand', v]));
        state.filters.ram.forEach(v => active.push(['RAM', v, 'ram', v]));
        state.filters.color.forEach(v => active.push([isEn ? 'Color' : 'Màu', v, 'color', v]));
        state.filters.size.forEach(v => active.push(['Size', v, 'size', v]));
        
        if (state.filters.in_stock) active.push([isEn ? 'Stock' : 'Kho', isEn ? 'In Stock' : 'Sẵn hàng', 'in_stock']);
        if (state.filters.new_arrival) active.push([isEn ? 'New' : 'Mới', isEn ? 'New Arrivals' : 'Hàng mới về', 'new_arrival']);
        if (state.filters.high_repairability) active.push(['Eco', isEn ? 'Easy to repair' : 'Dễ sửa chữa', 'high_repairability']);
        if (state.filters.eco_friendly) active.push(['Eco', isEn ? 'Environmentally friendly' : 'Thân thiện môi trường', 'eco_friendly']);

        // Vẽ các thẻ HTML nhãn lọc ra màn hình
        active.forEach(item => {
            const tag = document.createElement('span');
            tag.className = 'px-3 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-semibold flex items-center gap-1.5 cursor-pointer hover:bg-red-100 border border-red-100';
            tag.innerHTML = `<span>${item[0]}: ${item[1]}</span><button class="w-4 h-4">×</button>`;
            
            // Lắng nghe sự kiện click nút 'x' trên nhãn để xóa nhanh điều kiện lọc đó
            tag.querySelector('button').addEventListener('click', e => {
                e.stopPropagation();
                removeFilter(item[2], item[3] ?? null);
            });
            activeFiltersContainer.appendChild(tag);
        });

        // Ẩn hoặc hiển thị nút "Xóa tất cả" tùy thuộc vào số lượng bộ lọc đang được kích hoạt
        if (clearAllBtn) {
            clearAllBtn.classList.toggle('hidden', active.length === 0);
            if (active.length) activeFiltersContainer.appendChild(clearAllBtn);
        }
    }

    /**
     * Xóa bỏ một điều kiện lọc cụ thể khi người dùng click nút 'x' trên thẻ nhãn.
     * @param {string} key - Tên tiêu chí lọc cần xóa
     * @param {string} value - Giá trị cần xóa (với bộ lọc dạng mảng)
     */
    function removeFilter(key, value) {
        if (['brand', 'ram', 'color', 'size', 'needs'].includes(key)) {
            // Đối với mảng, lọc bỏ giá trị muốn xóa
            state.filters[key] = state.filters[key].filter(v => v !== value);
        } else if (key === 'price') {
            // Xóa khoảng giá
            state.filters.min_price = '';
            state.filters.max_price = '';
        } else {
            // Reset các trường toggle khác về rỗng
            state.filters[key] = '';
        }
        
        // Đồng bộ lại form, giao diện, thanh công cụ và gửi AJAX tải lại danh sách sản phẩm
        syncFormWithState();
        updateActiveFilters();
        syncQuickButtons();
        fetchFilteredProducts();
    }

    /**
     * Đồng bộ hóa và đổi màu giao diện cho các nút lọc nhanh trên thanh công cụ.
     * Giúp nút bấm thay đổi màu sắc nổi bật khi nó đang hoạt động (active).
     */
    function syncQuickButtons() {
        document.querySelectorAll('.quick-filter-btn').forEach(btn => {
            const name = btn.dataset.name;
            const value = btn.dataset.value;
            const active = ['needs', 'brand', 'ram', 'color', 'size'].includes(name)
                ? (state.filters[name] || []).includes(value)
                : !!state.filters[name];

            if (active) {
                // Thay đổi class CSS sang màu xanh đậm nổi bật (khi được chọn)
                btn.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-200', 'hover:bg-blue-100', 'hover:text-blue-800');
                btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600', 'hover:bg-blue-700', 'hover:text-white');
            } else {
                // Trả về giao diện mặc định màu xám xanh (khi không được chọn)
                btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600', 'hover:bg-blue-700', 'hover:text-white');
                btn.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-200', 'hover:bg-blue-100', 'hover:text-blue-800');
            }
        });

        // Đồng bộ trạng thái cho các nút toggle nhanh (Sẵn hàng, Hàng mới về)
        const toggleMap = { stock: 'in_stock', new: 'new_arrival' };
        Object.entries(toggleMap).forEach(([filterKey, stateKey]) => {
            const btn = document.querySelector(`.filter-trigger[data-filter="${filterKey}"]`);
            if (!btn) return;
            if (state.filters[stateKey]) {
                // Đổi nút toggle sang màu xanh lá cây đậm (đang bật)
                btn.classList.remove('bg-gray-50', 'text-gray-600', 'border-transparent');
                btn.classList.add('bg-green-600', 'text-white', 'border-green-600', 'shadow-md');
            } else {
                // Đổi nút toggle về màu xám mặc định (đang tắt)
                btn.classList.remove('bg-green-600', 'text-white', 'border-green-600', 'shadow-md');
                btn.classList.add('bg-gray-50', 'text-gray-600', 'border-transparent');
            }
        });
    }

    /**
     * Xóa sạch tất cả các thông số lọc đang áp dụng và reset hệ thống về trạng thái ban đầu.
     */
    function clearAll() {
        state.filters = {
            category_id: state.filters.category_id, // Giữ lại ID danh mục hiện tại để không bị nhảy trang
            min_price: '',
            max_price: '',
            sort: 'newest',
            q: '',
            needs: [],
            brand: [],
            ram: [],
            color: [],
            size: [],
            high_repairability: '',
            eco_friendly: '',
            in_stock: '',
            new_arrival: ''
        };
        syncFormWithState();
        updateActiveFilters();
        syncQuickButtons();
        fetchFilteredProducts();
    }

    /**
     * Hàm fetchFilteredProducts(): Thu thập dữ liệu từ form, tạo chuỗi query parameters,
     * thay đổi địa chỉ URL trên thanh công cụ trình duyệt (mà không tải lại trang) và kích hoạt gửi AJAX.
     */
    function fetchFilteredProducts() {
        if (!filterForm) return;
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();
        
        // Đẩy các giá trị không trống vào params
        formData.forEach((value, key) => {
            if (value !== '') params.append(key, value);
        });

        const queryString = params.toString();
        const url = `/products/filter?${queryString}`;
        
        // Thay đổi URL trên thanh địa chỉ của trình duyệt nhằm lưu vết bộ lọc khi tải lại hoặc chia sẻ link
        window.history.pushState(null, '', `${window.location.pathname}${queryString ? `?${queryString}` : ''}`);
        // Kích hoạt request AJAX tải sản phẩm
        fetchProductsByUrl(url);
    }

    /**
     * Gửi request AJAX tải mã HTML danh sách sản phẩm đã được lọc trên server.
     * @param {string} url - Đường dẫn API kèm tham số bộ lọc
     */
    function fetchProductsByUrl(url) {
        // Hiển thị giao diện bộ xương giả lập (Skeleton Loader animation) trong thời gian chờ đợi phản hồi
        if (productListContainer) {
            productListContainer.innerHTML = `<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">${Array.from({ length: 8 }).map(() => `<div class="product-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 animate-pulse"><div class="h-48 bg-gray-200 w-full"></div><div class="p-4 space-y-3"><div class="h-3 bg-gray-200 rounded w-1/4"></div><div class="h-4 bg-gray-200 rounded w-3/4"></div><div class="h-4 bg-gray-200 rounded w-1/2"></div></div></div>`).join('')}</div>`;
        }

        // Thực hiện fetch với Header báo đây là request AJAX (XMLHttpRequest)
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                // Thay thế toàn bộ khung xương chờ bằng mã HTML sản phẩm thật nhận từ Server
                if (productListContainer) productListContainer.innerHTML = html;
                // Cập nhật lại tổng số lượng hiển thị
                updateCountFromMarkup(html);
                // Gán lại sự kiện click AJAX cho các nút chuyển trang phân trang mới
                attachPaginationEvents();
                // Kích hoạt event thông báo việc render danh sách sản phẩm hoàn tất để các module JS khác (ví dụ So sánh sản phẩm) cập nhật theo
                window.dispatchEvent(new CustomEvent('product-grid:updated'));
            })
            .catch(() => {
                if (productListContainer) {
                    productListContainer.innerHTML = `<p class="text-red-500 text-center py-10">${isEn ? 'An error occurred while loading products. Please try again.' : 'Có lỗi xảy ra khi tải sản phẩm. Vui lòng thử lại.'}</p>`;
                }
            });
    }

    /**
     * Phân tích mã HTML trả về để đọc số lượng tổng sản phẩm từ thuộc tính dataset và cập nhật nhãn số lượng.
     */
    function updateCountFromMarkup(html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const grid = doc.querySelector('[data-total-products]');
        const total = grid ? grid.dataset.totalProducts : doc.querySelectorAll('.product-card').length;
        if (productCountDisplay) productCountDisplay.textContent = total || '0';
    }

    /**
     * Gán sự kiện click AJAX cho các đường link phân trang (Pagination Links) bên trong phần lưới sản phẩm vừa được load.
     * Đảm bảo khi chuyển trang danh sách sản phẩm vẫn load mượt bằng AJAX và giữ nguyên bộ lọc.
     */
    function attachPaginationEvents() {
        if (!productListContainer) return;
        productListContainer.querySelectorAll('.ajax-pagination-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const url = e.currentTarget.getAttribute('href');
                if (url) fetchProductsByUrl(url); // Gửi AJAX load trang tiếp theo
            });
        });
    }

    // Chạy khởi tạo hệ thống lần đầu tiên khi DOM sẵn sàng
    init();
});
