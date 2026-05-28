@extends('layouts.app')
@section('title', 'Đổi thưởng - DIENMAYPRO')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
  <div class="max-w-7xl mx-auto px-4">
    @php $locale = app()->getLocale(); @endphp
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-5">
      <div>
        <h1 class="text-3xl font-extrabold text-slate-900">{{ $locale === 'en' ? 'Redeem Rewards' : 'Đổi thưởng' }}</h1>
        <p class="text-slate-600 mt-2">
          {{ $locale === 'en' 
             ? 'Use consumer points to redeem vouchers, gifts or spin the lucky wheel.' 
             : 'Dùng điểm tiêu dùng để đổi voucher, quà tặng hoặc quay vòng may mắn.' }}
        </p>
      </div>
      <!-- Ví điểm mặc định hiển thị ở phía trên bên phải -->
      <div class="flex items-center gap-3 shrink-0 flex-wrap">
        <a href="{{ route('rewards.history') }}" class="px-5 py-3 rounded-2xl border-2 border-slate-200 hover:border-indigo-600 hover:bg-indigo-50/50 font-bold text-sm text-slate-700 hover:text-indigo-600 flex items-center gap-2 transition shadow-sm h-14">
          <i class="fa-solid fa-clock-rotate-left text-lg"></i>
          {{ $locale === 'en' ? 'Redemption History' : 'Lịch sử đổi thưởng' }}
        </a>
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl px-6 py-3 shadow-lg flex items-center gap-4 border border-indigo-400/20 h-14">
          <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-md">
            <i class="fa-solid fa-wallet"></i>
          </div>
          <div>
            <p class="text-[10px] text-blue-100 font-bold uppercase tracking-wider leading-none">{{ $locale === 'en' ? 'Your Points' : 'Số điểm' }}</p>
            <p class="text-lg font-black mt-0.5" id="user-header-points">{{ number_format($balance) }} {{ $locale === 'en' ? 'p' : 'đ' }}</p>
          </div>
        </div>
      </div>
    </div>

    @php
      $showWheel = \App\Models\Setting::where('setting_key', 'show_lucky_wheel')->value('setting_value') ?? '1';
    @endphp
    @if ($showWheel === '1')
    <!-- Khu vực Vòng Quay May Mắn Hero Section -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-8 shadow-xl mb-8 bg-gradient-to-br from-white via-slate-50/40 to-indigo-50/20 relative overflow-hidden">
      <!-- Decor gradient mờ ở góc -->
      <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-indigo-400/10 blur-3xl pointer-events-none"></div>
      <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-violet-400/10 blur-3xl pointer-events-none"></div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <!-- Bên trái: Đĩa quay to nổi bật -->
        <div class="lg:col-span-5 flex flex-col items-center justify-center select-none">
          <!-- Bộ chuyển đổi Vòng quay may mắn -->
          <div id="frontend-wheel-tabs-container" class="flex items-center gap-1 bg-slate-200/60 p-1 rounded-2xl w-full mb-5 max-w-md border border-slate-300/30 flex-wrap">
            @foreach($wheels as $idx => $w)
              <button type="button" onclick="switchWheelTab('{{ $w['key'] }}')" id="tab-{{ $w['key'] }}" class="flex-1 min-w-[80px] py-2 rounded-xl text-xs font-black transition {{ $idx === 0 ? 'bg-white text-slate-800 shadow-md border border-slate-200/50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">{{ $locale === 'en' ? $w['name_en'] : $w['name'] }}</button>
            @endforeach
          </div>

          <div class="relative w-[380px] h-[380px] flex items-center justify-center shrink-0">
            <!-- Kim chỉ 3D ở đỉnh đĩa -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-2 z-10 w-0 h-0 border-l-[16px] border-l-transparent border-r-[16px] border-r-transparent border-t-[28px] border-t-rose-600 filter drop-shadow-[0_4px_6px_rgba(0,0,0,0.2)]"></div>
            
            <!-- Canvas vẽ đĩa quay (Tăng size lên 360) -->
            <canvas id="wheel-canvas" width="360" height="360" class="rounded-full shadow-2xl border-4 border-slate-900 transition-transform duration-[5000ms] ease-[cubic-bezier(0.1,0.8,0.2,1)] bg-white"></canvas>
            
            <!-- Nút bấm quay ở tâm (Đẹp hơn, nổi hơn) -->
            <button id="wheel-center-btn" class="absolute w-14 h-14 bg-slate-900 text-white rounded-full flex items-center justify-center font-black text-xs shadow-xl border-4 border-white hover:bg-violet-600 hover:scale-105 transition active:scale-95 z-20 tracking-wider">
              SPIN
            </button>
          </div>
          <p class="text-xs text-slate-400 mt-4 italic text-center"><i class="fa-solid fa-circle-info mr-1"></i>{{ $locale === 'en' ? 'Click "SPIN" at the center or "SPIN NOW" to play.' : 'Bấm nút "SPIN" ở tâm hoặc "QUAY NGAY" để chơi.' }}</p>
        </div>

        <!-- Bên phải: Thông tin & Trải nghiệm -->
        <div class="lg:col-span-7 flex flex-col justify-between h-full space-y-6">
          <div class="space-y-4">
            <div class="flex items-center gap-2">
              @php
                $firstWheel = count($wheels) > 0 ? $wheels[0] : ['key' => 'standard', 'name' => 'Vòng Thường', 'name_en' => 'Standard Wheel', 'points_cost' => 10, 'min_rank' => 'none'];
                $firstWheelCost = $firstWheel['points_cost'];
                $firstMinRank = $firstWheel['min_rank'] ?? 'none';
                $rankNames = [
                  'Dong' => $locale === 'en' ? 'Bronze' : 'Đồng',
                  'Bac' => $locale === 'en' ? 'Silver' : 'Bạc',
                  'Vang' => $locale === 'en' ? 'Gold' : 'Vàng',
                  'KimCuong' => $locale === 'en' ? 'Diamond' : 'Kim Cương',
                ];
              @endphp
              <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-violet-100 text-violet-700 uppercase tracking-wider animate-pulse" id="wheel-badge-title">{{ $locale === 'en' ? $firstWheel['name_en'] : $firstWheel['name'] }}</span>
              <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700" id="wheel-cost-badge">{{ $locale === 'en' ? "$firstWheelCost points / spin" : "$firstWheelCost điểm / lượt" }}</span>
              <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 {{ $firstMinRank === 'none' ? 'hidden' : '' }}" id="wheel-rank-badge">
                <i class="fa-solid fa-crown mr-1"></i> {{ $locale === 'en' ? 'Rank: ' : 'Hạng: ' }}{{ $rankNames[$firstMinRank] ?? $firstMinRank }}
              </span>
            </div>
            
            <h2 class="text-3xl lg:text-4xl font-black text-slate-900 leading-tight">
              {{ $locale === 'en' ? 'Spin The Lucky Wheel' : 'Quay Vòng May Mắn' }} <br>
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-fuchsia-600">{{ $locale === 'en' ? 'Win Giant Prizes!' : 'Trúng Quà Cực Khủng!' }}</span>
            </h2>
            
            <p class="text-slate-600 text-sm max-w-xl">
              {{ $locale === 'en' 
                 ? 'Use consumer points accumulated from orders to try your luck. Many discount vouchers and free shipping codes are waiting for you!' 
                 : 'Sử dụng điểm tiêu dùng tích lũy được từ các đơn hàng để thử vận may của bạn. Rất nhiều Voucher giảm giá, mã miễn phí vận chuyển đang chờ bạn đón lấy!' }}
            </p>
          </div>

          <!-- Khu vực hiển thị Điểm của người dùng -->
          <div class="flex flex-col sm:flex-row items-center gap-4 bg-slate-50/80 p-5 rounded-2xl border border-slate-100 max-w-md">
            <div class="flex-1 text-center sm:text-left">
              <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $locale === 'en' ? 'Current Points Balance' : 'Số điểm tích lũy hiện tại' }}</p>
              <p class="text-3xl font-black text-indigo-600 mt-1" id="user-points-display">{{ number_format($balance) }} {{ $locale === 'en' ? 'points' : 'điểm' }}</p>
            </div>
            <a href="{{ route('rewards.history') }}" class="px-4 py-2 text-xs font-extrabold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition flex items-center gap-1 shrink-0">
              <i class="fa-solid fa-clock-rotate-left"></i> {{ $locale === 'en' ? 'Spin History' : 'Lịch sử trúng giải' }}
            </a>
          </div>

          <!-- Nút hành động chính -->
          <div class="flex items-center gap-4">
            <button id="spin-btn" class="px-8 py-4 rounded-2xl bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 text-white font-extrabold text-base transition shadow-lg shadow-violet-100 hover:shadow-violet-200 active:scale-95 flex items-center gap-2">
              <i class="fa-solid fa-spinner fa-spin hidden" id="spin-btn-spinner"></i>
              <i class="fa-solid fa-dharmachakra"></i> {{ $locale === 'en' ? "SPIN NOW ($firstWheelCost POINTS)" : "QUAY NGAY ($firstWheelCost ĐIỂM)" }}
            </button>
          </div>
        </div>
      </div>
    </div>
    @endif

    <div class="mb-6 flex flex-wrap gap-2">
      <button class="filter-btn px-4 py-2 rounded-full bg-slate-900 text-white text-sm" data-filter="all">{{ $locale === 'en' ? 'All' : 'Tất cả' }}</button>
      <button class="filter-btn px-4 py-2 rounded-full bg-white border text-sm" data-filter="voucher">{{ $locale === 'en' ? 'Voucher' : 'Voucher' }}</button>
      <button class="filter-btn px-4 py-2 rounded-full bg-white border text-sm" data-filter="shipping">{{ $locale === 'en' ? 'Free ship' : 'Free ship' }}</button>
      <button class="filter-btn px-4 py-2 rounded-full bg-white border text-sm" data-filter="product">{{ $locale === 'en' ? 'Gifts' : 'Quà tặng' }}</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="reward-grid">
      @forelse ($catalog as $reward)
        <div class="reward-card bg-white rounded-2xl border border-slate-200 shadow-sm p-5" data-id="{{ $reward->reward_id }}" data-type="{{ $reward->reward_type }}" data-category="{{ $reward->reward_category }}">
          <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex items-start gap-3 min-w-0">
              <div class="w-16 h-16 rounded-2xl overflow-hidden bg-slate-100 shrink-0">
                @php
                  $img = $reward->thumbnail_path ?? $reward->image_path ?? null;
                @endphp
                @if($img)
                  <img src="{{ asset('storage/'.$img) }}" class="w-full h-full object-cover" alt="{{ $reward->name }}">
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
              <div class="text-xs text-slate-500">{{ $locale === 'en' ? 'points' : 'điểm' }}</div>
            </div>
          </div>
          <p class="text-sm text-slate-600 min-h-12">{{ $reward->description }}</p>
          <div class="mt-4 space-y-1 text-sm text-slate-600">
            @php
              $meta = $reward->metadata ?? [];
              $minRank = $meta['min_rank'] ?? 'none';
            @endphp
            @if ($reward->requires_rank_check && $minRank !== 'none')
              @php
                $rankNamesMap = [
                  'Dong' => $locale === 'en' ? 'Bronze' : 'Đồng',
                  'Bac' => $locale === 'en' ? 'Silver' : 'Bạc',
                  'Vang' => $locale === 'en' ? 'Gold' : 'Vàng',
                  'KimCuong' => $locale === 'en' ? 'Diamond' : 'Kim Cương',
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
                <span class="text-xs text-slate-500 font-bold uppercase">{{ $locale === 'en' ? 'Required Rank:' : 'Yêu cầu hạng:' }}</span>
                <span class="px-2 py-0.5 rounded-md border text-[10px] font-black tracking-wider uppercase {{ $rankColor }}">{{ $rankName }}</span>
              </p>
            @endif
            @if ($reward->discount_amount > 0)
              <p>{{ $locale === 'en' ? 'Discount:' : 'Giảm:' }} {{ number_format($reward->discount_amount) }}đ</p>
            @endif
            @if ($reward->shipping_discount_amount > 0)
              <p>{{ $locale === 'en' ? 'Free ship:' : 'Free ship:' }} {{ number_format($reward->shipping_discount_amount) }}đ</p>
            @endif
            @if (!is_null($reward->stock))
              <p>{{ $locale === 'en' ? 'Stock:' : 'Tồn:' }} {{ $reward->stock }}</p>
            @endif
          </div>
          <div class="mt-5 flex gap-2">
            <a href="{{ route('rewards.show', $reward->reward_id) }}" class="w-full py-3 rounded-xl bg-slate-100 text-slate-900 font-semibold text-center hover:bg-slate-200 transition">{{ $locale === 'en' ? 'Details' : 'Xem chi tiết' }}</a>
            <button class="redeem-btn w-full py-3 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition" 
              data-id="{{ $reward->reward_id }}"
              data-min-rank="{{ $minRank }}"
              data-requires-rank-check="{{ $reward->requires_rank_check ? 1 : 0 }}">{{ $locale === 'en' ? 'Redeem' : 'Đổi ngay' }}</button>
          </div>
        </div>
      @empty
        <div class="col-span-full bg-white rounded-2xl border border-dashed p-10 text-center text-slate-500">{{ $locale === 'en' ? 'No rewards available.' : 'Chưa có phần thưởng nào.' }}</div>
      @endforelse
    </div>
  </div>
</div>

<div id="modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center">
    <h3 id="modal-title" class="text-2xl font-black text-slate-900">{{ $locale === 'en' ? 'Result' : 'Kết quả' }}</h3>
    <p id="modal-body" class="text-slate-600 mt-3"></p>
    <button onclick="closeModal()" class="mt-6 px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold">{{ $locale === 'en' ? 'Close' : 'Đóng' }}</button>
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

const trans = {
  vi: {
    points: ' điểm',
    pointsCost: ' điểm / lượt',
    spinNow: 'QUAY NGAY',
    pointsWord: 'ĐIỂM',
    noReward: 'Chưa cấu hình quà cho vòng này',
    congrats: 'Chúc mừng bạn!',
    wonMsg: 'Bạn đã trúng: {reward}. Điểm còn lại: {points} điểm',
    failed: 'Quay thất bại',
    configError: 'Có lỗi cấu hình vòng quay phần thưởng.',
    standardTitle: 'Vòng quay Thường',
    silverTitle: 'Vòng quay Bạc',
    goldTitle: 'Vòng quay Vàng',
    redeemSuccess: 'Đổi thưởng thành công',
    redeemSuccessMsg: 'Mã đổi thưởng: {code}. Điểm còn lại: {points} điểm',
    errorOccurred: 'Có lỗi xảy ra',
    redeemBtn: 'Đổi ngay',
    redeemingBtn: 'Đang xử lý...',
    cannotRedeem: 'Không thể đổi thưởng',
  },
  en: {
    points: ' points',
    pointsCost: ' points / spin',
    spinNow: 'SPIN NOW',
    pointsWord: 'POINTS',
    noReward: 'No rewards configured for this wheel',
    congrats: 'Congratulations!',
    wonMsg: 'You won: {reward}. Remaining points: {points} points',
    failed: 'Spin failed',
    configError: 'Rewards wheel configuration error.',
    standardTitle: 'Standard Wheel',
    silverTitle: 'Silver Wheel',
    goldTitle: 'Gold Wheel',
    redeemSuccess: 'Redemption successful',
    redeemSuccessMsg: 'Redemption code: {code}. Remaining points: {points} points',
    errorOccurred: 'An error occurred',
    redeemBtn: 'Redeem Now',
    redeemingBtn: 'Processing...',
    cannotRedeem: 'Cannot redeem reward',
  }
};
const lang = '{{ app()->getLocale() }}' === 'en' ? 'en' : 'vi';
const t = trans[lang];

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
    const requiresRankCheck = parseInt(btn.dataset.requiresRankCheck) || 0;
    const minRank = btn.dataset.minRank || 'none';
    
    if (requiresRankCheck && minRank !== 'none') {
      const userRankVal = rankOrder[userRank] || 1;
      const minRankVal = rankOrder[minRank] || 0;
      if (userRankVal < minRankVal) {
        const displayRankName = rankNamesMap[minRank] || minRank;
        openModal(t.cannotRedeem, lang === 'en' ? `You need to be at least ${displayRankName} rank to redeem this reward!` : `Bạn cần đạt hạng từ ${displayRankName} trở lên mới có thể đổi phần thưởng này!`);
        return;
      }
    }

    btn.disabled = true;
    btn.textContent = t.redeemingBtn;

    try {
      const res = await fetch('{{ route('rewards.redeem') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ reward_id: rewardId })
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.message || t.cannotRedeem);
      const successMsg = t.redeemSuccessMsg.replace('{code}', data.data.redemption_code).replace('{points}', data.data.remaining_points);
      openModal(t.redeemSuccess, successMsg);
      setTimeout(() => window.location.reload(), 1500);
    } catch (e) {
      openModal(t.errorOccurred, e.message);
    } finally {
      btn.disabled = false;
      btn.textContent = t.redeemBtn;
    }
  });
});

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
  'Dong': lang === 'en' ? 'Bronze' : 'Đồng',
  'Bac': lang === 'en' ? 'Silver' : 'Bạc',
  'Vang': lang === 'en' ? 'Gold' : 'Vàng',
  'KimCuong': lang === 'en' ? 'Diamond' : 'Kim Cương',
  'none': lang === 'en' ? 'No requirement' : 'Không yêu cầu'
};

