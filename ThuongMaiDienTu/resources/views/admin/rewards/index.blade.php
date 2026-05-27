@extends('admin.layouts.master')
@section('title', 'Quản lý đổi thưởng')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Quản lý đổi thưởng</h1>
                <p class="text-slate-500 mt-1">Quản lý voucher, quà tặng và vòng quay may mắn</p>
            </div>
            <a href="{{ route('rewards.index') }}" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold">Xem trang người dùng</a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold">Danh sách phần thưởng</h2>
                <button type="button" onclick="openCreateModal()" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold">Thêm reward</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-slate-500 border-b">
                        <tr>
                            <th class="py-3">Tên</th>
                            <th class="py-3">Loại</th>
                            <th class="py-3">Giá điểm</th>
                            <th class="py-3">Tồn</th>
                            <th class="py-3">Trạng thái</th>
                            <th class="py-3">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($catalog as $item)
                        <tr class="border-b last:border-0">
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 overflow-hidden shrink-0">
                                        @if($item->display_image)
                                            <img src="{{ asset('storage/'.$item->display_image) }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $item->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">{{ $item->reward_type }} / {{ $item->reward_category }}</td>
                            <td class="py-4 font-semibold">{{ number_format($item->points_cost) }}</td>
                            <td class="py-4">{{ is_null($item->stock) ? 'Không giới hạn' : $item->stock }}</td>
                            <td class="py-4">
                                <div class="flex flex-col gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $item->status_label }}
                                    </span>
                                    <div class="w-28 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width: {{ $item->progress_percent }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 space-x-2">
                                <button class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold" onclick='openEditModal(@json($item))'>Sửa</button>
                                <button class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold" onclick='openImageModal(@json($item))'>Đổi ảnh</button>
                                <form action="{{ route('admin.rewards.destroy', $item->reward_id) }}" method="POST" class="inline" onsubmit="return confirm('Xóa reward này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-semibold">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-lg font-bold">Cài đặt nhanh</h2>
            <div class="p-4 rounded-2xl bg-slate-50">
                <div class="text-sm text-slate-500">Tổng lịch sử đổi thưởng</div>
                <div class="text-2xl font-black text-slate-900">{{ $stats['redemptions'] }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50">
                <div class="text-sm text-slate-500">Tổng lượt quay</div>
                <div class="text-2xl font-black text-slate-900">{{ $stats['spins'] }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50">
                <div class="text-sm text-slate-500">Tổng điểm đã tiêu</div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($stats['points_spent']) }}</div>
            </div>
        </div>
    </div>
</div>

@include('admin.rewards.partials.image-modal')

