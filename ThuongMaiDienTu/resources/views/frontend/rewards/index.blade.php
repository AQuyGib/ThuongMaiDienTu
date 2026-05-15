@extends('layouts.app')
@section('title', 'Đổi thưởng - DIENMAYPRO')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
  <div class="max-w-7xl mx-auto px-4">
    <div class="mb-6">
      <h1 class="text-3xl font-extrabold text-slate-900">Đổi thưởng</h1>
      <p class="text-slate-600 mt-2">Dùng điểm tiêu dùng để đổi voucher, quà tặng hoặc quay vòng may mắn.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <p class="text-sm text-slate-500">Điểm hiện có</p>
        <p class="text-3xl font-black text-blue-600 mt-2">{{ number_format($balance) }} điểm</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm lg:col-span-3">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <p class="text-sm text-slate-500">Vòng quay may mắn</p>
            <p class="text-lg font-bold text-slate-900">Quay 1 lần: 10 điểm</p>
          </div>
          <button id="spin-btn" class="px-5 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-semibold hover:opacity-90 transition">Quay ngay</button>
        </div>
        <a href="{{ route('rewards.history') }}" class="inline-flex mt-4 text-sm text-indigo-600 font-semibold hover:underline">Xem lịch sử đổi thưởng</a>
      </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
      <button class="filter-btn px-4 py-2 rounded-full bg-slate-900 text-white text-sm" data-filter="all">Tất cả</button>
      <button class="filter-btn px-4 py-2 rounded-full bg-white border text-sm" data-filter="voucher">Voucher</button>
      <button class="filter-btn px-4 py-2 rounded-full bg-white border text-sm" data-filter="shipping">Free ship</button>
      <button class="filter-btn px-4 py-2 rounded-full bg-white border text-sm" data-filter="product">Quà tặng</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="reward-grid">
      @forelse ($catalog as $reward)
        <div class="reward-card bg-white rounded-2xl border border-slate-200 shadow-sm p-5" data-id="{{ $reward->reward_id }}" data-type="{{ $reward->reward_type }}" data-category="{{ $reward->reward_category }}">
          <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex items-start gap-3 min-w-0">
              <div class="w-16 h-16 rounded-2xl overflow-hidden bg-slate-100 shrink-0">
                @if($reward->display_image)
                  <img src="{{ asset('storage/'.$reward->display_image) }}" class="w-full h-full object-cover" alt="{{ $reward->name }}">
                @else
                  <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-violet-500"></div>
                @endif
              </div>
              <div class="min-w-0">
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">{{ $reward->reward_category }}</span>
                <h3 class="text-xl font-bold text-slate-900 mt-3 truncate">{{ $reward->name }}</h3>
              </div>
            </div>
            <div class="text-right shrink-0">
              <div class="text-2xl font-black text-violet-600">{{ number_format($reward->points_cost) }}</div>
              <div class="text-xs text-slate-500">điểm</div>
            </div>
          </div>
          <p class="text-sm text-slate-600 min-h-12">{{ $reward->description }}</p>
          <div class="mt-4 space-y-1 text-sm text-slate-600">
            @if ($reward->discount_amount > 0)
              <p>Giảm: {{ number_format($reward->discount_amount) }}đ</p>
            @endif
            @if ($reward->shipping_discount_amount > 0)
              <p>Free ship: {{ number_format($reward->shipping_discount_amount) }}đ</p>
            @endif
            @if (!is_null($reward->stock))
              <p>Tồn: {{ $reward->stock }}</p>
            @endif
          </div>
          <div class="mt-5 flex gap-2">
            <a href="{{ route('rewards.show', $reward->reward_id) }}" class="w-full py-3 rounded-xl bg-slate-100 text-slate-900 font-semibold text-center hover:bg-slate-200 transition">Xem chi tiết</a>
            <button class="redeem-btn w-full py-3 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition" data-id="{{ $reward->reward_id }}">Đổi ngay</button>
          </div>
        </div>
      @empty
        <div class="col-span-full bg-white rounded-2xl border border-dashed p-10 text-center text-slate-500">Chưa có phần thưởng nào.</div>
      @endforelse
    </div>
  </div>
</div>

<div id="modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center">
    <h3 id="modal-title" class="text-2xl font-black text-slate-900">Kết quả</h3>
    <p id="modal-body" class="text-slate-600 mt-3"></p>
    <button onclick="closeModal()" class="mt-6 px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold">Đóng</button>
  </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
const modal = document.getElementById('modal');
const modalTitle = document.getElementById('modal-title');
const modalBody = document.getElementById('modal-body');

function openModal(title, body) {
  modalTitle.textContent = title;
  modalBody.textContent = body;
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeModal() {
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const filter = btn.dataset.filter;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('bg-slate-900','text-white'));
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.add('bg-white','border'));
    btn.classList.add('bg-slate-900','text-white');
    btn.classList.remove('bg-white','border');

    document.querySelectorAll('.reward-card').forEach(card => {
      const ok = filter === 'all' || card.dataset.type === filter || card.dataset.category === filter;
      card.style.display = ok ? '' : 'none';
    });
  });
});

document.querySelectorAll('.redeem-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const rewardId = btn.dataset.id;
    btn.disabled = true;
    btn.textContent = 'Đang xử lý...';

    try {
      const res = await fetch('{{ route('rewards.redeem') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ reward_id: rewardId })
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.message || 'Không thể đổi thưởng');
      openModal('Đổi thưởng thành công', `Mã đổi thưởng: ${data.data.redemption_code}. Điểm còn lại: ${data.data.remaining_points}`);
      setTimeout(() => window.location.reload(), 1500);
    } catch (e) {
      openModal('Có lỗi xảy ra', e.message);
    } finally {
      btn.disabled = false;
      btn.textContent = 'Đổi ngay';
    }
  });
});

document.getElementById('spin-btn')?.addEventListener('click', async () => {
  const btn = document.getElementById('spin-btn');
  btn.disabled = true;
  btn.textContent = 'Đang quay...';
  try {
    const res = await fetch('{{ route('rewards.spin') }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (!res.ok || !data.success) throw new Error(data.message || 'Không thể quay vòng may mắn');
    openModal('Bạn đã trúng thưởng', `${data.data.reward_name}. Điểm còn lại: ${data.data.remaining_points}`);
  } catch (e) {
    openModal('Quay thất bại', e.message);
  } finally {
    btn.disabled = false;
    btn.textContent = 'Quay ngay';
  }
});
</script>
@endpush
