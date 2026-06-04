// resources/js/compare.js

let currentSlotIndex = null;
const MAX_SLOTS = 3;
const SEARCH_API = '/api/products/search-compare';
const SYNC_API = '/compare/sync';

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// --- Search Modal Logic ---
window.openCompareSearch = function(slotIndex) {
    currentSlotIndex = slotIndex;
    const modal = document.getElementById('compareSearchModal');
    if (modal) {
        modal.style.display = 'flex';
        const input = document.getElementById('compareSearchInput');
        input.value = '';
        document.getElementById('compareSearchResults').innerHTML = '<div style="padding:20px; text-align:center;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải gợi ý...</div>';
        
        // Gọi search với từ khóa trống để hiện sản phẩm gợi ý
        performSearch('').then(renderSearchResults);
        
        input.focus();
    }
};

window.closeCompareSearch = function() {
    const modal = document.getElementById('compareSearchModal');
    if (modal) modal.style.display = 'none';
};

function performSearch(query) {
    // Lấy danh sách ID đã có để loại trừ (exclude)
    const excludeIds = Array.from(document.querySelectorAll('.compare-slot-filled[data-product-id]'))
        .map(el => el.dataset.productId)
        .filter(id => id !== "");

    return fetch(`${SEARCH_API}?keyword=${encodeURIComponent(query)}&exclude=${excludeIds.join(',')}`)
        .then(res => res.json());
}

function renderSearchResults(results) {
    const container = document.getElementById('compareSearchResults');
    const input = document.getElementById('compareSearchInput');
    const isSearching = input && input.value.trim().length > 0;
    
    container.innerHTML = '';
    
    if (results.length === 0) {
        container.innerHTML = '<div style="padding:20px; text-align:center; color:#888;">Không tìm thấy sản phẩm phù hợp</div>';
        return;
    }

    // Thêm tiêu đề nếu là gợi ý
    if (!isSearching) {
        const title = document.createElement('div');
        title.style.cssText = 'padding: 10px 12px; font-size: 13px; font-weight: 700; color: #666; background: #f8f9fa; border-radius: 8px; margin-bottom: 8px;';
        title.innerHTML = '<i class="fa-solid fa-fire" style="color:#d70018"></i> Sản phẩm gợi ý';
        container.appendChild(title);
    }

    results.forEach(product => {
        const item = document.createElement('div');
        item.className = 'compare-search-result-item';
        item.innerHTML = `
            <img src="${product.thumbnail}" alt="${product.name}" />
            <div class="compare-search-result-info">
                <div class="compare-search-result-name">${product.name}</div>
                <div class="compare-search-result-price">${new Intl.NumberFormat('vi-VN').format(product.base_price)}đ</div>
            </div>
        `;
        item.onclick = () => {
            const productData = {
                id: product.product_id,
                name: product.name,
                image: product.thumbnail,
                price: new Intl.NumberFormat('vi-VN').format(product.base_price) + 'đ',
                categoryId: product.category_id,
                rootCategoryId: product.root_category_id
            };
            addToCompare(productData);
        };
        container.appendChild(item);
    });
}

const searchInput = document.getElementById('compareSearchInput');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function (e) {
        const term = e.target.value.trim();
        if (term.length < 2) {
            document.getElementById('compareSearchResults').innerHTML = '';
            return;
        }
        performSearch(term).then(renderSearchResults);
    }, 300));
}

// --- Comparison Logic ---

/**
 * Thêm sản phẩm vào so sánh.
 * Hỗ trợ cả object (từ search) và ID (từ trang chi tiết).
 */