// Vẽ Canvas Vòng Quay
const luckyWheels = @json($wheels);
const canvas = document.getElementById('wheel-canvas');
const ctx = canvas ? canvas.getContext('2d') : null;
const allWheelPrizes = @json($catalog->where('reward_type', 'wheel_prize')->values());
const wheelColors = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#14b8a6'];
let currentWheelTab = luckyWheels.length > 0 ? luckyWheels[0].key : 'standard';
let segments = [];
let anglePerSegment = 0;
let currentRotation = 0;
let isSpinning = false;
let ledTick = 0;

function buildSegments() {
  const activePrizes = allWheelPrizes.filter(prize => {
    const meta = prize.metadata ?? {};
    const type = meta.wheel_type ?? 'standard';
    return type === currentWheelTab;
  });

  if (activePrizes.length === 0) {
    segments = [];
    anglePerSegment = 0;
    return;
  }

  let temp = [...activePrizes];
  while (temp.length < 6) {
    temp = temp.concat(activePrizes);
  }

  segments = temp.map((prize, idx) => ({
    id: prize.reward_id,
    name: prize.name.replace('Vòng quay - ', '').substring(0, 12),
    color: wheelColors[idx % wheelColors.length],
    textColor: '#ffffff'
  }));

  anglePerSegment = (2 * Math.PI) / segments.length;
}

