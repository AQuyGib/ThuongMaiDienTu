@extends('admin.layouts.master')

@section('title', 'Chi tiết sản phẩm')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .page-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(229, 231, 235, 0.9);
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
    }

    .info-grid {
        display: grid;
        gap: 1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #eef2f7;
    }

    .info-item:last-child {
        border-bottom: 0;
    }

    .info-label {
        color: #64748b;
        font-size: .875rem;
        font-weight: 500;
        flex-shrink: 0;
    }

    .info-value {
        color: #0f172a;
        font-weight: 600;
        text-align: right;
    }

    .soft-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        padding: .35rem .75rem;
        font-size: .75rem;
        font-weight: 700;
        background: #eef2ff;
        color: #4338ca;
    }

    .icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #64748b;
        transition: all .2s ease;
    }

    .icon-btn:hover {
        transform: translateY(-1px);
        border-color: #c7d2fe;
        color: #4338ca;
        background: #eef2ff;
    }

    .icon-btn.danger:hover {
        border-color: #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    .table-modern th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        border-bottom: 1px solid #e5e7eb !important;
    }

    .table-modern td {
        border-bottom: 1px solid #eef2f7 !important;
        vertical-align: middle;
    }

    .modal-backdrop-custom {
        background: rgba(15, 23, 42, .55);
    }

    .modal-panel {
        max-height: calc(100vh - 2rem);
        overflow: auto;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <div class="page-hero p-6 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700">
                    <i class="fa-solid fa-box-open"></i>
                    Chi tiết sản phẩm
                </div>
                <h1 class="mt-4 text-2xl font-bold text-slate-900 sm:text-3xl">{{ $product->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">Thông tin sản phẩm và các biến thể được hiển thị đồng bộ với giao diện quản trị mới.</p>
            </div>

            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="glass-card border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="glass-card border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
            <div class="font-semibold mb-2"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Vui lòng kiểm tra lại</div>
            <div class="space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="glass-card p-5">
            <p class="text-sm text-slate-500">Mã sản phẩm</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">#{{ $product->product_id }}</p>
        </div>
        <div class="glass-card p-5">
            <p class="text-sm text-slate-500">Danh mục</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">
                @if($product->category)
                    <span class="soft-badge">{{ $product->category->name }}</span>
                @else
                    <span class="text-slate-400">—</span>
                @endif
            </p>
        </div>
        <div class="glass-card p-5">
            <p class="text-sm text-slate-500">Giá gốc</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($product->base_price, 0, ',', '.') }}₫</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_1.4fr]">
        <div class="space-y-6">
            <div class="glass-card p-6">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Thông tin sản phẩm</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Tên sản phẩm</span>
                        <span class="info-value">{{ $product->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Danh mục</span>
                        <span class="info-value">
                            @if($product->category)
                                <span class="soft-badge">{{ $product->category->name }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Giá bán</span>
                        <span class="info-value text-emerald-600">{{ number_format($product->base_price, 0, ',', '.') }}₫</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Thương hiệu</span>
                        <span class="info-value">{{ $product->brand ?? '—' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Mô tả SEO</span>
                        <span class="info-value font-medium text-slate-500">{{ $product->seo_description ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Sản phẩm bán kèm (Cross-sell) --}}
            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">
                        <i class="fa-solid fa-cart-plus mr-2 text-indigo-600"></i>Sản Phẩm Bán Kèm (Cross-sell)
                    </h2>
                    <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                        {{ $product->crossSells->count() }} đã chọn
                    </span>
                </div>
                <p class="text-sm text-slate-500 mb-4">Chọn các sản phẩm thường được mua kèm với sản phẩm này để gợi ý mua kèm.</p>
                
                <form action="{{ route('admin.products.cross-sells.sync', $product->product_id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Chọn sản phẩm gợi ý</label>
                        <select name="cross_sell_ids[]" class="select2-crosssell w-full" multiple="multiple">
                            @foreach($allProducts as $p)
                                <option value="{{ $p->product_id }}" 
                                    {{ $product->crossSells->contains('product_id', $p->product_id) ? 'selected' : '' }}>
                                    {{ $p->name }} - ({{ number_format($p->base_price, 0, ',', '.') }}₫)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">
                            <i class="fa-solid fa-floppy-disk"></i> Lưu cấu hình
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="glass-card p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Biến thể</h2>
                    <p class="text-sm text-slate-500">{{ $product->variants->count() }} biến thể hiện có</p>
                </div>
                <button type="button" class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700" onclick="openModal('addVariantModal')">
                    <i class="fa-solid fa-plus"></i>
                    Thêm biến thể
                </button>
            </div>

            @php
                $catName = $product->category->name ?? '';
                $variantMode = match (true) {
                    str_contains($catName, 'Laptop') || str_contains($catName, 'MacBook') => 'laptop',
                    str_contains($catName, 'Điện thoại') || in_array($catName, ['iPhone', 'Samsung', 'Xiaomi', 'OPPO'], true) => 'phone',
                    str_contains($catName, 'Tablet') || in_array($catName, ['iPad', 'Samsung Galaxy Tab'], true) => 'tablet',
                    str_contains($catName, 'Tai nghe') || str_contains($catName, 'Loa') => 'audio',
                    str_contains($catName, 'Đồng hồ') => 'watch',
                    str_contains($catName, 'Tivi') || str_contains($catName, 'Màn hình') => 'tv',
                    default => 'default',
                };
                $hasRamRom = in_array($variantMode, ['laptop', 'phone', 'tablet'], true);
                $hasCpuGpu = in_array($variantMode, ['laptop', 'tablet', 'phone'], true);
                $colorLabel = match ($variantMode) {
                    'watch' => 'Kích Thước',
                    'tv' => 'Kích Thước',
                    'audio' => 'Phiên Bản / Màu',
                    default => 'Màu Sắc',
                };
            @endphp

            <div class="mt-5 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-left text-sm table-modern">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">{{ $colorLabel }}</th>
                            @if($hasRamRom)
                                <th class="px-4 py-3">RAM</th>
                                <th class="px-4 py-3">ROM</th>
                            @endif
                            @if($hasCpuGpu)
                                <th class="px-4 py-3">CPU</th>
                                <th class="px-4 py-3">GPU</th>
                            @endif
                            <th class="px-4 py-3">Giá +</th>
                            <th class="px-4 py-3">Tổng</th>
                            <th class="px-4 py-3">Ảnh</th>
                            <th class="px-4 py-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse($product->variants as $variant)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-xl border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">#{{ $variant->variant_id }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if($variant->color && !str_contains($colorLabel, 'Kích Thước'))
                                            <span class="h-4 w-4 rounded-full border border-slate-300" style="background: {{ $variant->color }}"></span>
                                        @endif
                                        <span>{{ $variant->color ?? '—' }}</span>
                                    </div>
                                </td>
                                @if($hasRamRom)
                                    <td class="px-4 py-3">{{ $variant->ram ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $variant->rom_capacity ?? '—' }}</td>
                                @endif
                                @if($hasCpuGpu)
                                    <td class="px-4 py-3">{{ $variant->cpu_chip ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $variant->gpu_chip ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-3 text-amber-600">+{{ number_format($variant->extra_price, 0, ',', '.') }}₫</td>
                                <td class="px-4 py-3 font-semibold text-emerald-600">{{ number_format($product->base_price + $variant->extra_price, 0, ',', '.') }}₫</td>
                                <td class="px-4 py-3">
                                    @if($variant->image_url)
                                        <img src="{{ $variant->image_url }}" class="h-11 w-11 rounded-xl object-cover" alt="variant">
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" class="icon-btn" onclick="openEditVariant({{ $variant->variant_id }}, @js($variant->color ?? ''), @js($variant->ram ?? ''), @js($variant->rom_capacity ?? ''), @js($variant->cpu_chip ?? ''), @js($variant->gpu_chip ?? ''), {{ $variant->extra_price }}, @js($variant->image_url ?? ''))">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="icon-btn danger" onclick="confirmDeleteVariant({{ $variant->variant_id }})">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-16 text-center text-slate-500">
                                    <i class="fa-regular fa-folder-open mb-3 text-4xl text-slate-300"></i>
                                    <div class="font-medium">Chưa có biến thể nào.</div>
                                    <div class="text-sm">Hãy thêm biến thể đầu tiên để hoàn thiện sản phẩm.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="addVariantModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 modal-backdrop-custom" onclick="closeModal('addVariantModal')"></div>
    <div class="relative w-full max-w-3xl rounded-3xl bg-white shadow-2xl modal-panel">
        <form action="{{ route('admin.products.variants.store', $product->product_id) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8">
            @csrf
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Thêm biến thể</h3>
                    <p class="mt-1 text-sm text-slate-500">Thiết kế nhất quán với màn hình sản phẩm.</p>
                </div>
                <button type="button" class="icon-btn" onclick="closeModal('addVariantModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">{{ $colorLabel }}</label>
                    <input type="text" name="color" required maxlength="30" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full" placeholder="VD: Đen, 128GB...">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Giá cộng thêm (₫)</label>
                    <input type="number" name="extra_price" required min="0" value="0" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full">
                </div>
                @if($hasRamRom)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">RAM</label>
                        <input type="text" name="ram" maxlength="20" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">ROM / Bộ nhớ</label>
                        <input type="text" name="rom_capacity" maxlength="20" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full">
                    </div>
                @endif
                @if($hasCpuGpu)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">CPU</label>
                        <input type="text" name="cpu_chip" maxlength="100" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">GPU</label>
                        <input type="text" name="gpu_chip" maxlength="100" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full">
                    </div>
                @endif
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Ảnh biến thể</label>
                    <input type="text" name="image_url" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full" placeholder="https://...">
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3 border-t border-slate-200 pt-5">
                <button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50" onclick="closeModal('addVariantModal')">Hủy</button>
                <button type="submit" class="rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700">Thêm biến thể</button>
            </div>
        </form>
    </div>
</div>

<div id="editVariantModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 modal-backdrop-custom" onclick="closeModal('editVariantModal')"></div>
    <div class="relative w-full max-w-3xl rounded-3xl bg-white shadow-2xl modal-panel">
        <form id="editVariantForm" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8">
            @csrf
            @method('PUT')
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Sửa biến thể</h3>
                    <p class="mt-1 text-sm text-slate-500">Cập nhật dữ liệu của biến thể đang chọn.</p>
                </div>
                <button type="button" class="icon-btn" onclick="closeModal('editVariantModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">{{ $colorLabel }}</label>
                    <input type="text" id="evColor" name="color" required maxlength="30" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Giá cộng thêm (₫)</label>
                    <input type="number" id="evPrice" name="extra_price" required min="0" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full">
                </div>
                @if($hasRamRom)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">RAM</label>
                        <input type="text" id="evRam" name="ram" maxlength="20" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">ROM / Bộ nhớ</label>
                        <input type="text" id="evRom" name="rom_capacity" maxlength="20" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full">
                    </div>
                @endif
                @if($hasCpuGpu)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">CPU</label>
                        <input type="text" id="evCpu" name="cpu_chip" maxlength="100" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">GPU</label>
                        <input type="text" id="evGpu" name="gpu_chip" maxlength="100" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full">
                    </div>
                @endif
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Ảnh biến thể</label>
                    <input type="text" id="evImage" name="image_url" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full" placeholder="https://...">
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3 border-t border-slate-200 pt-5">
                <button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50" onclick="closeModal('editVariantModal')">Hủy</button>
                <button type="submit" class="rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteVariantForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<!-- jQuery & Select2 JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-crosssell').select2({
            placeholder: "Tìm kiếm và chọn sản phẩm...",
            allowClear: true
        });
    });

    const productId = {{ $product->product_id }};
    const baseUrl = "{{ url('admin/products') }}";

    function openModal(id) {
        const el = document.getElementById(id);
        if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
    }

    function openEditVariant(id, color, ram, rom, cpu, gpu, price, imageUrl) {
        document.getElementById('evColor').value = color || '';
        const evRam = document.getElementById('evRam');
        const evRom = document.getElementById('evRom');
        const evCpu = document.getElementById('evCpu');
        const evGpu = document.getElementById('evGpu');
        if (evRam) evRam.value = ram || '';
        if (evRom) evRom.value = rom || '';
        if (evCpu) evCpu.value = cpu || '';
        if (evGpu) evGpu.value = gpu || '';
        document.getElementById('evPrice').value = price || 0;
        document.getElementById('evImage').value = imageUrl || '';
        document.getElementById('editVariantForm').action = baseUrl + '/' + productId + '/variants/' + id;
        openModal('editVariantModal');
    }

    function confirmDeleteVariant(id) {
        Swal.fire({
            title: 'Xác nhận xóa?',
            html: 'Bạn có chắc muốn xóa biến thể này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteVariantForm');
                form.action = baseUrl + '/' + productId + '/variants/' + id;
                form.submit();
            }
        });
    }
</script>
@endpush
