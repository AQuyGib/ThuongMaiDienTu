document.addEventListener('DOMContentLoaded', function () {
    const STORAGE_KEY = 'compare_products';
    const MAX_ITEMS = 4;
    const PRODUCT_API = '/compare/data';
    const SYNC_API = '/compare/sync';
    const SEARCH_API = '/api/products/search-compare';
    const clearAllBtn = document.getElementById('compareClearAllBtn');
    const emptyState = document.getElementById('compareEmptyState');
    const tableWrap = document.getElementById('compareTableWrap');
    const headEl = document.getElementById('compareHead');
    const bodyEl = document.getElementById('compareBody');
    const mobileCardsEl = document.getElementById('compareMobileCards');
    const compareMetaEl = document.getElementById('compareMeta');
    const isLoggedIn = Array.isArray(window.__SERVER_COMPARE_IDS__);

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function getCompareIds() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            const ids = raw ? JSON.parse(raw) : [];
            return Array.isArray(ids) ? ids.map((id) => Number(id)).filter(Boolean) : [];
        } catch (e) {
            return [];
        }
    }

    async function syncCompareIds(ids) {
        if (!isLoggedIn) return;

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
            // Keep local state even if server sync fails.
        }
    }

    function saveCompareIds(ids) {
        const uniqueIds = Array.from(new Set(ids.map((id) => Number(id)).filter(Boolean))).slice(0, MAX_ITEMS);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(uniqueIds));
        renderCompareState();
        syncCompareIds(uniqueIds);
        return uniqueIds;
    }

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

    function animateCompareButton(productId) {
        const btn = document.querySelector(`.compare-card-btn[data-product-id="${productId}"]`);
        if (!btn) return;
        btn.classList.add('compare-btn-pop');
        setTimeout(() => btn.classList.remove('compare-btn-pop'), 180);
    }

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
        setTimeout(() => toast.classList.remove('show'), 2500);
    }

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

    function renderFloatingBadge() {
        const ids = getCompareIds();
        const badge = document.getElementById('compareFloatingBadge');
        const badgeCount = document.getElementById('compareFloatingBadgeCount');
        if (badgeCount) badgeCount.textContent = ids.length;
        if (badge) badge.classList.toggle('hidden', ids.length === 0);
        renderCompareButtons();
    }

    function renderCompareButtons() {
        const ids = getCompareIds();
        document.querySelectorAll('.compare-card-btn').forEach((btn) => {
            const productId = Number(btn.dataset.productId);
            const isAdded = ids.includes(productId);
            const status = btn.closest('.product-card')?.querySelector('.compare-status-badge');
            const compareText = btn.querySelector('.compare-card-btn-label');
            const icon = btn.querySelector('.compare-card-btn-icon');

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

    async function addToCompare(productId) {
        const id = Number(productId);
        if (!id) return;

        const ids = getCompareIds();
        if (ids.includes(id)) {
            showToast('Sản phẩm đã có trong danh sách so sánh.', 'error');
            return;
        }
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
        await loadComparePage();
    }

    async function removeCompareId(productId) {
        const id = Number(productId);
        const ids = getCompareIds().filter((item) => item !== id);
        saveCompareIds(ids);
        await loadComparePage();
    }

    async function clearCompare() {
        localStorage.removeItem(STORAGE_KEY);
        await syncCompareIds([]);
        renderCompareState();
        await loadComparePage();
    }

    function formatCurrency(value) {
        const num = Number(value || 0);
        return num ? `${num.toLocaleString('vi-VN')} ₫` : '—';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatSpecValue(value) {
        if (value === null || value === undefined || value === '') return '—';
        if (Array.isArray(value)) return escapeHtml(value.join(', '));
        if (typeof value === 'object') return escapeHtml(JSON.stringify(value));
        return escapeHtml(value);
    }

    function renderComparePageMobile(products, rows) {
        if (!mobileCardsEl) return;
        compareMetaEl && (compareMetaEl.textContent = `${products.length} sản phẩm`);
        const diffColumns = computeColumnDiffs(rows, products);

        mobileCardsEl.innerHTML = products.map((product) => {
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

        mobileCardsEl.querySelectorAll('[data-remove-id]').forEach((btn) => {
            btn.addEventListener('click', () => removeCompareId(btn.dataset.removeId));
        });
    }

    async function loadComparePage() {
        if (!window.__COMPARE_PAGE__) return;

        const ids = getCompareIds();
        if (ids.length === 0) {
            emptyState?.classList.remove('hidden');
            tableWrap?.classList.add('hidden');
            return;
        }

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

        const specKeys = new Map();
        rows.forEach((row) => specKeys.set(row.key, row.label));
        const generalRows = [
            { label: 'Tên sản phẩm', getter: (p, i) => escapeHtml(p.name), raw: (p) => p.name },
            { label: 'Giá hiện tại', getter: (p) => formatCurrency(p.base_price), raw: (p) => p.base_price },
            { label: 'Giá cũ', getter: (p) => formatCurrency(p.old_price), raw: (p) => p.old_price },
            { label: 'Giảm giá', getter: (p) => (p.discount_percent ? `${Number(p.discount_percent)}%` : '—'), raw: (p) => p.discount_percent },
            { label: 'Đánh giá', getter: (p) => (p.rating ? Number(p.rating).toFixed(1) : '—'), raw: (p) => p.rating },
            { label: 'Số review', getter: (p) => (p.review_count ?? '—'), raw: (p) => p.review_count },
            { label: 'Danh mục', getter: (p) => escapeHtml(p.category_name || '—'), raw: (p) => p.category_name },
        ];
        const specsRows = Array.from(specKeys.entries()).map(([key, label]) => ({
            label,
            getter: (p) => formatSpecValue(p.specifications?.[key]),
            raw: (p) => p.specifications?.[key],
            key,
        }));
        const allRows = [...generalRows, ...specsRows];
        const diffRows = new Set(rows.filter((row) => row.is_different).map((row) => row.label));

        bodyEl.innerHTML = allRows.map((row) => `
            <tr class="${diffRows.has(row.label) ? 'bg-blue-50/60' : ''}">
                <th class="sticky left-0 bg-white p-4 text-left font-medium text-gray-700 align-top border-r border-gray-100 ${diffRows.has(row.label) ? 'text-blue-700' : ''}">${escapeHtml(row.label)}</th>
                ${products.map((product) => `
                    <td class="p-4 align-top text-gray-700 border-r border-gray-50 ${diffRows.has(row.label) ? 'bg-blue-50/30' : ''}">${row.getter(product)}</td>
                `).join('')}
            </tr>
        `).join('');

        renderComparePageMobile(products, rows);

        bodyEl.querySelectorAll('[data-remove-id]').forEach((btn) => {
            btn.addEventListener('click', () => removeCompareId(btn.dataset.removeId));
        });
    }

    function renderCompareState() {
        renderFloatingBadge();
        renderCompareButtons();
    }

    async function hydrateFromServer() {
        if (!isLoggedIn) return;
        try {
            const serverIds = Array.isArray(window.__SERVER_COMPARE_IDS__)
                ? window.__SERVER_COMPARE_IDS__.map((id) => Number(id)).filter(Boolean)
                : [];
            const localIds = getCompareIds();
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

    clearAllBtn?.addEventListener('click', clearCompare);
    window.addToCompare = addToCompare;
    window.removeFromCompare = removeCompareId;
    window.clearCompare = clearCompare;
    window.addEventListener('product-grid:updated', renderCompareState);

    renderCompareState();
    hydrateFromServer().then(() => loadComparePage());
});