function switchWheelTab(tab) {
  if (isSpinning) return;
  currentWheelTab = tab;

  // Cập nhật giao diện tab
  luckyWheels.forEach(w => {
    const btn = document.getElementById(`tab-${w.key}`);
    if (btn) {
      if (w.key === tab) {
        btn.className = "flex-1 py-2 rounded-xl text-xs font-black transition bg-white text-slate-800 shadow-md border border-slate-200/50";
      } else {
        btn.className = "flex-1 py-2 rounded-xl text-xs font-black transition text-slate-500 hover:text-slate-800 hover:bg-slate-50";
      }
    }
  });

  // Cập nhật badge & chi phí tương ứng
  const matched = luckyWheels.find(x => x.key === tab);
  let cost = matched ? matched.points_cost : 10;
  let badgeTitle = matched ? (lang === 'en' ? matched.name_en : matched.name) : (lang === 'en' ? 'Standard Wheel' : 'Vòng quay Thường');

  document.getElementById('wheel-badge-title').textContent = badgeTitle;
  document.getElementById('wheel-cost-badge').textContent = `${cost}${t.pointsCost}`;
  
  // Cập nhật hạng tối thiểu yêu cầu
  const minRank = matched ? (matched.min_rank || 'none') : 'none';
  const rankBadge = document.getElementById('wheel-rank-badge');
  if (rankBadge) {
    if (minRank === 'none') {
      rankBadge.classList.add('hidden');
    } else {
      rankBadge.classList.remove('hidden');
      const displayRankName = rankNamesMap[minRank] || minRank;
      rankBadge.innerHTML = `<i class="fa-solid fa-crown mr-1"></i> ${lang === 'en' ? 'Rank: ' : 'Hạng: '}${displayRankName}`;
    }
  }
  
  const spinBtn = document.getElementById('spin-btn');
  if (spinBtn) {
    spinBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin hidden" id="spin-btn-spinner"></i><i class="fa-solid fa-dharmachakra"></i> ${t.spinNow} (${cost} ${t.pointsWord})`;
  }

  // Reset góc quay đĩa
  currentRotation = 0;
  if (canvas) canvas.style.transform = `rotate(0deg)`;

  buildSegments();
  drawWheel();
}