window.addToCompare = async function(productOrId) {
    let productData;

    // Nếu là ID (string hoặc number)
    if (typeof productOrId !== 'object') {
        const id = productOrId;
        // Kiểm tra xem đã có chưa
        const exists = document.querySelector(`.compare-slot-filled[data-product-id="${id}"]`);
        if (exists) {
            showToast('Sản phẩm này đã có trong danh sách so sánh', 'error');
            return;
        }

        // Tìm slot trống đầu tiên nếu không có slot cụ thể
        if (currentSlotIndex === null) {
            const filledSlots = Array.from(document.querySelectorAll('.compare-slot-filled'));
            const emptySlotIndex = filledSlots.findIndex(el => !el.dataset.productId || el.dataset.productId === "");
            
            if (emptySlotIndex === -1) {
                showToast('Danh sách so sánh đã đầy (tối đa 3 sản phẩm)', 'error');
                return;
            }
            currentSlotIndex = emptySlotIndex;
        }

        // Fetch thông tin sản phẩm từ server nếu chỉ có ID
        try {
            const response = await fetch(`/compare/data?ids=${id}`);
            const data = await response.json();
            if (data.products && data.products.length > 0) {
                const p = data.products[0];
                productData = {
                    id: p.product_id,
                    name: p.name,
                    image: p.thumbnail,
                    price: new Intl.NumberFormat('vi-VN').format(p.base_price) + 'đ',
                    categoryId: p.category_id,
                    rootCategoryId: p.root_category_id
                };
            } else {
                showToast('Không tìm thấy thông tin sản phẩm', 'error');
                return;
            }
        } catch (error) {
            console.error('Error fetching product data:', error);
            showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
            return;
        }
    } else {
        productData = productOrId;
    }

    // Kiểm tra cùng loại sản phẩm (sử dụng Root Category)
    const existingSlot = document.querySelector('.compare-slot-filled[data-root-category-id]:not([data-root-category-id=""])');
    if (existingSlot && existingSlot.dataset.rootCategoryId != productData.rootCategoryId) {
        const result = await Swal.fire({
            title: '<span class="text-2xl font-black text-gray-900">Khác loại sản phẩm?</span>',
            html: `<div class="text-gray-500 font-medium leading-relaxed mt-2">
                Sản phẩm này khác loại với danh sách hiện tại.<br>
                Bạn có muốn <span class="text-red-500 font-bold">xóa danh sách cũ</span> để bắt đầu so sánh loại mới này không?
            </div>`,
            icon: 'warning',
            iconColor: '#f59e0b',
            showCancelButton: true,
            confirmButtonColor: '#0046ab',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Đồng ý, xóa cũ',
            cancelButtonText: 'Để sau',
            reverseButtons: true,
            padding: '2rem',
            background: '#ffffff',
            borderRadius: '24px',
            customClass: {
                popup: 'rounded-[2rem] shadow-2xl border-none',
                confirmButton: 'rounded-xl px-6 py-3 font-bold text-sm',
                cancelButton: 'rounded-xl px-6 py-3 font-bold text-sm'
            },
            showClass: {
                popup: 'animate__animated animate__fadeInUp animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutDown animate__faster'
            }
        });

        if (result.isConfirmed) {
            clearCompare();
        } else {
            return;
        }
    }

    // Điền dữ liệu vào slot
    const slot = document.getElementById(`compareSlot${currentSlotIndex}`);
    if (!slot) return;

    const emptyDiv = slot.querySelector('.compare-slot-empty');
    const filledDiv = slot.querySelector('.compare-slot-filled');

    filledDiv.style.display = 'flex';
    emptyDiv.style.display = 'none';

    filledDiv.querySelector('.compare-slot-img').src = productData.image;
    filledDiv.querySelector('.compare-slot-name').textContent = productData.name;
    filledDiv.querySelector('.compare-slot-price').textContent = productData.price;
    filledDiv.dataset.productId = productData.id;
    filledDiv.dataset.categoryId = productData.categoryId;
    filledDiv.dataset.rootCategoryId = productData.rootCategoryId;

    currentSlotIndex = null; // Reset slot index
    updateCount();
    closeCompareSearch();
    syncWithServer();
    updateProductCardButtons();
    
    // Nếu đang ở trang so sánh, cần reload dữ liệu bảng
    if (window.__COMPARE_PAGE__) {
        loadComparePage();
    }
    
    showToast('Đã thêm sản phẩm vào so sánh');
};

