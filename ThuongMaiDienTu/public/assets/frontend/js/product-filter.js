document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filter-form');
    const dynamicInputsContainer = filterForm ? filterForm.querySelector('#dynamic-filter-inputs') : null;
    const popupsContainer = document.getElementById('filter-popups-container');
    const activeFiltersContainer = document.getElementById('active-filters');
    const clearAllBtn = document.getElementById('clear-all-filters');
    const productCountDisplay = document.getElementById('product-count');
    const productListContainer = document.getElementById('product-list-container');

    const state = {
        filters: {
            category_id: '',
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
        },
        activePopup: null,
    };

    let filterConfigs = {
        price: {
            label: 'Xem theo giá',
            type: 'range',
            fields: [
                { label: 'Từ', name: 'min_price', placeholder: '0' },
                { label: 'Đến', name: 'max_price', placeholder: '∞' },
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

    function init() {
        const params = new URLSearchParams(window.location.search);
        state.filters.category_id = window.__INITIAL_CATEGORY_ID || params.get('category_id') || '';
        state.filters.min_price = params.get('min_price') || '';
        state.filters.max_price = params.get('max_price') || '';
        state.filters.sort = params.get('sort') || 'newest';
        state.filters.q = params.get('q') || '';
        state.filters.high_repairability = params.get('high_repairability') || '';
        state.filters.eco_friendly = params.get('eco_friendly') || '';
        state.filters.in_stock = params.get('in_stock') || '';
        state.filters.new_arrival = params.get('new_arrival') || '';
        state.filters.needs = readArrayParam(params, 'needs');
        state.filters.brand = readArrayParam(params, 'brand');
        state.filters.ram = readArrayParam(params, 'ram');
        state.filters.color = readArrayParam(params, 'color');
        state.filters.size = readArrayParam(params, 'size');

        loadDynamicFilters(state.filters.category_id);
        bindEvents();
        syncFormWithState();
        updateActiveFilters();
        syncQuickButtons();
    }

    function readArrayParam(params, key) {
        const values = params.getAll(`${key}[]`);
        if (values.length) return values.filter(Boolean);
        const single = params.get(key);
        if (!single) return [];
        return single.split(',').map(v => v.trim()).filter(Boolean);
    }

    function loadDynamicFilters(categoryId) {
        const container = document.getElementById('dynamic-filter-triggers');
        if (!container) return;

        const url = categoryId ? `/api/categories/${categoryId}/filters` : '/api/categories/0/filters';
        fetch(url)
            .then(res => res.json())
            .then(cfg => {
                filterConfigs = { ...filterConfigs, ...(cfg || {}) };
                renderDynamicTriggers(container, cfg || {});
            })
            .catch(() => renderDynamicTriggers(container, {}));
    }

    function renderDynamicTriggers(container, cfg) {
        container.innerHTML = '';
        Object.keys(cfg).forEach(key => {
            if (['price', 'category', 'highlights'].includes(key)) return;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'filter-trigger px-4 py-2 bg-gray-50 text-gray-600 rounded-xl font-medium text-sm hover:bg-white hover:text-red-600 hover:border-red-100 border border-transparent hover:shadow-sm transition-all duration-200 whitespace-nowrap';
            btn.dataset.filter = key;
            btn.textContent = cfg[key].label || key;
            btn.addEventListener('click', () => openPopup(key));
            container.appendChild(btn);
        });
    }

    function bindEvents() {
        document.querySelectorAll('.filter-trigger').forEach(btn => {
            const filterType = btn.dataset.filter;
            // stock và new là toggle, không mở popup
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

        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                state.filters.sort = e.currentTarget.dataset.sort || 'newest';
                setValue('filter-sort', state.filters.sort);
                fetchFilteredProducts();
            });
        });

        document.querySelectorAll('.quick-filter-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                toggleQuickFilter(e.currentTarget.dataset.name, e.currentTarget.dataset.value);
            });
        });

        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', clearAll);
        }

        document.addEventListener('click', e => {
            if (state.activePopup && !e.target.closest('.filter-popup') && !e.target.closest('.filter-trigger')) {
                closePopup();
            }
        });
    }

    function toggleQuickFilter(name, value) {
        if (['needs', 'brand', 'ram', 'color', 'size'].includes(name)) {
            state.filters[name] = toggleArray(state.filters[name], value);
        } else {
            state.filters[name] = state.filters[name] === value ? '' : value;
        }

        syncFormWithState();
        updateActiveFilters();
        syncQuickButtons();
        fetchFilteredProducts();
    }

    function toggleArray(values, value) {
        const list = Array.isArray(values) ? [...values] : [];
        return list.includes(value) ? list.filter(v => v !== value) : [...list, value];
    }

    function openPopup(type) {
        if (!popupsContainer) return;
        closePopup();
        state.activePopup = type;

        const trigger = document.querySelector(`[data-filter="${type}"]`);
        if (!trigger) return;

        const popup = document.createElement('div');
        popup.className = 'filter-popup absolute z-50 bg-white shadow-2xl border border-gray-100 rounded-2xl p-5 ring-1 ring-black/5';
        popup.style.width = type === 'filter' ? 'min(750px, calc(100vw - 32px))' : '340px';
        const rect = trigger.getBoundingClientRect();
        popup.style.top = `${rect.bottom + window.scrollY + 8}px`;
        popup.style.left = `${rect.left}px`;

        let label = 'Bộ lọc';
        if (type !== 'filter' && filterConfigs[type]) {
            label = filterConfigs[type].label || type;
        }

        let html = `<div class="flex justify-between items-center mb-4"><h3 class="font-bold text-gray-900 text-lg">${label}</h3><button class="close-popup p-1.5 rounded-full text-gray-400 hover:bg-gray-100 text-xl font-bold">×</button></div>`;

        if (type === 'filter') {
            html += renderAllFilters();
        } else if (filterConfigs[type] && filterConfigs[type].type === 'range') {
            html += renderPriceRange();
        } else if (filterConfigs[type] && Array.isArray(filterConfigs[type].options)) {
            html += renderPills(type, filterConfigs[type]);
        } else {
            html += '<div class="text-sm text-gray-500 py-4 text-center italic">Tính năng này đang được cập nhật...</div>';
        }

        html += '<div class="flex gap-3 mt-5 pt-4 border-t border-gray-100"><button type="button" class="close-popup flex-1 px-4 py-2.5 text-sm font-medium text-gray-500 bg-gray-50 rounded-xl">Đóng</button><button type="button" class="apply-filter flex-1 px-4 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Xem kết quả</button></div>';
        popup.innerHTML = html;
        popupsContainer.appendChild(popup);

        popup.querySelectorAll('.close-popup').forEach(btn => btn.addEventListener('click', closePopup));
        popup.querySelector('.apply-filter')?.addEventListener('click', () => {
            syncFormWithState();
            updateActiveFilters();
            syncQuickButtons();
            fetchFilteredProducts();
            closePopup();
        });
        popup.querySelectorAll('.filter-pill').forEach(pill => pill.addEventListener('click', () => toggleFilterPill(pill)));
        popup.querySelectorAll('.popup-price-input').forEach(input => input.addEventListener('input', e => { state.filters[e.target.name] = e.target.value; }));
    }

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

    function renderPillsInline(key, config) {
        const stateKey = config.inputName || key;
        return `<div class="flex flex-wrap gap-1.5">
            ${(config.options || []).map(opt => {
                const label = typeof opt === 'object' ? opt.label : opt;
                const value = typeof opt === 'object' ? opt.value : opt;
                const selected = Array.isArray(state.filters[stateKey]) ? state.filters[stateKey].includes(value) : state.filters[stateKey] === value;
                return `<button type="button" class="filter-pill px-3 py-1.5 rounded-lg border text-xs transition-all duration-150 ${
                    selected 
                        ? 'border-blue-500 bg-blue-50 text-blue-600 font-semibold shadow-sm' 
                        : 'border-gray-200 bg-white text-gray-600 hover:border-blue-300 hover:bg-blue-50/30'
                }" data-key="${stateKey}" data-value="${value}">${label}</button>`;
            }).join('')}
        </div>`;
    }

    function toggleFilterPill(pill) {
        const key = pill.dataset.key;
        const value = pill.dataset.value;
        if (!Array.isArray(state.filters[key])) state.filters[key] = [];
        state.filters[key] = toggleArray(state.filters[key], value);

        const active = state.filters[key].includes(value);
        if (active) {
            pill.classList.remove('border-gray-200', 'bg-white', 'text-gray-700', 'text-gray-600');
            pill.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-600', 'font-semibold');
        } else {
            pill.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-600', 'font-semibold');
            pill.classList.add('border-gray-200', 'bg-white', 'text-gray-600');
        }
    }

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

    function renderPriceRange() {
        return `<div class="grid grid-cols-2 gap-3">${(filterConfigs.price.fields || []).map(field => `<div><label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">${field.label}</label><input type="number" name="${field.name}" value="${state.filters[field.name] || ''}" placeholder="${field.placeholder}" class="popup-price-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:border-blue-500 outline-none"></div>`).join('')}</div>`;
    }

    function closePopup() {
        const popup = popupsContainer ? popupsContainer.querySelector('.filter-popup') : null;
        if (popup) popup.remove();
        state.activePopup = null;
    }

    function setValue(id, value) {
        const input = document.getElementById(id);
        if (input) input.value = value;
    }

    function syncFormWithState() {
        setValue('filter-category-id', state.filters.category_id);
        setValue('filter-min-price', state.filters.min_price);
        setValue('filter-max-price', state.filters.max_price);
        setValue('filter-sort', state.filters.sort);
        setValue('filter-q', state.filters.q);

        if (!dynamicInputsContainer) return;
        dynamicInputsContainer.innerHTML = '';

        ['brand', 'ram', 'color', 'size', 'needs'].forEach(key => {
            (state.filters[key] || []).forEach(v => appendHidden(`${key}[]`, v));
        });
        ['high_repairability', 'eco_friendly', 'in_stock', 'new_arrival'].forEach(key => {
            if (state.filters[key]) appendHidden(key, state.filters[key]);
        });
    }

    function appendHidden(name, value) {
        if (!dynamicInputsContainer) return;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        dynamicInputsContainer.appendChild(input);
    }

    function updateActiveFilters() {
        if (!activeFiltersContainer) return;
        activeFiltersContainer.innerHTML = '';
        const active = [];
        // Chỉ hiển thị tag danh mục nếu nó không phải là danh mục mặc định của trang (danh mục cố định)
        if (state.filters.category_id && !window.__INITIAL_CATEGORY_ID) active.push(['Danh mục', window.__INITIAL_CATEGORY_NAME || state.filters.category_id, 'category_id']);
        if (state.filters.min_price || state.filters.max_price) active.push(['Giá', `${state.filters.min_price || 0}đ - ${state.filters.max_price || '∞'}đ`, 'price']);

        const labels = { gaming: 'Chơi mượt Genshin', student: 'Học Web Dev' };
        state.filters.needs.forEach(v => active.push(['Nhu cầu', labels[v] || v, 'needs', v]));
        state.filters.brand.forEach(v => active.push(['Hãng', v, 'brand', v]));
        state.filters.ram.forEach(v => active.push(['RAM', v, 'ram', v]));
        state.filters.color.forEach(v => active.push(['Màu', v, 'color', v]));
        state.filters.size.forEach(v => active.push(['Size', v, 'size', v]));
        if (state.filters.in_stock) active.push(['Kho', 'Sẵn hàng', 'in_stock']);
        if (state.filters.new_arrival) active.push(['Mới', 'Hàng mới về', 'new_arrival']);
        if (state.filters.high_repairability) active.push(['Eco', 'Dễ sửa chữa', 'high_repairability']);
        if (state.filters.eco_friendly) active.push(['Eco', 'Thân thiện môi trường', 'eco_friendly']);

        active.forEach(item => {
            const tag = document.createElement('span');
            tag.className = 'px-3 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-semibold flex items-center gap-1.5 cursor-pointer hover:bg-red-100 border border-red-100';
            tag.innerHTML = `<span>${item[0]}: ${item[1]}</span><button class="w-4 h-4">×</button>`;
            tag.querySelector('button').addEventListener('click', e => {
                e.stopPropagation();
                removeFilter(item[2], item[3] ?? null);
            });
            activeFiltersContainer.appendChild(tag);
        });

        if (clearAllBtn) {
            clearAllBtn.classList.toggle('hidden', active.length === 0);
            if (active.length) activeFiltersContainer.appendChild(clearAllBtn);
        }
    }

    function removeFilter(key, value) {
        if (['brand', 'ram', 'color', 'size', 'needs'].includes(key)) {
            state.filters[key] = state.filters[key].filter(v => v !== value);
        } else if (key === 'price') {
            state.filters.min_price = '';
            state.filters.max_price = '';
        } else {
            state.filters[key] = '';
        }
        syncFormWithState();
        updateActiveFilters();
        syncQuickButtons();
        fetchFilteredProducts();
    }

    function syncQuickButtons() {
        document.querySelectorAll('.quick-filter-btn').forEach(btn => {
            const name = btn.dataset.name;
            const value = btn.dataset.value;
            const active = ['needs', 'brand', 'ram', 'color', 'size'].includes(name)
                ? (state.filters[name] || []).includes(value)
                : !!state.filters[name];

            if (active) {
                btn.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-200', 'hover:bg-blue-100', 'hover:text-blue-800');
                btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600', 'hover:bg-blue-700', 'hover:text-white');
            } else {
                btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600', 'hover:bg-blue-700', 'hover:text-white');
                btn.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-200', 'hover:bg-blue-100', 'hover:text-blue-800');
            }
        });

        // Đồng bộ trạng thái cho nút toggle (Sẵn hàng, Hàng mới về)
        const toggleMap = { stock: 'in_stock', new: 'new_arrival' };
        Object.entries(toggleMap).forEach(([filterKey, stateKey]) => {
            const btn = document.querySelector(`.filter-trigger[data-filter="${filterKey}"]`);
            if (!btn) return;
            if (state.filters[stateKey]) {
                btn.classList.remove('bg-gray-50', 'text-gray-600', 'border-transparent');
                btn.classList.add('bg-green-600', 'text-white', 'border-green-600', 'shadow-md');
            } else {
                btn.classList.remove('bg-green-600', 'text-white', 'border-green-600', 'shadow-md');
                btn.classList.add('bg-gray-50', 'text-gray-600', 'border-transparent');
            }
        });
    }

    function clearAll() {
        state.filters = {
            category_id: state.filters.category_id,
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

    function fetchFilteredProducts() {
        if (!filterForm) return;
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();
        formData.forEach((value, key) => {
            if (value !== '') params.append(key, value);
        });

        const queryString = params.toString();
        const url = `/products/filter?${queryString}`;
        window.history.pushState(null, '', `${window.location.pathname}${queryString ? `?${queryString}` : ''}`);
        fetchProductsByUrl(url);
    }

    function fetchProductsByUrl(url) {
        if (productListContainer) {
            productListContainer.innerHTML = `<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">${Array.from({ length: 8 }).map(() => `<div class="product-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 animate-pulse"><div class="h-48 bg-gray-200 w-full"></div><div class="p-4 space-y-3"><div class="h-3 bg-gray-200 rounded w-1/4"></div><div class="h-4 bg-gray-200 rounded w-3/4"></div><div class="h-4 bg-gray-200 rounded w-1/2"></div></div></div>`).join('')}</div>`;
        }

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                if (productListContainer) productListContainer.innerHTML = html;
                updateCountFromMarkup(html);
                attachPaginationEvents();
                window.dispatchEvent(new CustomEvent('product-grid:updated'));
            })
            .catch(() => {
                if (productListContainer) {
                    productListContainer.innerHTML = '<p class="text-red-500 text-center py-10">Có lỗi xảy ra khi tải sản phẩm. Vui lòng thử lại.</p>';
                }
            });
    }

    function updateCountFromMarkup(html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const grid = doc.querySelector('[data-total-products]');
        const total = grid ? grid.dataset.totalProducts : doc.querySelectorAll('.product-card').length;
        if (productCountDisplay) productCountDisplay.textContent = total || '0';
    }

    function attachPaginationEvents() {
        if (!productListContainer) return;
        productListContainer.querySelectorAll('.ajax-pagination-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const url = e.currentTarget.getAttribute('href');
                if (url) fetchProductsByUrl(url);
            });
        });
    }

    init();
});
