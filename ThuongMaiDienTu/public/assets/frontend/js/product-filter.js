document.addEventListener('DOMContentLoaded', function() {
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
            eco_friendly: ''
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
            label: 'Khoảng giá',
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
                const filterType = e.target.dataset.filter;
                openFilterPopup(filterType);
            });
        });

        // Sort buttons
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const sortValue = e.target.dataset.sort;
                state.filters.sort = sortValue;
                document.getElementById('filter-sort').value = sortValue;
                
                // Update active state of buttons
                document.querySelectorAll('.sort-btn').forEach(b => {
                    b.classList.remove('bg-red-600', 'text-white', 'border-red-600');
                    b.classList.add('bg-white', 'text-gray-600', 'border-gray-200');
                });
                e.target.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');
                e.target.classList.add('bg-red-600', 'text-white', 'border-red-600');
                
                fetchFilteredProducts();
            });
        });

        // Quick filter buttons (needs, eco, etc)
        document.querySelectorAll('.quick-filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const name = e.target.dataset.name;
                const value = e.target.dataset.value;
                
                if (name === 'needs') {
                    if (state.filters.needs.includes(value)) {
                        state.filters.needs = state.filters.needs.filter(v => v !== value);
                        e.target.classList.remove('bg-blue-600', 'text-white');
                        e.target.classList.add('bg-blue-50', 'text-blue-700');
                    } else {
                        state.filters.needs.push(value);
                        e.target.classList.remove('bg-blue-50', 'text-blue-700');
                        e.target.classList.add('bg-blue-600', 'text-white');
                    }
                } else {
                    if (state.filters[name] === value) {
                        state.filters[name] = '';
                        e.target.classList.remove('bg-green-600', 'text-white');
                        e.target.classList.add('bg-green-50', 'text-green-700');
                    } else {
                        state.filters[name] = value;
                        e.target.classList.remove('bg-green-50', 'text-green-700');
                        e.target.classList.add('bg-green-600', 'text-white');
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
                    category_id: '',
                    min_price: '',
                    max_price: '',
                    ram: [],
                    rom: [],
                    sort: 'newest',
                    q: ''
                };
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

    function openFilterPopup(type) {
        if (state.activePopup) closePopup();

        let config = filterConfigs[type];
        if (!config) {
            config = { label: type.toUpperCase(), type: 'placeholder', options: ['Tùy chọn 1', 'Tùy chọn 2'] };
        }

        state.activePopup = type;

        const popup = document.createElement('div');
        popup.className = 'filter-popup absolute z-50 bg-white shadow-2xl border border-gray-100 rounded-2xl p-5 w-80 animate-in fade-in zoom-in duration-200 ring-1 ring-black/5';
        
        const triggerBtn = document.querySelector(`[data-filter="${type}"]`);
        const rect = triggerBtn.getBoundingClientRect();
        popup.style.top = `${rect.bottom + window.scrollY + 8}px`;
        popup.style.left = `${rect.left}px`;

        let content = `<div class="flex justify-between items-center mb-5">
            <h3 class="font-bold text-gray-900 text-base">${config.label}</h3>
            <button class="close-popup p-1 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>`;

        if (config.type === 'checkbox') {
            content += `<div class="space-y-2 max-h-60 overflow-y-auto mb-4">`;
            config.options.forEach(opt => {
                const isChecked = state.filters[type]?.includes(opt) ? 'checked' : '';
                content += `
                    <label class="flex items-center gap-3 p-2.5 hover:bg-red-50 rounded-xl cursor-pointer transition-all duration-200 group">
                        <input type="checkbox" name="${config.inputName}" value="${opt}" ${isChecked} 
                                class="popup-checkbox w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer">
                        <span class="text-sm text-gray-600 group-hover:text-red-700 font-medium transition-colors">${opt}</span>
                    </label>`;
            });
            content += `</div>`;
        } else if (config.type === 'range') {
            content += `<div class="grid grid-cols-2 gap-3 mb-4">`;
            config.fields.forEach(field => {
                content += `
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">${field.label}</label>
                        <input type="number" name="${field.name}" value="${state.filters[field.name] || ''}" 
                                placeholder="${field.placeholder}" 
                                class="popup-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-red-500 outline-none transition-all duration-200 hover:border-gray-300">
                    </div>`;
            });
            content += `</div>`;
        } else if (config.type === 'select') {
            content += `<select name="category_id" class="popup-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm mb-4 outline-none focus:ring-2 focus:ring-red-500">
                <option value="">Tất cả danh mục</option>
                ${config.options.map(opt => `<option value="${opt.id}" ${state.filters.category_id == opt.id ? 'selected' : ''}>${opt.name}</option>`).join('')}
            </select>`;
        } else {
            content += `<div class="text-sm text-gray-500 mb-4 italic">Tính năng này đang được cập nhật...</div>`;
        }

        content += `
            <div class="flex gap-3 mt-6 pt-4 border-t border-gray-100">
                <button type="button" class="close-popup flex-1 px-4 py-2.5 text-sm font-medium text-gray-500 bg-gray-50 rounded-xl hover:bg-gray-100 transition-all duration-200">Đóng</button>
                <button type="button" class="apply-filter flex-1 px-4 py-2.5 text-sm font-bold text-white bg-red-600 rounded-xl hover:bg-red-700 shadow-md shadow-red-200 transition-all duration-200 active:scale-95">Xem kết quả</button>
            </div>`;

        popup.innerHTML = content;
        popupsContainer.appendChild(popup);

        popup.querySelector('.close-popup').addEventListener('click', closePopup);
        popup.querySelector('.apply-filter').addEventListener('click', () => {
            applyPopupFilters(type, popup);
        });
    }

    function closePopup() {
        if (state.activePopup) {
            const popup = popupsContainer.querySelector('.filter-popup');
            if (popup) popup.remove();
            state.activePopup = null;
        }
    }

    function applyPopupFilters(type, popup) {
        const inputs = popup.querySelectorAll('input, select');
        
        inputs.forEach(input => {
            if (input.type === 'checkbox') {
                if (!state.filters[type]) state.filters[type] = [];
                if (input.checked) {
                    if (!state.filters[type].includes(input.value)) state.filters[type].push(input.value);
                } else {
                    state.filters[type] = state.filters[type].filter(v => v !== input.value);
                }
            } else {
                state.filters[input.name] = input.value;
            }
        });

        syncFormWithState();
        updateActiveFilters();
        fetchFilteredProducts();
        closePopup();
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
                const labels = {'gaming': 'Chơi mượt Genshin', 'student': 'Học Web Dev'};
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
            
            // Update UI buttons for eco
            const btn = document.querySelector(`.quick-filter-btn[data-name="${key}"]`);
            if (btn) {
                btn.classList.remove('bg-green-600', 'text-white');
                btn.classList.add('bg-green-50', 'text-green-700');
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
            for(let i=0; i<8; i++) {
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

    init();
});