setInterval(() => {
  ledTick = (ledTick + 1) % 2;
  if (canvas) drawWheel();
}, 280);

function drawWheel() {
  if (!canvas || !ctx) return;
  const size = canvas.width;
  const center = size / 2;
  const radius = center - 8;

  ctx.clearRect(0, 0, size, size);

  if (segments.length === 0) {
    ctx.beginPath();
    ctx.arc(center, center, radius, 0, 2 * Math.PI);
    ctx.fillStyle = '#f8fafc';
    ctx.fill();
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 4;
    ctx.stroke();

    ctx.fillStyle = '#94a3b8';
    ctx.font = 'bold 12px sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(t.noReward, center, center);
    return;
  }

  // Vẽ các sector
  segments.forEach((seg, i) => {
    const startAngle = i * anglePerSegment;
    const endAngle = startAngle + anglePerSegment;

    ctx.beginPath();
    ctx.moveTo(center, center);
    ctx.arc(center, center, radius - 6, startAngle, endAngle);
    ctx.closePath();

    // Tạo hiệu ứng chuyển sắc Radial Gradient 3D sang trọng
    const grad = ctx.createRadialGradient(center, center, 10, center, center, radius - 6);
    if (seg.color === '#ef4444') {
      grad.addColorStop(0, '#ff8787');
      grad.addColorStop(1, '#c92a2a');
    } else if (seg.color === '#3b82f6') {
      grad.addColorStop(0, '#74c0fc');
      grad.addColorStop(1, '#1c7ed6');
    } else if (seg.color === '#10b981') {
      grad.addColorStop(0, '#8ce99a');
      grad.addColorStop(1, '#2b8a3e');
    } else if (seg.color === '#f59e0b') {
      grad.addColorStop(0, '#ffe066');
      grad.addColorStop(1, '#d9480f');
    } else {
      grad.addColorStop(0, '#f8fafc');
      grad.addColorStop(1, '#cbd5e1');
    }
    
    ctx.fillStyle = grad;
    ctx.fill();

    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 1.5;
    ctx.stroke();

    // Vẽ chữ cho sector
    ctx.save();
    ctx.translate(center, center);
    ctx.rotate(startAngle + anglePerSegment / 2);
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';
    
    // Đổ bóng cho chữ dễ đọc
    ctx.shadowColor = 'rgba(0, 0, 0, 0.4)';
    ctx.shadowBlur = 4;
    ctx.fillStyle = seg.textColor;
    ctx.font = 'bold 11px sans-serif';
    ctx.fillText(seg.name, radius - 20, 0);
    ctx.restore();
  });

  // Vòng tròn viền ngoài mạ vàng kim (Gold Metallic Rim)
  const rimGrad = ctx.createRadialGradient(center, center, radius - 10, center, center, radius);
  rimGrad.addColorStop(0, '#b58928');
  rimGrad.addColorStop(0.3, '#f9d976');
  rimGrad.addColorStop(0.7, '#e9b646');
  rimGrad.addColorStop(1, '#8a5a00');
  
  ctx.beginPath();
  ctx.arc(center, center, radius - 5, 0, 2 * Math.PI);
  ctx.strokeStyle = rimGrad;
  ctx.lineWidth = 10;
  ctx.stroke();

  // Khuyên tròn bảo vệ viền
  ctx.beginPath();
  ctx.arc(center, center, radius, 0, 2 * Math.PI);
  ctx.strokeStyle = '#2d1e03';
  ctx.lineWidth = 2;
  ctx.stroke();

  // Vẽ các chấm LED lấp lánh chạy xung quanh viền
  const numLeds = 24;
  const ledAngle = (2 * Math.PI) / numLeds;
  for (let j = 0; j < numLeds; j++) {
    const angle = j * ledAngle;
    const x = center + (radius - 5) * Math.cos(angle);
    const y = center + (radius - 5) * Math.sin(angle);
    
    ctx.beginPath();
    ctx.arc(x, y, 3.5, 0, 2 * Math.PI);
    
    if ((j + ledTick) % 2 === 0) {
      ctx.fillStyle = '#ffffb3'; // Vàng neon sáng
      ctx.shadowColor = '#f59e0b';
      ctx.shadowBlur = 6;
    } else {
      ctx.fillStyle = '#d9480f'; // Đỏ cam tối
      ctx.shadowBlur = 0;
    }
    ctx.fill();
    ctx.shadowBlur = 0;
  }
  
  // Vòng tròn trung tâm 3D Gold
  const centerGrad = ctx.createRadialGradient(center, center, 0, center, center, 24);
  centerGrad.addColorStop(0, '#ffffff');
  centerGrad.addColorStop(0.4, '#f9d976');
  centerGrad.addColorStop(1, '#b58928');
  
  ctx.beginPath();
  ctx.arc(center, center, 22, 0, 2 * Math.PI);
  ctx.fillStyle = centerGrad;
  ctx.fill();
  
  ctx.strokeStyle = '#8a5a00';
  ctx.lineWidth = 2;
  ctx.stroke();
}

