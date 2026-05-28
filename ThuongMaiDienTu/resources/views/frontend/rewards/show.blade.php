@extends('layouts.app')
@section('title', $reward->name)

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50 py-10">
  <div class="max-w-5xl mx-auto px-4">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
      <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        @php
          $img = $reward->thumbnail_path ?? $reward->image_path ?? null;
        @endphp
        @if($img)
          <div class="relative">
            <img src="{{ asset('storage/'.$img) }}" class="w-full h-[420px] object-cover" alt="{{ $reward->name }}">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-transparent"></div>
          </div>
        @else
          <div class="relative h-[420px] bg-gradient-to-br from-indigo-500 via-violet-600 to-fuchsia-600">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,.25),transparent_35%)]"></div>
          </div>
        @endif
      </div>
      <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">{{ $reward->reward_category }}</span>
        <h1 class="text-3xl font-black text-slate-900 mt-3">{{ $reward->name }}</h1>
        <p class="text-slate-600 mt-3">{{ $reward->description }}</p>
        <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
          <div class="p-4 rounded-2xl bg-slate-50">
            <div class="text-slate-500">Giá điểm</div>
            <div class="text-2xl font-black text-violet-600">{{ number_format($reward->points_cost) }}</div>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50">
            <div class="text-slate-500">Loại</div>
            <div class="text-lg font-bold text-slate-900">{{ $reward->reward_type }}</div>
          </div>
        </div>
        <div class="mt-6 space-y-2 text-sm text-slate-600">
          @php
            $meta = $reward->metadata ?? [];
            $minRank = $meta['min_rank'] ?? 'none';
          @endphp
          @if ($reward->requires_rank_check && $minRank !== 'none')
            @php
              $rankNamesMap = [
                'Dong' => 'Đồng',
                'Bac' => 'Bạc',
                'Vang' => 'Vàng',
                'KimCuong' => 'Kim Cương',
              ];
              $rankColorsMap = [
                'Dong' => 'bg-amber-100 text-amber-800 border-amber-200',
                'Bac' => 'bg-slate-100 text-slate-800 border-slate-200',
                'Vang' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'KimCuong' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
              ];
              $rankName = $rankNamesMap[$minRank] ?? $minRank;
              $rankColor = $rankColorsMap[$minRank] ?? 'bg-slate-100 text-slate-800';
            @endphp
            <p class="flex items-center gap-1.5 mb-2">
              <span class="text-slate-500 font-bold uppercase text-xs">Yêu cầu hạng:</span>
              <span class="px-2 py-0.5 rounded-md border text-[10px] font-black tracking-wider uppercase {{ $rankColor }}">{{ $rankName }}</span>
            </p>
          @endif
          @if($reward->discount_amount)
            <p>Giảm tiền: {{ number_format($reward->discount_amount) }}đ</p>
          @endif
          @if($reward->shipping_discount_amount)
            <p>Giảm ship: {{ number_format($reward->shipping_discount_amount) }}đ</p>
          @endif
          @if(!is_null($reward->stock))
            <p>Tồn kho: {{ $reward->stock }}</p>
          @endif
        </div>
        <div class="mt-8 flex gap-3">
          <a href="{{ route('rewards.index') }}" class="px-5 py-3 rounded-xl bg-slate-900 text-white font-semibold">Quay lại</a>
          <button class="redeem-btn-show px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold transition"
            data-id="{{ $reward->reward_id }}"
            data-min-rank="{{ $minRank }}"
            data-requires-rank-check="{{ $reward->requires_rank_check ? 1 : 0 }}">Đổi ngay</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="result-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center">
    <h3 id="result-modal-title" class="text-2xl font-black text-slate-900">Kết quả</h3>
    <p id="result-modal-body" class="text-slate-600 mt-3"></p>
    <button onclick="closeResultModal()" class="mt-6 px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold">Đóng</button>
  </div>
</div>
@endsection

@push('scripts')
<script>
const resultModal = document.getElementById('result-modal');
const resultTitle = document.getElementById('result-modal-title');
const resultBody = document.getElementById('result-modal-body');

function openResultModal(title, body) {
  resultTitle.textContent = title;
  resultBody.textContent = body;
  resultModal.classList.remove('hidden');
  resultModal.classList.add('flex');
}
function closeResultModal() {
  resultModal.classList.add('hidden');
  resultModal.classList.remove('flex');
}

const userRank = '{{ Auth::check() ? Auth::user()->member_tier : 'Dong' }}';
const rankOrder = {
  'none': 0,
  'Dong': 1,
  'Bronze': 1,
  'Bac': 2,
  'Silver': 2,
  'Vang': 3,
  'Gold': 3,
  'KimCuong': 4,
  'Diamond': 4
};
const rankNamesMap = {
  'Dong': 'Đồng',
  'Bac': 'Bạc',
  'Vang': 'Vàng',
  'KimCuong': 'Kim Cương',
  'none': 'Không yêu cầu'
};

document.querySelector('.redeem-btn-show')?.addEventListener('click', async (e) => {
  const btn = e.currentTarget;
  const rewardId = btn.dataset.id;
  const requiresRankCheck = parseInt(btn.dataset.requiresRankCheck) || 0;
  const minRank = btn.dataset.minRank || 'none';
  
  if (requiresRankCheck && minRank !== 'none') {
    const userRankVal = rankOrder[userRank] || 1;
    const minRankVal = rankOrder[minRank] || 0;
    if (userRankVal < minRankVal) {
      const displayRankName = rankNamesMap[minRank] || minRank;
      openResultModal('Không thể đổi thưởng', `Bạn cần đạt hạng từ ${displayRankName} trở lên mới có thể đổi phần thưởng này!`);
      return;
    }
  }

  btn.disabled = true;
  btn.textContent = 'Đang xử lý...';

  try {
    const res = await fetch('{{ route('rewards.redeem') }}', {
      method: 'POST',
      headers: { 
        'X-CSRF-TOKEN': '{{ csrf_token() }}', 
        'Accept': 'application/json', 
        'Content-Type': 'application/json' 
      },
      body: JSON.stringify({ reward_id: rewardId })
    });
    const data = await res.json();
    if (!res.ok || !data.success) throw new Error(data.message || 'Không thể đổi thưởng');
    
    openResultModal('Đổi thưởng thành công', `Mã đổi thưởng: ${data.data.redemption_code}. Điểm còn lại: ${data.data.remaining_points} điểm`);
    setTimeout(() => window.location.href = '{{ route('rewards.index') }}', 2000);
  } catch (err) {
    openResultModal('Có lỗi xảy ra', err.message);
  } finally {
    btn.disabled = false;
    btn.textContent = 'Đổi ngay';
  }
});
</script>
@endpush
