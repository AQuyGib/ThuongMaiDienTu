document.addEventListener('DOMContentLoaded', function () {
    // State management for filters
    const state = {
        filters: {
            category_id: '',
            min_price: '',
            max_price: '',
            ram: [],
            rom: [],
            sort: 'newest',
            q: '',
            needs: [],
            high_repairability: '',
            eco_friendly: '',
            brand: []
        },
        activePopup: null
    };

    const filterForm = document.getElementById('filter-form');
    const dynamicInputsContainer = document.getElementById('dynamic-filter-inputs');
    const popupsContainer = document.getElementById('filter-popups-container');
    const activeFiltersContainer = document.getElementById('active-filters');
    const clearAllBtn = document.getElementById('clear-all-filters');
    const productCountDisplay = document.getElementById('product-count');
    const productListContainer = document.getElementById('product-list-container');

    // Filter configurations will be populated dynamically from the server
    let filterConfigs = {
        price: {
            label: 'Xem theo giá',
            type: 'range',
            fields: [
                { label: 'Từ', name: 'min_price', placeholder: '0' },
                { label: 'Đến', name: 'max_price', placeholder: '∞' }
            ]
        }
    };

    function init() {
        // Ưu tiên lấy category_id từ server (cho route slug-based), fallback URL params
        const urlParams = new URLSearchParams(window.location.search);
        state.filters.category_id = window.__INITIAL_CATEGORY_ID || urlParams.get('category_id') || '';

        loadDynamicFilters(state.filters.category_id);
        setupEventListeners();
        updateActiveFilters();
        attachPaginationEvents();
    }

    function loadDynamicFilters(categoryId) {
        const triggersContainer = document.getElementById('dynamic-filter-triggers');
        if (!triggersContainer) return;

        // Nếu không có category, có thể load filter mặc định (id = 0) hoặc bỏ qua
        const fetchUrl = categoryId ? `/api/categories/${categoryId}/filters` : '/api/categories/0/filters';

        fetch(fetchUrl)
            .then(res => res.json())
            .then(config => {
                // Giữ lại cấu hình tĩnh (như price, category) và merge cấu hình động
                filterConfigs = { ...filterConfigs, ...config };

                // Render filter triggers
                triggersContainer.innerHTML = '';
                Object.keys(config).forEach(key => {
                    if (key === 'price' || key === 'category' || key === 'highlights') return; // Bỏ qua metadata

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'filter-trigger px-4 py-2 bg-gray-50 text-gray-600 rounded-xl font-medium text-sm hover:bg-white hover:text-red-600 hover:border-red-100 border border-transparent hover:shadow-sm transition-all duration-200 whitespace-nowrap';
                    btn.dataset.filter = key;
                    btn.innerText = config[key].label;

                    btn.addEventListener('click', (e) => {
                        openFilterPopup(key);
                    });

                    triggersContainer.appendChild(btn);
                });
            })
            .catch(err => console.error("Error loading filters:", err));
    }

    function setupEventListeners() {
        // Filter trigger buttons
        document.querySelectorAll('.filter-trigger').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const filterType = e.currentTarget.dataset.filter;
                openFilterPopup(filterType);
            });
        });

        // Sort buttons
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const sortBtn = e.currentTarget;
                const sortValue = sortBtn.dataset.sort;
                state.filters.sort = sortValue;
                document.getElementById('filter-sort').value = sortValue;

                // Update active state of buttons
                document.querySelectorAll('.sort-btn').forEach(b => {
                    b.classList.remove('bg-red-600', 'text-white', 'border-red-600');
                    b.classList.add('bg-white', 'text-gray-600', 'border-gray-200');
                });
                sortBtn.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');
                sortBtn.classList.add('bg-red-600', 'text-white', 'border-red-600');

                fetchFilteredProducts();
            });
        });

        // Quick filter buttons (needs, eco, etc)
        document.querySelectorAll('.quick-filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const filterBtn = e.currentTarget;
                const name = filterBtn.dataset.name;
                const value = filterBtn.dataset.value;

                if (name === 'needs') {
                    if (state.filters.needs.includes(value)) {
                        state.filters.needs = state.filters.needs.filter(v => v !== value);
                        filterBtn.classList.remove('bg-blue-600', 'text-white');
                        filterBtn.classList.add('bg-blue-50', 'text-blue-700');
                    } else {
                        state.filters.needs.push(value);
                        filterBtn.classList.remove('bg-blue-50', 'text-blue-700');
                        filterBtn.classList.add('bg-blue-600', 'text-white');
                    }
                } else {
                    if (state.filters[name] === value) {
                        state.filters[name] = '';
                        filterBtn.classList.remove('bg-blue-600', 'text-white');
                        filterBtn.classList.add('bg-blue-50', 'text-blue-700');
                    } else {
                        state.filters[name] = value;
                        filterBtn.classList.remove('bg-blue-50', 'text-blue-700');
                        filterBtn.classList.add('bg-blue-600', 'text-white');
                    }
                }

                syncFormWithState();
                updateActiveFilters();
                fetchFilteredProducts();
            });
        });

        // Clear all filters
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', () => {
                state.filters = {
                    category_id: state.filters.category_id, // giữ lại category
                    min_price: '',
                    max_price: '',
                    ram: [],
                    rom: [],
                    sort: 'newest',
                    q: '',
                    needs: [],
                    high_repairability: '',
                    eco_friendly: '',
                    brand: []
                };

                // Reset all quick filter buttons UI
                document.querySelectorAll('.quick-filter-btn').forEach(btn => {
                    btn.classList.remove('bg-blue-600', 'text-white');
                    btn.classList.add('bg-blue-50', 'text-blue-700');
                });

                syncFormWithState();
                updateActiveFilters();
                fetchFilteredProducts();
            });
        }

        // Close popups when clicking outside
        document.addEventListener('click', (e) => {
            if (state.activePopup && !e.target.closest('.filter-popup') && !e.target.closest('.filter-trigger')) {
                closePopup();
            }
        });
    }

    // ==================== PILL-STYLE FILTER POPUP ====================

    /**
     * Render một nhóm pill options cho một filter key
     */
    function renderPillGroup(filterKey, config) {
        const options = config.options || [];
        if (!options.length) return '';

        let html = '<div class="flex flex-wrap gap-2">';
        options.forEach(opt => {
            const label = typeof opt === 'object' ? opt.label : opt;
            const value = typeof opt === 'object' ? opt.value : opt;

            // Kiểm tra trạng thái đã chọn
            let isSelected = false;
            if (Array.isArray(state.filters[filterKey])) {
                isSelected = state.filters[filterKey].includes(value);
            } else if (state.filters[filterKey] !== undefined) {
                isSelected = state.filters[filterKey] === value;
            }

            const selectedClass = isSelected
                ? 'border-red-500 bg-red-50 text-red-600 font-semibold'
                : 'border-gray-200 bg-white text-gray-700 hover:border-gray-400';

            html += `<button type="button" class="filter-pill px-4 py-2 rounded-lg border text-sm transition-all duration-200 cursor-pointer ${selectedClass}" data-key="${filterKey}" data-value="${value}">${label}</button>`;
        });
        html += '</div>';
        return html;
    }

    /**
     * Render phần giá (range inputs)
     */
    function renderPriceRange() {
        const config = filterConfigs.price;
        if (!config || config.type !== 'range') return '';

        let html = '<div class="grid grid-cols-2 gap-3">';
        config.fields.forEach(field => {
            html += `<div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">${field.label}</label>
                <input type="number" name="${field.name}" value="${state.filters[field.name] || ''}" 
                    placeholder="${field.placeholder}" 
                    class="popup-price-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-red-500 outline-none transition-all duration-200 hover:border-gray-300">
            </div>`;
        });
        html += '</div>';
        return html;
    }

    /**
     * Mở popup lọc - 2 chế độ: full panel (Bộ lọc) hoặc single filter
     */
    function openFilterPopup(type) {
        if (state.activePopup) closePopup();
        state.activePopup = type;

        const isFullPanel = (type === 'filter');
        const popup = document.createElement('div');

        // Style popup
        popup.className = 'filter-popup absolute z-50 bg-white shadow-2xl border border-gray-100 rounded-2xl p-5 ring-1 ring-black/5';
        if (isFullPanel) {
            popup.style.width = 'min(750px, calc(100vw - 32px))';
        } else {
            popup.style.width = '340px';
        }

        // Vị trí
        const triggerBtn = document.querySelector(`[data-filter="${type}"]`);
        if (!triggerBtn) return;
        const rect = triggerBtn.getBoundingClientRect();
        popup.style.top = `${rect.bottom + window.scrollY + 8}px`;
        popup.style.left = isFullPanel
            ? `${Math.max(16, rect.left)}px`
            : `${rect.left}px`;

        // Đảm bảo không tràn phải
        requestAnimationFrame(() => {
            const popupRect = popup.getBoundingClientRect();
            if (popupRect.right > window.innerWidth - 16) {
                popup.style.left = `${window.innerWidth - popupRect.width - 16}px`;
            }
        });

        let content = '';

        if (isFullPanel) {
            // ===== FULL PANEL: Hiển thị tất cả bộ lọc =====
            content += `<div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-gray-900 text-lg">Bộ lọc nâng cao</h3>
                <button class="close-popup p-1.5 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>`;

            content += '<div class="max-h-[450px] overflow-y-auto pr-1 space-y-5">';

            // Khoảng giá
            content += `<div>
                <h4 class="font-semibold text-gray-800 text-sm mb-3">Khoảng giá</h4>
                ${renderPriceRange()}
            </div>`;

            // Render tất cả filter configs có options
            const skipKeys = ['price', 'highlights'];
            Object.keys(filterConfigs).forEach(key => {
                if (skipKeys.includes(key)) return;
                const cfg = filterConfigs[key];
                if (cfg.options && cfg.options.length > 0) {
                    content += `<div>
                        <h4 class="font-semibold text-gray-800 text-sm mb-3">${cfg.label}</h4>
                        ${renderPillGroup(key, cfg)}
                    </div>`;
                }
            });

            content += '</div>';
        } else {
            // ===== SINGLE FILTER POPUP =====
            let config = filterConfigs[type];

            // Header
            const label = config ? config.label : type;
            content += `<div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-900 text-base">${label}</h3>
                <button class="close-popup p-1.5 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>`;

            if (config && config.type === 'range') {
                content += renderPriceRange();
            } else if (config && config.options && config.options.length > 0) {
                content += renderPillGroup(type, config);
            } else {
                content += '<div class="text-sm text-gray-500 py-4 text-center italic">Tính năng này đang được cập nhật...</div>';
            }
        }

        // Footer buttons
        content += `<div class="flex gap-3 mt-5 pt-4 border-t border-gray-100">
            <button type="button" class="close-popup flex-1 px-4 py-2.5 text-sm font-medium text-gray-500 bg-gray-50 rounded-xl hover:bg-gray-100 transition-all duration-200">Đóng</button>
            <button type="button" class="apply-filter flex-1 px-4 py-2.5 text-sm font-bold text-white bg-red-600 rounded-xl hover:bg-red-700 shadow-md shadow-red-200 transition-all duration-200 active:scale-95">Xem kết quả</button>
        </div>`;

        popup.innerHTML = content;
        popupsContainer.appendChild(popup);

        // ===== Gắn sự kiện cho pill buttons =====
        popup.querySelectorAll('.filter-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                const key = pill.dataset.key;
                const value = pill.dataset.value;

                // Khởi tạo mảng nếu chưa có
                if (state.filters[key] === undefined) {
                    state.filters[key] = [];
                }

                if (Array.isArray(state.filters[key])) {
                    if (state.filters[key].includes(value)) {
                        // Bỏ chọn
                        state.filters[key] = state.filters[key].filter(v => v !== value);
                        pill.classList.remove('border-red-500', 'bg-red-50', 'text-red-600', 'font-semibold');
                        pill.classList.add('border-gray-200', 'bg-white', 'text-gray-700');
                    } else {
                        // Chọn
                        state.filters[key].push(value);
                        pill.classList.remove('border-gray-200', 'bg-white', 'text-gray-700');
                        pill.classList.add('border-red-500', 'bg-red-50', 'text-red-600', 'font-semibold');
                    }
                } else {
                    // Toggle single value
                    if (state.filters[key] === value) {
                        state.filters[key] = '';
                        pill.classList.remove('border-red-500', 'bg-red-50', 'text-red-600', 'font-semibold');
                        pill.classList.add('border-gray-200', 'bg-white', 'text-gray-700');
                    } else {
                        // Bỏ chọn pill cũ trong cùng nhóm
                        popup.querySelectorAll(`.filter-pill[data-key="${key}"]`).forEach(p => {
                            p.classList.remove('border-red-500', 'bg-red-50', 'text-red-600', 'font-semibold');
                            p.classList.add('border-gray-200', 'bg-white', 'text-gray-700');
                        });
                        state.filters[key] = value;
                        pill.classList.remove('border-gray-200', 'bg-white', 'text-gray-700');
                        pill.classList.add('border-red-500', 'bg-red-50', 'text-red-600', 'font-semibold');
                    }
                }
            });
        });

        // ===== Gắn sự kiện cho price inputs =====
        popup.querySelectorAll('.popup-price-input').forEach(input => {
            input.addEventListener('input', () => {
                state.filters[input.name] = input.value;
            });
        });

        // ===== Close & Apply buttons =====
        popup.querySelectorAll('.close-popup').forEach(btn => btn.addEventListener('click', closePopup));
        popup.querySelector('.apply-filter').addEventListener('click', () => {
            syncFormWithState();
            updateActiveFilters();
            fetchFilteredProducts();
            closePopup();
        });
    }

    function closePopup() {
        if (state.activePopup) {
            const popup = popupsContainer.querySelector('.filter-popup');
            if (popup) popup.remove();
            state.activePopup = null;
        }
    }

    function syncFormWithState() {
        document.getElementById('filter-category-id').value = state.filters.category_id;
        document.getElementById('filter-min-price').value = state.filters.min_price;
        document.getElementById('filter-max-price').value = state.filters.max_price;
        document.getElementById('filter-sort').value = state.filters.sort;
        document.getElementById('filter-q').value = state.filters.q;

        dynamicInputsContainer.innerHTML = '';
        Object.keys(state.filters).forEach(key => {
            // Loại trừ các key mặc định không phải là filter động
            const excludedKeys = ['category_id', 'min_price', 'max_price', 'sort', 'q', 'needs', 'high_repairability', 'eco_friendly'];

            if (!excludedKeys.includes(key) && Array.isArray(state.filters[key])) {
                state.filters[key].forEach(val => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `${key}[]`;
                    input.value = val;
                    dynamicInputsContainer.appendChild(input);
                });
            }
        });

        // Add needs arrays explicitly
        if (state.filters.needs && state.filters.needs.length > 0) {
            state.filters.needs.forEach(val => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'needs[]';
                input.value = val;
                dynamicInputsContainer.appendChild(input);
            });
        }

        const quickFilterContainer = document.getElementById('quick-filter-inputs');
        if (quickFilterContainer) {
            quickFilterContainer.innerHTML = '';
            ['high_repairability', 'eco_friendly'].forEach(key => {
                if (state.filters[key]) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = state.filters[key];
                    quickFilterContainer.appendChild(input);
                }
            });
        }
    }

    function updateActiveFilters() {
        activeFiltersContainer.innerHTML = '';
        const active = [];

        if (state.filters.category_id) {
            const catName = (state.filters.category_id == window.__INITIAL_CATEGORY_ID) ? window.__INITIAL_CATEGORY_NAME : state.filters.category_id;
            active.push({ label: 'Danh mục', value: catName, key: 'category_id' });
        }
        if (state.filters.min_price || state.filters.max_price) {
            active.push({
                label: 'Giá',
                value: `${state.filters.min_price || 0}đ - ${state.filters.max_price || '∞'}đ`,
                key: 'price'
            });
        }
        // Hiển thị active filter tags cho các thông số động
        Object.keys(state.filters).forEach(key => {
            const excludedKeys = ['category_id', 'min_price', 'max_price', 'sort', 'q', 'needs', 'high_repairability', 'eco_friendly'];

            if (!excludedKeys.includes(key) && Array.isArray(state.filters[key])) {
                state.filters[key].forEach(val => {
                    // Lấy label từ filterConfigs nếu có
                    const label = filterConfigs[key] ? filterConfigs[key].label : key.toUpperCase();
                    active.push({ label: label, value: val, key: key, val: val });
                });
            }
        });

        if (state.filters.needs && Array.isArray(state.filters.needs)) {
            state.filters.needs.forEach(v => {
                const labels = { 'gaming': 'Chơi mượt Genshin', 'student': 'Học Web Dev' };
                active.push({ label: 'Nhu cầu', value: labels[v] || v, key: 'needs', val: v });
            });
        }
        if (state.filters.high_repairability) active.push({ label: 'Eco', value: 'Dễ sửa chữa', key: 'high_repairability' });
        if (state.filters.eco_friendly) active.push({ label: 'Eco', value: 'Thân thiện môi trường', key: 'eco_friendly' });

        active.forEach(item => {
            const tag = document.createElement('span');
            tag.className = 'px-3 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-semibold flex items-center gap-1.5 cursor-pointer hover:bg-red-100 border border-red-100 transition-all duration-200 shadow-sm';
            tag.innerHTML = `<span>${item.label}: ${item.value}</span> <button class="w-4 h-4 flex items-center justify-center rounded-full bg-red-200 hover:bg-red-300 text-red-700 transition-colors">×</button>`;

            tag.querySelector('button').addEventListener('click', (e) => {
                e.stopPropagation();
                removeFilter(item.key, item.val);
            });
            activeFiltersContainer.appendChild(tag);
        });

        if (active.length > 0) {
            if (clearAllBtn) {
                clearAllBtn.classList.remove('hidden');
                activeFiltersContainer.appendChild(clearAllBtn);
            }
        } else {
            if (clearAllBtn) clearAllBtn.classList.add('hidden');
        }
    }

    function removeFilter(key, val) {
        if (Array.isArray(state.filters[key])) {
            state.filters[key] = state.filters[key].filter(v => v !== val);

            // Also update quick filter UI buttons
            if (key === 'needs') {
                const btn = document.querySelector(`.quick-filter-btn[data-name="needs"][data-value="${val}"]`);
                if (btn) {
                    btn.classList.remove('bg-blue-600', 'text-white');
                    btn.classList.add('bg-blue-50', 'text-blue-700');
                }
            }
        } else if (key === 'price') {
            state.filters.min_price = '';
            state.filters.max_price = '';
        } else {
            state.filters[key] = '';

            // Update UI buttons for eco (now using blue theme)
            const btn = document.querySelector(`.quick-filter-btn[data-name="${key}"]`);
            if (btn) {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-blue-50', 'text-blue-700');
            }
        }
        syncFormWithState();
        updateActiveFilters();
        fetchFilteredProducts();
    }

    function fetchFilteredProducts() {
        const form = document.getElementById('filter-form');
        if (!form) return;

        const formData = new FormData(form);
        const queryString = new URLSearchParams(formData).toString();
        const url = `/products/filter?${queryString}`;

        // 1. Cập nhật URL (History API)
        window.history.pushState(null, '', '?' + queryString);

        fetchProductsByUrl(url);
    }

    function fetchProductsByUrl(url) {
        if (productListContainer) {
            // 2. Skeleton Loading
            let skeletons = '';
            for (let i = 0; i < 8; i++) {
                skeletons += `
                <div class="product-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 animate-pulse">
                    <div class="h-48 bg-gray-200 w-full"></div>
                    <div class="p-4 space-y-3">
                        <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div class="flex gap-2">
                            <div class="h-6 bg-gray-200 rounded w-12"></div>
                            <div class="h-6 bg-gray-200 rounded w-12"></div>
                        </div>
                        <div class="h-6 bg-gray-200 rounded w-1/3"></div>
                        <div class="h-8 bg-gray-200 rounded w-full mt-4"></div>
                    </div>
                </div>`;
            }
            productListContainer.innerHTML = `<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">${skeletons}</div>`;
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.text())
            .then(html => {
                if (productListContainer) productListContainer.innerHTML = html;
                updateProductCount(html);
                attachPaginationEvents();
            })
            .catch(error => {
                console.error('Lỗi khi lọc:', error);
                if (productListContainer) {
                    productListContainer.innerHTML = '<p class="text-red-500 text-center py-10">Có lỗi xảy ra khi tải sản phẩm. Vui lòng thử lại.</p>';
                }
            });
    }

    function updateProductCount(html) {
        if (!productCountDisplay) return;
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const products = doc.querySelectorAll('.product-card').length;
        productCountDisplay.innerText = products;
    }

    function attachPaginationEvents() {
        if (!productListContainer) return;
        
        const paginationLinks = productListContainer.querySelectorAll('.ajax-pagination-link');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                if (url) {
                    // Update URL and fetch products
                    window.history.pushState(null, '', url);
                    
                    // Parse query params to update state
                    const urlParams = new URLSearchParams(url.split('?')[1]);
                    
                    // Keep track of current page in history if needed, but fetchProductsByUrl handles rendering
                    fetchProductsByUrl(url);
                    
                    // Scroll to top of product list
                    const containerTop = document.getElementById('filter-form').getBoundingClientRect().top + window.scrollY - 100;
                    window.scrollTo({ top: containerTop, behavior: 'smooth' });
                }
            });
        });
    }

    init();
});