window.removeFromCompare = function(btn) {
    const slot = btn.closest('.compare-slot');
    const emptyDiv = slot.querySelector('.compare-slot-empty');
    const filledDiv = slot.querySelector('.compare-slot-filled');

    filledDiv.style.display = 'none';
    emptyDiv.style.display = 'flex';
    filledDiv.dataset.productId = '';
    updateCount();
    syncWithServer();
    updateProductCardButtons();
};

window.clearCompare = function() {
    document.querySelectorAll('.compare-slot-filled').forEach(el => {
        el.style.display = 'none';
        el.dataset.productId = "";
        el.dataset.categoryId = "";
        el.dataset.rootCategoryId = "";
    });
    const emptyDivs = document.querySelectorAll('.compare-slot-empty');
    emptyDivs.forEach(el => el.style.display = 'flex');
    
    updateCount();
    syncWithServer();
    updateProductCardButtons();
};

function updateCount() {
    const filledSlots = document.querySelectorAll('.compare-slot-filled[data-product-id]:not([data-product-id=""])');
    const count = filledSlots.length;
    
    const badge = document.getElementById('compareCountBadge');
    if (badge) badge.textContent = count;
    
    const bar = document.getElementById('compareBar');
    if (bar) {
        if (count > 0) {
            bar.style.display = 'block';
        } else {
            bar.style.display = 'none';
        }
    }
}

function updateProductCardButtons() {
    const ids = Array.from(document.querySelectorAll('.compare-slot-filled[data-product-id]'))
        .map(el => el.dataset.productId)
        .filter(id => id !== "");

    document.querySelectorAll('.compare-card-btn').forEach(btn => {
        const id = btn.dataset.productId;
        const isAdded = ids.includes(id);
        const statusBadge = btn.closest('.product-card')?.querySelector('.compare-status-badge');

        if (isAdded) {
            btn.classList.add('bg-blue-600', 'text-white', 'ring-2', 'ring-blue-200');
            btn.querySelector('.compare-card-btn-icon')?.classList.add('text-white');
            if (statusBadge) statusBadge.classList.remove('hidden');
        } else {
            btn.classList.remove('bg-blue-600', 'text-white', 'ring-2', 'ring-blue-200');
            btn.querySelector('.compare-card-btn-icon')?.classList.remove('text-white');
            if (statusBadge) statusBadge.classList.add('hidden');
        }
    });
}

function syncWithServer() {
    const ids = Array.from(document.querySelectorAll('.compare-slot-filled[data-product-id]'))
        .map(el => el.dataset.productId)
        .filter(id => id !== "");

    fetch(SYNC_API, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ ids: ids })
    });
}

function showToast(message, type = 'success') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#ffffff',
        color: '#1e293b',
        borderRadius: '16px',
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Toast.fire({
        icon: type,
        title: `<span class="text-sm font-bold ml-2">${message}</span>`,
        customClass: {
            popup: 'shadow-xl border border-gray-100 rounded-2xl'
        }
    });
}

// --- Toggle Collapse ---
function toggleCollapse() {
    const container = document.getElementById('compareSlotsContainer');
    const btn = document.getElementById('compareCollapseBtn');
    const bar = document.getElementById('compareBar');
    
    if (container && bar) {
        if (bar.classList.contains('collapsed')) {
            container.style.display = 'flex';
            bar.classList.remove('collapsed');
            if (btn) btn.textContent = 'Thu gọn';
        } else {
            container.style.display = 'none';
            bar.classList.add('collapsed');
            if (btn) btn.textContent = 'Mở rộng';
        }
    }
}

const collapseBtn = document.getElementById('compareCollapseBtn');
if (collapseBtn) {
    collapseBtn.addEventListener('click', toggleCollapse);
}

// --- Page Rendering Logic ---
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

function extractNumber(str) {
    if (typeof str !== 'string') return null;
    const match = str.match(/[\d\.]+/);
    return match ? parseFloat(match[0].replace(/\./g, '')) : null;
}