<div id="reward-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl p-6 max-w-2xl w-full">
        <h3 id="modal-title" class="text-xl font-bold mb-4">Tạo reward</h3>
        <form id="reward-form" method="POST">
            @csrf
            <div id="method-field"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input name="code" placeholder="Mã" class="border rounded-xl p-3 uppercase" pattern="[A-Z0-9_\-]+" title="Chỉ dùng A-Z, 0-9, _ hoặc -">
                <input name="name" placeholder="Tên reward" class="border rounded-xl p-3" minlength="3" maxlength="150">
                <select name="reward_type" class="border rounded-xl p-3">
                    <option value="voucher">voucher</option>
                    <option value="shipping">shipping</option>
                    <option value="product">product</option>
                    <option value="wheel_prize">wheel_prize</option>
                </select>
                <select name="reward_category" class="border rounded-xl p-3">
                    <option value="free_ship">free_ship</option>
                    <option value="discount">discount</option>
                    <option value="gift">gift</option>
                    <option value="wheel">wheel</option>
                </select>
                <input name="points_cost" type="number" min="0" max="1000000" placeholder="Điểm" class="border rounded-xl p-3">
                <input name="discount_amount" type="number" min="0" max="50000000" placeholder="Giảm tiền" class="border rounded-xl p-3">
                <input name="shipping_discount_amount" type="number" min="0" max="5000000" placeholder="Giảm ship" class="border rounded-xl p-3">
                <input name="stock" type="number" min="0" max="100000" placeholder="Tồn kho" class="border rounded-xl p-3">
                <input name="max_per_user" type="number" min="1" max="100" placeholder="Giới hạn/user" class="border rounded-xl p-3">
                <div class="space-y-1">
                    <input name="min_rank_points" type="number" min="0" max="1000000" placeholder="Min rank points" class="border rounded-xl p-3 w-full">
                    <p class="text-xs text-slate-500 leading-relaxed">Điểm rank tối thiểu để được đổi. Ví dụ 5000 nghĩa là user phải đạt tối thiểu 5.000 rank points.</p>
                </div>
                <div class="space-y-1">
                    <select name="requires_rank_check" class="border rounded-xl p-3 w-full">
                        <option value="0">Không bắt buộc rank</option>
                        <option value="1">Bắt buộc rank</option>
                    </select>
                    <p class="text-xs text-slate-500 leading-relaxed">Bật khi reward chỉ dành cho nhóm user đã đạt hạng nhất định.</p>
                </div>
                <input name="starts_at" type="datetime-local" class="border rounded-xl p-3">
                <input name="ends_at" type="datetime-local" class="border rounded-xl p-3">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="text-sm font-semibold text-slate-600 block mb-2">Ảnh hiện tại</label>
                    <div id="image-preview" class="w-full h-40 rounded-2xl bg-slate-100 overflow-hidden flex items-center justify-center text-slate-400 text-sm">Chưa có ảnh</div>
                    <p class="text-xs text-slate-500 mt-2">Ảnh reward được cập nhật riêng qua nút "Đổi ảnh" trong danh sách.</p>
                </div>
                <div>
                    <textarea name="description" rows="7" placeholder="Mô tả" class="border rounded-xl p-3 w-full h-full"></textarea>
                </div>
            </div>
            <label class="inline-flex items-center gap-2 mt-3"><input type="checkbox" name="is_active" checked> Kích hoạt</label>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="closeRewardModal()" class="px-4 py-2 rounded-xl bg-slate-100">Đóng</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white">Lưu</button>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('reward-modal');
const form = document.getElementById('reward-form');
const imageModal = document.getElementById('reward-image-modal');
const imageForm = document.getElementById('reward-image-form');
function openCreateModal(){
  document.getElementById('modal-title').textContent = 'Tạo reward';
  form.action = '{{ route('admin.rewards.store') }}';
  document.getElementById('method-field').innerHTML = '';
  form.reset();
  document.getElementById('image-preview').textContent = 'Chưa có ảnh';
  modal.classList.remove('hidden'); modal.classList.add('flex');
}
function openEditModal(item){
  document.getElementById('modal-title').textContent = 'Sửa reward';
  form.action = `/admin/rewards/${item.reward_id}`;
  document.getElementById('method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';
  for (const [k,v] of Object.entries(item)) {
    const el = form.querySelector(`[name="${k}"]`);
    if (el) el.value = v ?? '';
  }
  const preview = document.getElementById('image-preview');
  if (item.display_image) {
    preview.innerHTML = `<img src="/storage/${item.display_image}" class="w-full h-full object-cover" alt="preview">`;
  } else {
    preview.textContent = 'Chưa có ảnh';
  }
  modal.classList.remove('hidden'); modal.classList.add('flex');
}
function closeRewardModal(){ modal.classList.add('hidden'); modal.classList.remove('flex'); }
function openImageModal(item){
  imageForm.action = `/admin/rewards/${item.reward_id}/image`;
  const preview = document.getElementById('reward-image-preview');
  const placeholder = document.getElementById('reward-image-placeholder');
  if (item.display_image) {
    preview.src = `/storage/${item.display_image}`;
    preview.classList.remove('hidden');
    placeholder.classList.add('hidden');
  } else {
    preview.src = '';
    preview.classList.add('hidden');
    placeholder.classList.remove('hidden');
  }
  imageModal.classList.remove('hidden'); imageModal.classList.add('flex');
}
function closeImageModal(){ imageModal.classList.add('hidden'); imageModal.classList.remove('flex'); }

document.getElementById('reward-image-form')?.addEventListener('change', (e) => {
  const file = e.target.files?.[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = () => {
    const preview = document.getElementById('reward-image-preview');
    const placeholder = document.getElementById('reward-image-placeholder');
    preview.src = reader.result;
    preview.classList.remove('hidden');
    placeholder.classList.add('hidden');
  };
  reader.readAsDataURL(file);
});
</script>
@endsection
