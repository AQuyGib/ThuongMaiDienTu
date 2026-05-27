@extends('admin.layouts.master')

@section('title', 'Tạo chiến dịch thông báo')
@section('page-title', 'Tạo chiến dịch thông báo')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Tạo chiến dịch thông báo mới</h1>
            <p class="text-slate-500 text-sm mt-1">Gửi thông báo tới khách hàng, admin hoặc tài khoản cụ thể.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.notifications.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left mr-2"></i>Quay lại
            </a>
            <a href="{{ route('notifications.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                <i class="fa-regular fa-bell mr-2"></i>Xem trang người dùng
            </a>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.notifications.store') }}" id="createCampaignForm">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Cột trái: Form chính --}}
            <div class="lg:col-span-3 space-y-5">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                    {{-- Đối tượng nhận --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Đối tượng nhận</label>
                        <select name="target" id="targetSelect" onchange="handleTargetChange()" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 font-bold text-slate-700">
                            <option value="all">Tất cả người dùng</option>
                            @foreach($roles as $role)
                                <option value="role:{{ $role->role_id }}">{{ $role->name }}{{ $role->description ? ' — ' . $role->description : '' }}</option>
                            @endforeach
                            <option value="specific">Gửi cho tài khoản cụ thể</option>
                        </select>
                    </div>

                    {{-- Tìm chọn tài khoản cụ thể --}}
                    <div id="specificUsersSection" class="hidden space-y-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Tìm & chọn tài khoản nhận</label>
                        <div class="relative" id="userSearchWrapper">
                            <input type="text" id="userQueryInput" placeholder="Nhập tên, email hoặc ID..." class="w-full pl-10 pr-4 py-3 text-sm rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                            <i class="fa-solid fa-user-plus absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                            <div id="userSearchResults" class="hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 p-2 max-h-48 overflow-y-auto"></div>
                        </div>
                        <div id="selectedUsersContainer" class="flex flex-wrap gap-2 p-3 bg-slate-50 rounded-2xl border border-slate-100 min-h-[50px] items-center">
                            <span class="text-xs text-slate-400" id="noUsersText">Chưa có tài khoản nào được chọn.</span>
                        </div>
                    </div>

                    {{-- Tiêu đề --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tiêu đề</label>
                        <input name="title" type="text" required placeholder="Ví dụ: Flash Sale 50% hôm nay" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50">
                    </div>

                    {{-- Nội dung --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nội dung thông báo</label>
                        <textarea name="content" rows="5" required placeholder="Mô tả nội dung thông báo chi tiết..." class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50"></textarea>
                    </div>

                    {{-- Action URL --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Đường dẫn hành động (Action URL)</label>
                        <input name="action_url" type="text" placeholder="/products hoặc URL đầy đủ" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50">
                        <span class="text-[10px] text-slate-400 mt-1 block">Khách hàng sẽ chuyển hướng đến link này khi click vào thông báo.</span>
                    </div>

                    {{-- Sản phẩm liên quan (Multi-select) --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Sản phẩm liên quan (Chọn nhiều)</label>
                        <div class="relative" id="productSearchWrapper">
                            <input type="text" id="productQueryInput" placeholder="Tìm sản phẩm bằng tên..." class="w-full pl-10 pr-4 py-3 text-sm rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                            <div id="productSearchResults" class="hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 p-2 max-h-48 overflow-y-auto">
                                @foreach($products as $product)
                                    <div class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-xl cursor-pointer transition" data-id="{{ $product->product_id }}" onmousedown="addProductTag('{{ $product->product_id }}', '{{ addslashes($product->name) }}')">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-bold text-slate-800 truncate">{{ $product->name }}</div>
                                            <div class="text-[10px] text-slate-400">#{{ $product->product_id }} • {{ number_format($product->base_price) }}đ</div>
                                        </div>
                                        <div class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded shrink-0">Chọn</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div id="selectedProductsContainer" class="flex flex-wrap gap-2 mt-3 p-3 bg-slate-50 rounded-2xl border border-slate-100 min-h-[50px] items-center">
                            <span class="text-xs text-slate-400" id="noProductsText">Chưa có sản phẩm nào được chọn.</span>
                        </div>
                    </div>

                    {{-- Mã KM liên quan (Multi-select) --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Mã KM / Flash sale (Chọn nhiều)</label>
                        <div class="relative" id="promoSearchWrapper">
                            <input type="text" id="promoQueryInput" placeholder="Tìm mã KM/flash sale..." class="w-full pl-10 pr-4 py-3 text-sm rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                            <div id="promoSearchResults" class="hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 p-2 max-h-48 overflow-y-auto">
                                @foreach($promoItems as $promo)
                                    <div class="flex items-center justify-between p-2 hover:bg-slate-50 rounded-xl cursor-pointer transition" data-id="{{ $promo->promo_id }}" onmousedown="addPromoTag('{{ $promo->promo_id }}', '{{ addslashes($promo->promo_type) }}', '{{ addslashes($promo->code ?? '') }}')">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-bold text-slate-800">{{ $promo->promo_type }}</div>
                                            @if($promo->code)<div class="text-[10px] text-indigo-600 font-extrabold mt-0.5">Mã: {{ $promo->code }}</div>@endif
                                        </div>
                                        <div class="text-[10px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded">Chọn</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div id="selectedPromosContainer" class="flex flex-wrap gap-2 mt-3 p-3 bg-slate-50 rounded-2xl border border-slate-100 min-h-[50px] items-center">
                            <span class="text-xs text-slate-400" id="noPromosText">Chưa có khuyến mãi nào được chọn.</span>
                        </div>
                    </div>

                    {{-- Nút submit --}}
                    <div class="pt-2">
                        <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                            <i class="fa-solid fa-paper-plane mr-2"></i>Gửi thông báo
                        </button>
                    </div>
                </div>
            </div>

            {{-- Cột phải: Hướng dẫn --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="text-sm font-extrabold text-slate-900 mb-3"><i class="fa-solid fa-lightbulb text-amber-500 mr-2"></i>Gợi ý mẫu nhanh</h3>
                    <div class="space-y-2 text-xs text-slate-600 leading-relaxed">
                        <div class="p-3 rounded-xl bg-slate-50">• <strong>Flash Sale:</strong> giảm sốc theo khung giờ, gắn link tới danh mục.</div>
                        <div class="p-3 rounded-xl bg-slate-50">• <strong>Coupon:</strong> thông báo mã giảm giá mới cho khách hàng.</div>
                        <div class="p-3 rounded-xl bg-slate-50">• <strong>Thủ công:</strong> admin chủ động gửi thông báo riêng theo chiến dịch.</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="text-sm font-extrabold text-slate-900 mb-3"><i class="fa-solid fa-circle-info text-blue-500 mr-2"></i>Lưu ý vận hành</h3>
                    <ul class="text-xs text-slate-600 leading-relaxed space-y-1.5 list-disc pl-4">
                        <li>Nên dùng nội dung ngắn gọn, có CTA rõ ràng.</li>
                        <li>Chỉ gửi cho đúng nhóm người dùng để tránh spam.</li>
                        <li>Nên gắn <code class="bg-slate-100 px-1 rounded">action_url</code> tới trang sản phẩm hoặc trang khuyến mãi.</li>
                        <li>Chiến dịch sẽ được xử lý qua <strong>hàng đợi (Queue)</strong> nên không gây chậm hệ thống.</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // ========== Target Audience Toggle ==========
    window.handleTargetChange = function() {
        const target = document.getElementById('targetSelect').value;
        const section = document.getElementById('specificUsersSection');
        if (target === 'specific') {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    };

    // ========== Users Multi-select ==========
    const selectedUsers = {};
    let userDebounce;

    document.getElementById('userQueryInput')?.addEventListener('focus', () => {
        document.getElementById('userSearchResults').classList.remove('hidden');
    });

    document.getElementById('userQueryInput')?.addEventListener('input', function() {
        const q = this.value.trim();
        const container = document.getElementById('userSearchResults');
        if (!q) { container.innerHTML = '<div class="p-2 text-center text-xs text-slate-400">Nhập từ khóa để tìm...</div>'; return; }
        clearTimeout(userDebounce);
        userDebounce = setTimeout(() => {
            container.innerHTML = '<div class="p-2 text-center text-xs text-slate-400"><i class="fa-solid fa-spinner animate-spin mr-1"></i> Đang tìm...</div>';
            fetch(`{{ route('admin.notifications.search-users') }}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.length) { container.innerHTML = '<div class="p-2 text-center text-xs text-slate-400">Không tìm thấy</div>'; return; }
                    let html = '';
                    data.forEach(u => {
                        if (selectedUsers[u.user_id]) return;
                        html += `<div class="p-2 hover:bg-slate-50 rounded-xl cursor-pointer transition flex items-center justify-between" onmousedown="addUserTag(${u.user_id}, '${u.full_name.replace(/'/g,"\\'")}', '${u.email.replace(/'/g,"\\'")}')">
                            <div><div class="text-xs font-bold text-slate-800">${u.full_name}</div><div class="text-[10px] text-slate-400">${u.email}</div></div>
                            <div class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded">Chọn</div></div>`;
                    });
                    container.innerHTML = html || '<div class="p-2 text-center text-xs text-slate-400">Đã chọn hết</div>';
                }).catch(() => { container.innerHTML = '<div class="p-2 text-center text-xs text-rose-500">Lỗi tìm kiếm</div>'; });
        }, 300);
    });

    window.addUserTag = function(id, name, email) {
        if (selectedUsers[id]) return;
        selectedUsers[id] = { name, email };
        updateUserTags();
        document.getElementById('userQueryInput').value = '';
    };
    window.removeUserTag = function(id) { delete selectedUsers[id]; updateUserTags(); };

    function updateUserTags() {
        const c = document.getElementById('selectedUsersContainer');
        const keys = Object.keys(selectedUsers);
        if (!keys.length) { c.innerHTML = '<span class="text-xs text-slate-400">Chưa có tài khoản nào được chọn.</span>'; return; }
        c.innerHTML = keys.map(id => {
            const u = selectedUsers[id];
            return `<span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                ${u.name}<input type="hidden" name="user_ids[]" value="${id}">
                <button type="button" onclick="removeUserTag(${id})" class="w-4 h-4 rounded-full bg-indigo-200/50 hover:bg-indigo-200 text-indigo-800 text-[10px] flex items-center justify-center">&times;</button></span>`;
        }).join('');
    }

    // ========== Products Multi-select ==========
    const selectedProducts = {};
    const defaultProductsHTML = document.getElementById('productSearchResults')?.innerHTML || '';

    document.getElementById('productQueryInput')?.addEventListener('focus', () => {
        const r = document.getElementById('productSearchResults');
        r.classList.remove('hidden');
        filterItems('productSearchResults', selectedProducts, defaultProductsHTML);
    });

    document.getElementById('productQueryInput')?.addEventListener('input', function() {
        const q = this.value.trim();
        const r = document.getElementById('productSearchResults');
        if (!q) { r.innerHTML = defaultProductsHTML; filterItems('productSearchResults', selectedProducts, defaultProductsHTML); return; }
        // Lọc client-side từ danh sách mặc định
        r.innerHTML = defaultProductsHTML;
        const items = r.querySelectorAll('[data-id]');
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(q.toLowerCase()) ? '' : 'none';
        });
        filterItems('productSearchResults', selectedProducts, null);
    });

    window.addProductTag = function(id, name) {
        if (selectedProducts[id]) return;
        selectedProducts[id] = { name };
        updateProductTags();
        document.getElementById('productQueryInput').value = '';
        document.getElementById('productSearchResults').innerHTML = defaultProductsHTML;
        filterItems('productSearchResults', selectedProducts, defaultProductsHTML);
    };
    window.removeProductTag = function(id) {
        delete selectedProducts[id];
        updateProductTags();
        filterItems('productSearchResults', selectedProducts, defaultProductsHTML);
    };

    function updateProductTags() {
        const c = document.getElementById('selectedProductsContainer');
        const keys = Object.keys(selectedProducts);
        if (!keys.length) { c.innerHTML = '<span class="text-xs text-slate-400">Chưa có sản phẩm nào được chọn.</span>'; return; }
        c.innerHTML = keys.map(id => {
            const p = selectedProducts[id];
            return `<span class="inline-flex items-center gap-1.5 pl-2 pr-1 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                <span class="max-w-[140px] truncate">${p.name}</span><input type="hidden" name="product_ids[]" value="${id}">
                <button type="button" onclick="removeProductTag(${id})" class="w-4 h-4 rounded-full bg-emerald-200/50 hover:bg-emerald-200 text-emerald-800 text-[10px] flex items-center justify-center">&times;</button></span>`;
        }).join('');
    }

    // ========== Promos Multi-select ==========
    const selectedPromos = {};
    const defaultPromosHTML = document.getElementById('promoSearchResults')?.innerHTML || '';

    document.getElementById('promoQueryInput')?.addEventListener('focus', () => {
        const r = document.getElementById('promoSearchResults');
        r.classList.remove('hidden');
        filterItems('promoSearchResults', selectedPromos, defaultPromosHTML);
    });

    document.getElementById('promoQueryInput')?.addEventListener('input', function() {
        const q = this.value.trim();
        const r = document.getElementById('promoSearchResults');
        if (!q) { r.innerHTML = defaultPromosHTML; filterItems('promoSearchResults', selectedPromos, defaultPromosHTML); return; }
        r.innerHTML = defaultPromosHTML;
        const items = r.querySelectorAll('[data-id]');
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(q.toLowerCase()) ? '' : 'none';
        });
        filterItems('promoSearchResults', selectedPromos, null);
    });

    window.addPromoTag = function(id, type, code) {
        if (selectedPromos[id]) return;
        selectedPromos[id] = { type, code };
        updatePromoTags();
        document.getElementById('promoQueryInput').value = '';
        document.getElementById('promoSearchResults').innerHTML = defaultPromosHTML;
        filterItems('promoSearchResults', selectedPromos, defaultPromosHTML);
    };
    window.removePromoTag = function(id) {
        delete selectedPromos[id];
        updatePromoTags();
        filterItems('promoSearchResults', selectedPromos, defaultPromosHTML);
    };

    function updatePromoTags() {
        const c = document.getElementById('selectedPromosContainer');
        const keys = Object.keys(selectedPromos);
        if (!keys.length) { c.innerHTML = '<span class="text-xs text-slate-400">Chưa có khuyến mãi nào được chọn.</span>'; return; }
        c.innerHTML = keys.map(id => {
            const pr = selectedPromos[id];
            const codeDisplay = pr.code ? ` (${pr.code})` : '';
            return `<span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100">
                <span class="max-w-[150px] truncate">${pr.type}${codeDisplay}</span><input type="hidden" name="promo_ids[]" value="${id}">
                <button type="button" onclick="removePromoTag(${id})" class="w-4 h-4 rounded-full bg-amber-200/50 hover:bg-amber-200 text-amber-800 text-[10px] flex items-center justify-center">&times;</button></span>`;
        }).join('');
    }

    // ========== Shared: Filter selected items from dropdown ==========
    function filterItems(containerId, selectedMap, defaultHTML) {
        const c = document.getElementById(containerId);
        if (!c) return;
        if (defaultHTML && c.querySelectorAll('[data-id]').length === 0) c.innerHTML = defaultHTML;
        const items = c.querySelectorAll('[data-id]');
        let visible = 0;
        items.forEach(item => {
            const id = item.getAttribute('data-id');
            if (selectedMap[id]) { item.style.display = 'none'; } else { if (item.style.display !== 'none') visible++; }
        });
    }

    // ========== Close dropdowns on click outside ==========
    document.addEventListener('click', function(e) {
        ['productSearchWrapper', 'promoSearchWrapper', 'userSearchWrapper'].forEach(id => {
            const wrapper = document.getElementById(id);
            if (wrapper && !wrapper.contains(e.target)) {
                const results = wrapper.querySelector('[id$="Results"]');
                if (results) setTimeout(() => results.classList.add('hidden'), 150);
            }
        });
    });
});
</script>
@endpush