async function loadComparePage() {
    console.log('Loading compare page...');
    if (!window.__COMPARE_PAGE__) {
        console.log('Not on compare page, skipping table render.');
        return;
    }

    const emptyState = document.getElementById('compareEmptyState');
    const tableWrap = document.getElementById('compareTableWrap');
    const headEl = document.getElementById('compareHead');
    const bodyEl = document.getElementById('compareBody');
    const mobileCardsEl = document.getElementById('compareMobileCards');
    const metaEl = document.getElementById('compareMeta');

    const ids = Array.from(document.querySelectorAll('.compare-slot-filled[data-product-id]'))
        .map(el => el.dataset.productId)
        .filter(id => id !== "");

    if (ids.length === 0) {
        if (emptyState) emptyState.classList.remove('hidden');
        if (tableWrap) tableWrap.classList.add('hidden');
        if (metaEl) metaEl.textContent = '0 sản phẩm';
        return;
    }

    try {
        const response = await fetch(`/compare/data?ids=${ids.join(',')}`);
        const data = await response.json();
        const products = data.products || [];
        const rows = data.comparison_data || [];

        if (products.length === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
            if (tableWrap) tableWrap.classList.add('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');
        if (tableWrap) tableWrap.classList.remove('hidden');
        const chartWrap = document.getElementById('compareChartWrap');
        if (chartWrap) {
            if (products.length >= 2) {
                chartWrap.classList.remove('hidden');
                renderRadarChart(products, rows);
            } else {
                chartWrap.classList.add('hidden');
            }
        }
        if (metaEl) metaEl.textContent = `${products.length} sản phẩm`;

        // Render Head
        if (headEl) {
            headEl.innerHTML = `
                <tr class="z-[900]">
                    <th class="bg-white/95 backdrop-blur-md p-6 text-left font-black text-gray-900 w-64 uppercase tracking-wider text-xs border-r border-gray-100">Đặc điểm nổi bật</th>
                    ${products.map(p => `
                        <th class="p-6 text-left min-w-[300px] align-top bg-white/95 backdrop-blur-md border-r border-gray-100 last:border-r-0">
                            <div class="relative group">
                                <button type="button" class="absolute -top-2 -right-2 w-8 h-8 flex items-center justify-center rounded-full bg-red-50 text-red-500 opacity-0 group-hover:opacity-100 transition-all hover:bg-red-500 hover:text-white shadow-lg" onclick="window.removeAndRefresh('${p.product_id}')">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <div class="space-y-4">
                                    <div class="h-40 w-full overflow-hidden rounded-2xl bg-gray-50/50 p-4 flex items-center justify-center group-hover:bg-blue-50/30 transition-colors">
                                        <img src="${p.thumbnail}" alt="${escapeHtml(p.name)}" class="h-full object-contain mix-blend-multiply group-hover:scale-110 transition-transform duration-500">
                                    </div>
                                    <div class="space-y-1">
                                        <div class="font-black text-gray-900 leading-tight h-10 line-clamp-2 group-hover:text-blue-600 transition-colors">${escapeHtml(p.name)}</div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl font-black text-blue-600">${new Intl.NumberFormat('vi-VN').format(p.base_price)}đ</span>
                                            ${p.discount_percent ? `<span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">-${p.discount_percent}%</span>` : ''}
                                        </div>
                                    </div>
                                    <a href="/product/${p.product_id}" class="inline-flex w-full items-center justify-center py-2.5 rounded-xl border-2 border-blue-600 text-blue-600 font-bold text-xs hover:bg-blue-600 hover:text-white transition-all">Xem chi tiết</a>
                                </div>
                            </div>
                        </th>
                    `).join('')}
                </tr>
            `;
        }

        const diffOnly = document.getElementById('diffOnlyCheckbox')?.checked || false;

        // Render Body
        if (bodyEl) {
            const processedGeneralRows = [
                { label: 'Giá ưu đãi', values: products.map(p => `<span class="font-black text-blue-600 text-base">${new Intl.NumberFormat('vi-VN').format(p.base_price)}đ</span>`) },
                { label: 'Giá niêm yết', values: products.map(p => p.old_price ? `<span class="text-gray-400 line-through">${new Intl.NumberFormat('vi-VN').format(p.old_price)}đ</span>` : '<span class="text-gray-300">—</span>') },
                { label: 'Đánh giá người dùng', values: products.map(p => p.rating ? `<div class="flex items-center gap-1"><i class="fa-solid fa-star text-yellow-400"></i><span class="font-bold">${p.rating}</span><span class="text-gray-400 text-xs">(${p.review_count} lượt)</span></div>` : '<span class="text-gray-400">Chưa có</span>') },
                { label: 'Phân khúc', values: products.map(p => `<span class="px-2 py-1 bg-gray-100 rounded-md text-[10px] font-bold text-gray-600 uppercase">${p.category_name || '—'}</span>`) },
            ].map(row => ({
                ...row,
                is_different: new Set(row.values.map(v => v.replace(/<[^>]*>/g, ''))).size > 1
            }));

            const specRows = rows.map(row => ({
                label: row.label,
                is_different: row.is_different,
                values: row.values
            }));

            const filteredGeneralRows = diffOnly ? processedGeneralRows.filter(r => r.is_different) : processedGeneralRows;

            let html = filteredGeneralRows.map(row => `
                <tr class="group hover:bg-gray-50/50 transition-colors ${row.is_different ? 'bg-blue-50/30' : ''}">
                    <th class="p-4 text-left font-bold text-gray-500 border-r border-gray-100 bg-white group-hover:bg-gray-50/80 transition-colors">
                        <div class="flex items-center gap-2">
                            ${row.label}
                            ${row.is_different ? '<span class="flex h-2 w-2 rounded-full bg-blue-500"></span>' : ''}
                        </div>
                    </th>
                    ${row.values.map(v => `<td class="p-4 text-gray-700 border-r border-gray-100 last:border-r-0">${v}</td>`).join('')}
                </tr>
            `).join('');

            const filteredSpecRows = diffOnly ? specRows.filter(r => r.is_different) : specRows;

            html += filteredSpecRows.map(row => {
                const rowValues = row.values.map(v => formatSpecValue(v));
                const numbers = rowValues.map(v => extractNumber(v)).filter(n => n !== null);
                const maxVal = (numbers.length > 1) ? Math.max(...numbers) : null;

                return `
                    <tr class="group hover:bg-gray-50/50 transition-colors ${row.is_different ? 'bg-blue-50/30' : ''}">
                        <th class="p-4 text-left font-bold text-gray-500 border-r border-gray-100 bg-white group-hover:bg-gray-50/80 transition-colors">
                            <div class="flex items-center gap-2">
                                ${row.label}
                                ${row.is_different ? '<span class="flex h-2 w-2 rounded-full bg-blue-500"></span>' : ''}
                            </div>
                        </th>
                        ${rowValues.map(v => {
                            const num = extractNumber(v);
                            const isBest = maxVal !== null && num === maxVal;
                            return `<td class="p-4 text-gray-700 border-r border-gray-100 last:border-r-0 ${isBest ? 'bg-blue-50/40' : ''}">
                                <div class="flex items-center gap-2">
                                    ${isBest ? '<i class="fa-solid fa-crown text-blue-500 animate-bounce"></i>' : ''}
                                    <span class="${isBest ? 'text-blue-700 font-black' : ''}">${v}</span>
                                </div>
                            </td>`;
                        }).join('')}
                    </tr>
                `;
            }).join('');

            if (diffOnly && filteredSpecRows.length === 0 && filteredGeneralRows.length === 0) {
                html += `<tr><td colspan="${products.length + 1}" class="p-20 text-center">
                    <div class="flex flex-col items-center gap-4 text-gray-400">
                        <i class="fa-solid fa-equals text-4xl opacity-20"></i>
                        <p class="italic text-lg font-medium">Không có sự khác biệt đáng kể giữa các sản phẩm được chọn</p>
                    </div>
                </td></tr>`;
            }

            bodyEl.innerHTML = html;
        }

        // Render Mobile Cards
        if (mobileCardsEl) {
            mobileCardsEl.innerHTML = products.map((p, idx) => `
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-xl shadow-blue-900/5 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4">
                         <button onclick="window.removeAndRefresh('${p.product_id}')" class="w-10 h-10 flex items-center justify-center rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all">
                            <i class="fa-solid fa-trash-can"></i>
                         </button>
                    </div>
                    <div class="flex gap-5 mb-6">
                        <div class="w-24 h-24 rounded-2xl bg-gray-50 p-2 flex items-center justify-center shrink-0">
                            <img src="${p.thumbnail}" alt="${escapeHtml(p.name)}" class="max-h-full object-contain">
                        </div>
                        <div class="flex-1 pt-1">
                            <h3 class="font-black text-gray-900 text-base line-clamp-2 mb-2 pr-8">${escapeHtml(p.name)}</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-black text-blue-600">${new Intl.NumberFormat('vi-VN').format(p.base_price)}đ</span>
                                ${p.discount_percent ? `<span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">-${p.discount_percent}%</span>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4 pt-4 border-t border-gray-50">
                        ${(diffOnly ? rows.filter(r => r.is_different) : rows).map(row => `
                            <div class="flex justify-between items-center gap-4 text-xs">
                                <span class="text-gray-400 font-bold uppercase tracking-tight">${row.label}</span>
                                <span class="font-black text-gray-900 text-right">${formatSpecValue(row.values[idx])}</span>
                            </div>
                        `).join('')}
                    </div>
                    <a href="/product/${p.product_id}" class="mt-6 flex w-full items-center justify-center py-3 rounded-2xl bg-blue-600 text-white font-black text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">Xem chi tiết sản phẩm</a>
                </div>
            `).join('');
        }

    } catch (error) {
        console.error('Error loading comparison page:', error);
    }
}

let compareRadarChartInstance = null;
function renderRadarChart(products, rows) {
    const canvas = document.getElementById('compareRadarChart');
    if (!canvas) return;

    if (compareRadarChartInstance) {
        compareRadarChartInstance.destroy();
    }

    const labels = ['Màn hình', 'RAM', 'Pin', 'Đánh giá', 'Camera'];
    
    const datasets = products.map((p, idx) => {
        const getVal = (label) => {
            const row = rows.find(r => r.label.toLowerCase().includes(label.toLowerCase()));
            if (!row) return 0;
            const val = row.values[idx];
            return extractNumber(val) || 0;
        };

        const screen = getVal('Màn hình');
        const ram = getVal('RAM');
        const battery = getVal('Pin');
        const camera = getVal('Camera');
        const rating = p.rating || 0;

        const data = [
            Math.min((screen / 7) * 100, 100),
            Math.min((ram / 16) * 100, 100),
            Math.min((battery / 6000) * 100, 100),
            (rating / 5) * 100,
            Math.min((camera / 200) * 100, 100)
        ];

        const colors = [
            { border: '#2563eb', background: 'rgba(37, 99, 235, 0.15)' },
            { border: '#dc2626', background: 'rgba(220, 38, 38, 0.15)' },
            { border: '#16a34a', background: 'rgba(22, 163, 74, 0.15)' }
        ];
        const color = colors[idx % colors.length];

        return {
            label: p.name,
            data: data,
            fill: true,
            backgroundColor: color.background,
            borderColor: color.border,
            borderWidth: 3,
            pointBackgroundColor: color.border,
            pointBorderColor: '#fff',
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.2
        };
    });

    compareRadarChartInstance = new Chart(canvas, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    angleLines: { color: 'rgba(0,0,0,0.05)' },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    suggestedMin: 0,
                    suggestedMax: 100,
                    ticks: { display: false },
                    pointLabels: {
                        font: { size: 14, weight: '900', family: "'Inter', sans-serif" },
                        color: '#1e293b'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { 
                        boxWidth: 15, 
                        padding: 30, 
                        usePointStyle: true,
                        font: { size: 13, weight: '700', family: "'Inter', sans-serif" },
                        color: '#475569'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#1e293b',
                    bodyColor: '#475569',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            return ` ${context.dataset.label}: ${context.raw.toFixed(1)}/100`;
                        }
                    }
                }
            }
        }
    });
}

window.removeAndRefresh = function(id) {
    const filledDiv = document.querySelector(`.compare-slot-filled[data-product-id="${id}"]`);
    if (filledDiv) {
        const btn = filledDiv.querySelector('.compare-slot-remove');
        removeFromCompare(btn);
        loadComparePage();
    }
};

window.copyCompareLink = function() {
    const ids = Array.from(document.querySelectorAll('.compare-slot-filled[data-product-id]'))
        .map(el => el.dataset.productId)
        .filter(id => id && id !== "");
    
    if (ids.length === 0) {
        showToast('Hãy thêm sản phẩm để chia sẻ', 'error');
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('ids', ids.join(','));
    
    navigator.clipboard.writeText(url.toString()).then(() => {
        showToast('Đã sao chép liên kết so sánh');
    }).catch(err => {
        console.error('Copy failed:', err);
        showToast('Không thể sao chép liên kết', 'error');
    });
};

const clearAllBtn = document.getElementById('compareClearAllBtn');
if (clearAllBtn) {
    clearAllBtn.addEventListener('click', async () => {
        const result = await Swal.fire({
            title: '<span class="text-2xl font-black text-gray-900">Xóa toàn bộ?</span>',
            html: '<div class="text-gray-500 font-medium mt-2">Bạn có chắc chắn muốn xóa tất cả sản phẩm trong danh sách so sánh? Hành động này không thể hoàn tác.</div>',
            icon: 'question',
            iconColor: '#0046ab',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Đồng ý, xóa hết',
            cancelButtonText: 'Hủy',
            reverseButtons: true,
            padding: '2rem',
            background: '#ffffff',
            borderRadius: '24px',
            customClass: {
                popup: 'rounded-[2rem] shadow-2xl border-none',
                confirmButton: 'rounded-xl px-6 py-3 font-bold text-sm',
                cancelButton: 'rounded-xl px-6 py-3 font-bold text-sm'
            }
        });

        if (result.isConfirmed) {
            clearCompare();
            loadComparePage();
        }
    });
}

const diffOnlyCheckbox = document.getElementById('diffOnlyCheckbox');
if (diffOnlyCheckbox) {
    diffOnlyCheckbox.addEventListener('change', () => {
        loadComparePage();
    });
}

// Khởi tạo ban đầu
document.addEventListener('DOMContentLoaded', function() {
    // Luôn load page nếu đang ở trang so sánh để hiện trạng thái trống nếu cần
    if (window.__COMPARE_PAGE__) {
        loadComparePage();
    }

    // Load dữ liệu từ server nếu có (qua endpoint data)
    fetch('/compare/data')
        .then(res => res.json())
        .then(data => {
            if (data.products && data.products.length > 0) {
                data.products.forEach((productData, index) => {
                    if (index < 3) {
                        const slot = document.getElementById(`compareSlot${index}`);
                        if (slot) {
                            const empty = slot.querySelector('.compare-slot-empty');
                            const filled = slot.querySelector('.compare-slot-filled');
                            
                            if (empty && filled) {
                                empty.style.display = 'none';
                                filled.style.display = 'flex';
                                
                                const img = filled.querySelector('.compare-slot-img');
                                const name = filled.querySelector('.compare-slot-name');
                                const price = filled.querySelector('.compare-slot-price');
                                
                                if (img) img.src = productData.thumbnail;
                                if (name) name.textContent = productData.name;
                                if (price) price.textContent = new Intl.NumberFormat('vi-VN').format(productData.base_price) + 'đ';
                                
                                filled.dataset.productId = productData.product_id;
                                filled.dataset.categoryId = productData.category_id;
                                filled.dataset.rootCategoryId = productData.root_category_id;
                            }
                        }
                    }
                });
                updateCount();
                updateProductCardButtons();
                // Sau khi điền vào bar, render bảng
                loadComparePage();
            } else if (window.__COMPARE_PAGE__) {
                // Nếu rỗng nhưng đang ở trang so sánh, đảm bảo gọi loadComparePage để hiện empty state
                loadComparePage();
            }
        })
        .catch(err => {
            console.error('Error syncing compare data:', err);
            if (window.__COMPARE_PAGE__) loadComparePage();
        });
});

