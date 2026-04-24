<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sổ Quỹ – Điện Máy PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-100 text-slate-800">

<div class="flex min-h-screen items-stretch">

    {{-- SIDEBAR --}}
    @include('components.sidebar')

    {{-- MAIN --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Header --}}
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-500 hover:text-indigo-600 transition">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Sổ Quỹ Tài Chính</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ now('Asia/Ho_Chi_Minh')->locale('vi')->isoFormat('dddd, D [tháng] M, YYYY') }}</p>
                </div>
            </div>
            <button onclick="openModal()"
                    class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-xl font-semibold transition shadow">
                <i class="fa-solid fa-plus"></i> Thêm giao dịch
            </button>
        </header>

        <main class="flex-1 p-8 space-y-6">

            {{-- Flash success --}}
            @if(session('success'))
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-5 py-3 rounded-xl">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i> {{ session('success') }}
                </div>
            @endif

            {{-- Errors --}}
            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm px-5 py-3 rounded-xl">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-white rounded-2xl p-5 border border-slate-200 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tổng thu</p>
                        <p class="text-2xl font-black text-emerald-600 mt-1.5 tabular-nums">{{ number_format($totalIncome) }}<span class="text-sm">đ</span></p>
                    </div>
                    <div class="w-11 h-11 bg-emerald-50 rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-arrow-trend-up text-emerald-500"></i>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-slate-200 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tổng chi</p>
                        <p class="text-2xl font-black text-rose-500 mt-1.5 tabular-nums">{{ number_format($totalExpense) }}<span class="text-sm">đ</span></p>
                    </div>
                    <div class="w-11 h-11 bg-rose-50 rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-arrow-trend-down text-rose-500"></i>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-slate-200 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tồn quỹ</p>
                        <p class="text-2xl font-black mt-1.5 tabular-nums {{ $balance >= 0 ? 'text-blue-600' : 'text-rose-600' }}">
                            {{ $balance < 0 ? '-' : '' }}{{ number_format(abs($balance)) }}<span class="text-sm">đ</span>
                        </p>
                    </div>
                    <div class="w-11 h-11 bg-blue-50 rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-scale-balanced text-blue-500"></i>
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <form method="GET" action="{{ route('cashbooks.index') }}"
                  class="flex flex-wrap gap-3 bg-white px-5 py-4 rounded-2xl border border-slate-200">
                <div class="flex items-center gap-2 flex-1 min-w-52 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm"></i>
                    <input name="search" value="{{ request('search') }}" type="text"
                           placeholder="Tìm theo nội dung..."
                           class="bg-transparent border-none focus:outline-none text-sm w-full">
                </div>
                <select name="type" onchange="this.form.submit()"
                        class="border border-slate-200 bg-slate-50 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
                    <option value="">Tất cả loại</option>
                    <option value="Income"  {{ request('type') === 'Income'  ? 'selected' : '' }}>🟢 Thu </option>
                    <option value="Expense" {{ request('type') === 'Expense' ? 'selected' : '' }}>🔴 Chi </option>
                </select>
                <button type="submit"
                        class="bg-slate-800 hover:bg-indigo-600 text-white px-5 py-2 rounded-xl text-sm font-semibold transition">
                    Lọc
                </button>
                @if(request()->hasAny(['search','type']))
                    <a href="{{ route('cashbooks.index') }}"
                       class="flex items-center gap-1 px-4 py-2 text-sm text-slate-400 hover:text-rose-500 font-medium transition">
                        <i class="fa-solid fa-xmark"></i> Xóa lọc
                    </a>
                @endif
            </form>

            {{-- Table --}}
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                @if($cashbooks->isEmpty())
                    <div class="py-24 text-center">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-inbox text-slate-400 text-2xl"></i>
                        </div>
                        <p class="text-slate-500 font-semibold text-lg">Không có giao dịch nào</p>
                        <p class="text-slate-400 text-sm mt-1">Nhấn "Thêm giao dịch" để bắt đầu</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                                <th class="text-left px-6 py-3.5 font-semibold">Ngày</th>
                                <th class="text-left px-4 py-3.5 font-semibold">Loại</th>
                                <th class="text-left px-4 py-3.5 font-semibold">Nội dung</th>
                                <th class="text-right px-6 py-3.5 font-semibold">Số tiền</th>
                                <th class="text-center px-4 py-3.5 font-semibold">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($cashbooks as $cb)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3.5 text-slate-500 text-xs whitespace-nowrap">
                                    {{ $cb->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }}<br>
                                    <span class="text-slate-300">{{ $cb->created_at->timezone('Asia/Ho_Chi_Minh')->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($cb->type === 'Income')
                                        <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[11px] font-bold px-2.5 py-1 rounded-full">
                                            <i class="fa-solid fa-plus text-[9px]"></i> Thu
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-600 text-[11px] font-bold px-2.5 py-1 rounded-full">
                                            <i class="fa-solid fa-minus text-[9px]"></i> Chi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-slate-700 max-w-xs truncate font-medium">
                                    {{ $cb->description ?? '—' }}
                                </td>
                                <td class="px-6 py-3.5 text-right font-black tabular-nums {{ $cb->type === 'Income' ? 'text-emerald-600' : 'text-rose-500' }}">
                                    {{ $cb->type === 'Income' ? '+' : '-' }}{{ number_format($cb->amount) }}đ
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" 
                                           onclick="openEditModal(this)"
                                           data-id="{{ $cb->cashbook_id }}"
                                           data-type="{{ $cb->type }}"
                                           data-amount="{{ $cb->amount }}"
                                           data-desc="{{ $cb->description }}"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition"
                                           title="Sửa">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </button>
                                        <form action="{{ route('cashbooks.destroy', $cb->cashbook_id) }}"
                                              method="POST" class="inline"
                                              onsubmit="return confirm('Xóa giao dịch này?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                    title="Xóa">
                                                <i class="fa-solid fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    @if($cashbooks->hasPages())
                        <div class="px-6 py-4 border-t border-slate-100">
                            {{ $cashbooks->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </main>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL THÊM GIAO DỊCH
══════════════════════════════════════════════════════════ --}}
<div id="modal-add"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 relative">

        <button onclick="closeModal()"
                class="absolute top-5 right-5 w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition text-lg">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <h3 class="text-xl font-bold text-slate-800 mb-6">
            <i class="fa-solid fa-plus-circle mr-2 text-indigo-500"></i>Thêm giao dịch mới
        </h3>

        <form action="{{ route('cashbooks.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Loại --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Loại giao dịch <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="Income" class="sr-only peer" required>
                        <div class="border-2 border-slate-200 rounded-xl p-3 text-center text-sm font-bold text-slate-500 transition
                                    peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700
                                    hover:border-emerald-300">
                            <i class="fa-solid fa-arrow-down mr-1"></i> Thu
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="Expense" class="sr-only peer">
                        <div class="border-2 border-slate-200 rounded-xl p-3 text-center text-sm font-bold text-slate-500 transition
                                    peer-checked:bg-rose-50 peer-checked:border-rose-500 peer-checked:text-rose-700
                                    hover:border-rose-300">
                            <i class="fa-solid fa-arrow-up mr-1"></i> Chi
                        </div>
                    </label>
                </div>
            </div>

            {{-- Số tiền --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Số tiền (VNĐ) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="amount" min="1000" step="1000"
                           placeholder="Ví dụ: 500000"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           required>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">đ</span>
                </div>
            </div>

            {{-- Nội dung --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Nội dung <span class="text-rose-500">*</span>
                </label>
                <textarea name="description" rows="3" maxlength="500"
                          placeholder="Mô tả chi tiết giao dịch..."
                          class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
                          required></textarea>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModal()"
                        class="flex-1 border border-slate-200 text-slate-600 py-3 rounded-xl font-semibold text-sm hover:bg-slate-50 transition">
                    Hủy
                </button>
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold text-sm transition shadow">
                    <i class="fa-solid fa-paper-plane mr-2"></i>Lưu giao dịch
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL SỬA GIAO DỊCH
══════════════════════════════════════════════════════════ --}}
<div id="modal-edit" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 relative">
        <button type="button" onclick="closeEditModal()" class="absolute top-5 right-5 w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition text-lg">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <h3 class="text-xl font-bold text-slate-800 mb-6">
            <i class="fa-solid fa-pen mr-2 text-indigo-500"></i>Sửa giao dịch
        </h3>
        <form id="form-edit" method="POST" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Loại giao dịch <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" id="edit-type-income" value="Income" class="sr-only peer" required>
                        <div class="border-2 border-slate-200 rounded-xl p-3 text-center text-sm font-bold text-slate-500 transition peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700 hover:border-emerald-300">
                            <i class="fa-solid fa-arrow-down mr-1"></i> Thu
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" id="edit-type-expense" value="Expense" class="sr-only peer">
                        <div class="border-2 border-slate-200 rounded-xl p-3 text-center text-sm font-bold text-slate-500 transition peer-checked:bg-rose-50 peer-checked:border-rose-500 peer-checked:text-rose-700 hover:border-rose-300">
                            <i class="fa-solid fa-arrow-up mr-1"></i> Chi
                        </div>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Số tiền (VNĐ) <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <input type="number" name="amount" id="edit-amount" min="1000" step="1000" class="w-full border border-slate-200 rounded-xl px-4 py-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">đ</span>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nội dung <span class="text-rose-500">*</span></label>
                <textarea name="description" id="edit-description" rows="3" maxlength="500" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none" required></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeEditModal()" class="flex-1 border border-slate-200 text-slate-600 py-3 rounded-xl font-semibold text-sm hover:bg-slate-50 transition">Hủy</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold text-sm transition shadow">
                    <i class="fa-solid fa-save mr-2"></i>Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal()  { document.getElementById('modal-add').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal-add').classList.add('hidden'); }

    function openEditModal(btn) {
        const id = btn.getAttribute('data-id');
        document.getElementById('form-edit').action = `/cashbooks/${id}`;
        document.getElementById('edit-amount').value = btn.getAttribute('data-amount');
        document.getElementById('edit-description').value = btn.getAttribute('data-desc');
        
        if(btn.getAttribute('data-type') === 'Income') {
            document.getElementById('edit-type-income').checked = true;
        } else {
            document.getElementById('edit-type-expense').checked = true;
        }

        document.getElementById('modal-edit').classList.remove('hidden');
    }
    function closeEditModal() { document.getElementById('modal-edit').classList.add('hidden'); }

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
    }
    // Mở lại modal nếu có validation error
    @if($errors->any())
        openModal();
    @endif
</script>

</body>
</html>