// Khởi động segments ban đầu
buildSegments();
if (canvas) {
  drawWheel();
}

async function startSpin() {
  if (isSpinning || segments.length === 0) return;

  const matched = luckyWheels.find(x => x.key === currentWheelTab);
  let cost = matched ? matched.points_cost : 10;
  
  // Kiểm tra hạng thành viên tối thiểu trước khi quay
  const minRank = matched ? (matched.min_rank || 'none') : 'none';
  const userRankVal = rankOrder[userRank] || 1;
  const minRankVal = rankOrder[minRank] || 0;
  
  if (userRankVal < minRankVal) {
    const displayRankName = rankNamesMap[minRank] || minRank;
    openModal(t.failed || (lang === 'en' ? 'Spin failed' : 'Quay thất bại'), lang === 'en' ? `You need to be at least ${displayRankName} rank to spin this wheel!` : `Bạn cần đạt hạng từ ${displayRankName} trở lên mới có thể quay vòng quay này!`);
    return;
  }
  
  const userHeaderPointsEl = document.getElementById('user-header-points');
  let currentBalance = 0;
  if (userHeaderPointsEl) {
    currentBalance = parseInt(userHeaderPointsEl.textContent.replace(/[^0-9]/g, '')) || 0;
  }
  
  if (currentBalance < cost) {
    openModal(t.failed || (lang === 'en' ? 'Spin failed' : 'Quay thất bại'), lang === 'en' ? 'You do not have enough points to spin this wheel!' : 'Bạn không đủ điểm để quay vòng quay này!');
    return;
  }

  const btn = document.getElementById('spin-btn');
  const btnSpinner = document.getElementById('spin-btn-spinner');
  const centerBtn = document.getElementById('wheel-center-btn');

  isSpinning = true;
  if (btn) btn.disabled = true;
  if (centerBtn) centerBtn.disabled = true;
  if (btnSpinner) btnSpinner.classList.remove('hidden');

  try {
    const res = await fetch('{{ route('rewards.spin') }}', {
      method: 'POST',
      headers: { 
        'X-CSRF-TOKEN': csrf, 
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ wheel_type: currentWheelTab })
    });
    const data = await res.json();
    if (!res.ok || !data.success) throw new Error(data.message || (lang === 'en' ? 'Cannot spin the lucky wheel' : 'Không thể quay vòng may mắn'));

    // Lấy ID phần thưởng trúng giải
    const wonRewardId = parseInt(data.data.result.reward_id);
    
    // Tìm các chỉ số ô có ID khớp
    const matchingIndices = [];
    segments.forEach((seg, index) => {
      if (seg.id === wonRewardId) {
        matchingIndices.push(index);
      }
    });

    if (matchingIndices.length === 0) {
      throw new Error(t.configError);
    }

    // Chọn ngẫu nhiên một ô trúng giải hợp lệ
    const targetSegmentIndex = matchingIndices[Math.floor(Math.random() * matchingIndices.length)];

    // Tính toán góc quay
    const extraSpins = 6;
    const centerAngleRad = (targetSegmentIndex * anglePerSegment) + (anglePerSegment / 2);
    const centerAngleDeg = (centerAngleRad * 180) / Math.PI;

    // Để ô trúng giải dừng ở kim chỉ phía TRÊN (góc 270 độ)
    let targetRotationDeg = 270 - centerAngleDeg;

    // Đảm bảo xoay tiến lên phía trước theo chiều kim đồng hồ
    const additionalDeg = (extraSpins * 360) + (targetRotationDeg - (currentRotation % 360) + 360) % 360;
    currentRotation += additionalDeg;

    // Áp dụng CSS để xoay đĩa
    if (canvas) {
      canvas.style.transform = `rotate(${currentRotation}deg)`;
    }

    // Chờ kết thúc xoay
    setTimeout(() => {
      const wonMsgFormatted = t.wonMsg.replace('{reward}', data.data.reward_name).replace('{points}', data.data.remaining_points);
      openModal(t.congrats, wonMsgFormatted);
      
      // Cập nhật điểm trên UI
      const pointsDisplay = document.getElementById('user-points-display') || document.querySelector('.text-3xl.font-black.text-blue-600');
      if (pointsDisplay) {
        pointsDisplay.textContent = new Intl.NumberFormat(lang === 'en' ? 'en-US' : 'vi-VN').format(data.data.remaining_points) + t.points;
      }
      const headerPoints = document.getElementById('user-header-points');
      if (headerPoints) {
        headerPoints.textContent = new Intl.NumberFormat(lang === 'en' ? 'en-US' : 'vi-VN').format(data.data.remaining_points) + t.points;
      }

      isSpinning = false;
      if (btn) btn.disabled = false;
      if (centerBtn) centerBtn.disabled = false;
      if (btnSpinner) btnSpinner.classList.add('hidden');
    }, 5100);

  } catch (e) {
    openModal(t.failed, e.message);
    isSpinning = false;
    if (btn) btn.disabled = false;
    if (centerBtn) centerBtn.disabled = false;
    if (btnSpinner) btnSpinner.classList.add('hidden');
  }
}

document.getElementById('spin-btn')?.addEventListener('click', startSpin);
document.getElementById('wheel-center-btn')?.addEventListener('click', startSpin);
</script>
@endpush
