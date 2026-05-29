@php
    $filters = $filters ?? [];
    $typeOptions = $typeOptions ?? [];
    $showRecipient = $showRecipient ?? false;
    $showDateRange = $showDateRange ?? false;
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Lọc theo loại</label>
            <select name="type" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tất cả</option>
                @foreach($typeOptions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Trạng thái đọc</label>
            <select name="read" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tất cả</option>
                <option value="unread" @selected(($filters['read'] ?? '') === 'unread')>Chưa đọc</option>
                <option value="read" @selected(($filters['read'] ?? '') === 'read')>Đã đọc</option>
            </select>
        </div>

        @if($showRecipient)
            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Người nhận</label>
                <input type="text" name="recipient" value="{{ $filters['recipient'] ?? '' }}" placeholder="Tên / email người nhận" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        @endif

        @if($showDateRange)
            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Từ ngày - đến ngày</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        @endif

        <div class="flex gap-3 md:col-span-4">
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition">Lọc</button>
            <a href="{{ $resetUrl ?? url()->current() }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Xóa lọc</a>
        </div>
    </form>
</div>
