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
                categoryId: product.category_id
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
                    categoryId: p.category_id
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

    // Kiểm tra cùng loại sản phẩm
    const existingSlot = document.querySelector('.compare-slot-filled[data-category-id]:not([data-category-id=""])');
    if (existingSlot && existingSlot.dataset.categoryId != productData.categoryId) {
        if (confirm('Sản phẩm này khác loại với danh sách hiện tại. Bạn có muốn xóa danh sách cũ để bắt đầu so sánh loại mới này không?')) {
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
    const toast = document.getElementById('compareToast');
    if (!toast) return;

    toast.querySelector('span').textContent = message;
    toast.className = 'compare-global-toast ' + type;
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
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
        if (metaEl) metaEl.textContent = `${products.length} sản phẩm`;

        // Render Head
        if (headEl) {
            headEl.innerHTML = `
                <tr class="sticky top-0 z-20 shadow-sm">
                    <th class="bg-gray-50 p-4 text-left font-semibold text-gray-700 w-56 border-b border-gray-200">Thuộc tính</th>
                    ${products.map(p => `
                        <th class="p-4 text-left min-w-72 align-top bg-white border-b border-gray-200">
                            <div class="space-y-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="font-semibold text-gray-900 leading-6 truncate">${escapeHtml(p.name)}</div>
                                    <button type="button" class="text-red-500 hover:text-red-700" onclick="window.removeAndRefresh('${p.product_id}')">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <img src="${p.thumbnail}" alt="${escapeHtml(p.name)}" class="h-24 w-full object-contain rounded-xl bg-gray-50 p-2">
                                <div class="text-red-600 font-bold">${new Intl.NumberFormat('vi-VN').format(p.base_price)}đ</div>
                            </div>
                        </th>
                    `).join('')}
                </tr>
            `;
        }

        // Render Body
        if (bodyEl) {
            const processedGeneralRows = [
                { label: 'Giá hiện tại', values: products.map(p => new Intl.NumberFormat('vi-VN').format(p.base_price) + 'đ') },
                { label: 'Giá cũ', values: products.map(p => p.old_price ? new Intl.NumberFormat('vi-VN').format(p.old_price) + 'đ' : '—') },
                { label: 'Đánh giá', values: products.map(p => p.rating ? `${p.rating}/5` : 'Chưa có') },
                { label: 'Danh mục', values: products.map(p => p.category_name || '—') },
            ].map(row => ({
                ...row,
                is_different: new Set(row.values).size > 1
            }));

            const specRows = rows.map(row => ({
                label: row.label,
                is_different: row.is_different,
                values: row.values
            }));

            const diffOnly = document.getElementById('diffOnlyCheckbox')?.checked || false;

            const filteredGeneralRows = diffOnly ? processedGeneralRows.filter(r => r.is_different) : processedGeneralRows;

            let html = filteredGeneralRows.map(row => `
                <tr class="${row.is_different ? 'bg-blue-50/50' : ''}">
                    <th class="sticky left-0 ${row.is_different ? 'bg-blue-50' : 'bg-white'} p-4 text-left font-medium text-gray-700 border-r border-gray-100">
                        ${row.label} ${row.is_different ? '<span class="ml-2 text-[10px] bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">Khác biệt</span>' : ''}
                    </th>
                    ${row.values.map(v => `<td class="p-4 text-gray-700">${v}</td>`).join('')}
                </tr>
            `).join('');

            const filteredSpecRows = diffOnly ? specRows.filter(r => r.is_different) : specRows;

            html += filteredSpecRows.map(row => {
                const rowValues = row.values.map(v => formatSpecValue(v));
                const numbers = rowValues.map(v => extractNumber(v)).filter(n => n !== null);
                const maxVal = (numbers.length > 1) ? Math.max(...numbers) : null;

                return `
                    <tr class="${row.is_different ? 'bg-blue-50/50' : ''}">
                        <th class="sticky left-0 ${row.is_different ? 'bg-blue-50' : 'bg-white'} p-4 text-left font-medium text-gray-700 border-r border-gray-100">
                            ${row.label} ${row.is_different ? '<span class="ml-2 text-[10px] bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">Khác biệt</span>' : ''}
                        </th>
                        ${rowValues.map(v => {
                            const num = extractNumber(v);
                            const isBest = maxVal !== null && num === maxVal;
                            return `<td class="p-4 text-gray-700 ${isBest ? 'text-blue-600 font-bold bg-blue-50/30' : ''}">
                                ${isBest ? '<i class="fa-solid fa-award mr-1 text-blue-500"></i>' : ''}${v}
                            </td>`;
                        }).join('')}
                    </tr>
                `;
            }).join('');

            if (diffOnly && filteredSpecRows.length === 0) {
                html += `<tr><td colspan="${products.length + 1}" class="p-10 text-center text-gray-400 italic">Không có khác biệt về thông số kỹ thuật</td></tr>`;
            }

            bodyEl.innerHTML = html;
        }

        // Render Mobile Cards
        if (mobileCardsEl) {
            mobileCardsEl.innerHTML = products.map((p, idx) => `
                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                    <div class="flex gap-4 mb-4">
                        <img src="${p.thumbnail}" alt="${escapeHtml(p.name)}" class="w-20 h-20 object-contain rounded-lg border border-gray-50">
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-gray-900 text-sm line-clamp-2">${escapeHtml(p.name)}</h3>
                                <button onclick="window.removeAndRefresh('${p.product_id}')" class="text-red-500"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            <div class="text-red-600 font-bold mt-1">${new Intl.NumberFormat('vi-VN').format(p.base_price)}đ</div>
                        </div>
                    </div>
                    <div class="space-y-2 pt-2 border-t border-gray-50">
                        ${(diffOnly ? rows.filter(r => r.is_different) : rows).map(row => `
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">${row.label}</span>
                                <span class="font-medium text-gray-900 text-right">${formatSpecValue(row.values[idx])}</span>
                            </div>
                        `).join('')}
                        ${diffOnly && rows.filter(r => r.is_different).length === 0 ? '<div class="text-center text-[10px] text-gray-400 italic pt-2">Không có khác biệt</div>' : ''}
                    </div>
                </div>
            `).join('');
        }

    } catch (error) {
        console.error('Error loading comparison page:', error);
    }
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
    clearAllBtn.addEventListener('click', () => {
        clearCompare();
        loadComparePage();
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

