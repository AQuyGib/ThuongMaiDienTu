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
            q: ''
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

    // Filter configurations for popups
    const filterConfigs = {
        ram: {
            label: 'Dung lượng RAM',
            type: 'checkbox',
            options: ['8GB', '16GB', '32GB', '64GB'],
            inputName: 'ram[]'
        },
        rom: {
            label: 'Ổ cứng (ROM)',
            type: 'checkbox',
            options: ['128GB', '256GB', '512GB', '1TB'],
            inputName: 'rom[]'
        },
        price: {
            label: 'Khoảng giá',
            type: 'range',
            fields: [
                { label: 'Từ', name: 'min_price', placeholder: '0' },
                { label: 'Đến', name: 'max_price', placeholder: '∞' }
            ]
        },
        category: {
            label: 'Danh mục',
            type: 'select',
            options: [] // Will be populated from server or initial page load
        }
    };

    function init() {
        setupEventListeners();
        updateActiveFilters();
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
        ['ram', 'rom'].forEach(key => {
            state.filters[key].forEach(val => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key === 'ram' ? 'ram[]' : 'rom[]';
                input.value = val;
                dynamicInputsContainer.appendChild(input);
            });
        });
    }

    function updateActiveFilters() {
        activeFiltersContainer.innerHTML = '';
        const active = [];

        if (state.filters.category_id) active.push({ label: 'Danh mục', value: state.filters.category_id, key: 'category_id' });
        if (state.filters.min_price || state.filters.max_price) {
            active.push({ 
                label: 'Giá', 
                value: `${state.filters.min_price || 0}đ - ${state.filters.max_price || '∞'}đ`, 
                key: 'price' 
            });
        }
        state.filters.ram.forEach(v => active.push({ label: 'RAM', value: v, key: 'ram', val: v }));
        state.filters.rom.forEach(v => active.push({ label: 'ROM', value: v, key: 'rom', val: v }));

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
        } else if (key === 'price') {
            state.filters.min_price = '';
            state.filters.max_price = '';
        } else {
            state.filters[key] = '';
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
        
        fetchProductsByUrl(url);
    }

    function fetchProductsByUrl(url) {
        if (productListContainer) {
            productListContainer.innerHTML = `
                <div class="flex justify-center items-center py-20">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-10 h-10 border-4 border-red-600 border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-gray-500 animate-pulse">Đang cập nhật sản phẩm...</p>
                    </div>
                </div>`;
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
