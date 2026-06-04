@extends('layouts.app')
@section('title', 'Thanh toán - DIENMAYPRO')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: false,
        }
    }
</script>
<style>
.pay-radio:checked ~ .pay-label { border-color:#2563eb; background:#eff6ff; }
.pay-radio:checked ~ .pay-label .dot-outer { border-color:#2563eb; }
.pay-radio:checked ~ .pay-label .dot-inner { opacity:1; }
.method-panel { display:none; }
.method-panel.active { display:block; }
@keyframes scanLine {
  0%,100%{top:0;opacity:0} 50%{top:calc(100% - 4px);opacity:1}
}
.qr-scan-line { animation: scanLine 2.5s ease-in-out infinite; }
.step-done { background:#16a34a!important; }
</style>
@endpush

@section('content')
@php
    $prefilledName = '';
    $prefilledPhone = '';
    $prefilledProvince = '';
    $prefilledAddress = '';
    if (Auth::check()) {
        $user = Auth::user();
        $prefilledName = $user->full_name;
        $prefilledPhone = $user->phone_number;
        
        $addresses = $user->addresses()->orderByDesc('is_default')->get();
      $defaultAddress = $addresses->where('is_default', 1)->first() 
        ?? $addresses->first();
      // If a saved_address_id is passed via query (from cart page), prefer it
      $requestedSavedId = request()->query('saved_address_id');
      if ($requestedSavedId) {
        $requested = $addresses->where('id', $requestedSavedId)->first();
        if ($requested) {
          $defaultAddress = $requested;
        }
      }
      $selectedAddressId = $defaultAddress->id ?? null;
            
        if ($defaultAddress) {
            // Lưu ý: $defaultAddress->name là nhãn địa chỉ ("Nhà riêng", "Văn phòng"),
            // KHÔNG phải tên người nhận. Tên người nhận lấy từ users.full_name.
            
            $addrParts = [];
            if (!empty($defaultAddress->street)) {
                $addrParts[] = $defaultAddress->street;
            }
            if (!empty($defaultAddress->ward)) {
                $addrParts[] = $defaultAddress->ward;
            }
            if (!empty($defaultAddress->district)) {
                $addrParts[] = $defaultAddress->district;
            }
            $prefilledAddress = implode(', ', $addrParts);
            
            if (!empty($defaultAddress->city)) {
                $cityStr = mb_strtolower($defaultAddress->city);
                if (str_contains($cityStr, 'hồ chí minh') || str_contains($cityStr, 'hcm')) {
                    $prefilledProvince = 'hcm';
                } elseif (str_contains($cityStr, 'hà nội') || str_contains($cityStr, 'hn')) {
                    $prefilledProvince = 'hn';
                } elseif (str_contains($cityStr, 'bình dương')) {
                    $prefilledProvince = 'bd';
                } elseif (str_contains($cityStr, 'đồng nai')) {
                    $prefilledProvince = 'dnai';
                } elseif (str_contains($cityStr, 'long an')) {
                    $prefilledProvince = 'la';
                } elseif (str_contains($cityStr, 'tiền giang')) {
                    $prefilledProvince = 'tg';
                } elseif (str_contains($cityStr, 'vũng tàu') || str_contains($cityStr, 'bà rịa')) {
                    $prefilledProvince = 'vt';
                } elseif (str_contains($cityStr, 'bắc ninh')) {
                    $prefilledProvince = 'bn';
                } elseif (str_contains($cityStr, 'hưng yên')) {
                    $prefilledProvince = 'hy';
                } elseif (str_contains($cityStr, 'hà nam')) {
                    $prefilledProvince = 'hnam';
                } elseif (str_contains($cityStr, 'vĩnh phúc')) {
                    $prefilledProvince = 'vp';
                } elseif (str_contains($cityStr, 'hải phòng')) {
                    $prefilledProvince = 'hp';
                } elseif (str_contains($cityStr, 'cần thơ')) {
                    $prefilledProvince = 'ct';
                } elseif (str_contains($cityStr, 'hòa bình')) {
                    $prefilledProvince = 'hb';
                } elseif (str_contains($cityStr, 'nam định') || str_contains($cityStr, 'ninh bình')) {
                    $prefilledProvince = 'nb';
                } elseif (str_contains($cityStr, 'an giang')) {
                    $prefilledProvince = 'ag';
                } elseif (str_contains($cityStr, 'kiên giang')) {
                    $prefilledProvince = 'kg';
                } elseif (str_contains($cityStr, 'đồng tháp')) {
                    $prefilledProvince = 'dt';
                } elseif (str_contains($cityStr, 'trà vinh') || str_contains($cityStr, 'vĩnh long')) {
                    $prefilledProvince = 'tv';
                } elseif (str_contains($cityStr, 'bến tre') || str_contains($cityStr, 'sóc trăng')) {
                    $prefilledProvince = 'bte';
                } elseif (str_contains($cityStr, 'đà nẵng')) {
                    $prefilledProvince = 'dn';
                } elseif (str_contains($cityStr, 'quảng nam') || str_contains($cityStr, 'quảng ngãi')) {
                    $prefilledProvince = 'qng';
                } elseif (str_contains($cityStr, 'bình định') || str_contains($cityStr, 'phú yên')) {
                    $prefilledProvince = 'bdinh';
                } elseif (str_contains($cityStr, 'khánh hòa') || str_contains($cityStr, 'nha trang')) {
                    $prefilledProvince = 'nth';
                } elseif (str_contains($cityStr, 'thanh hóa') || str_contains($cityStr, 'nghệ an')) {
                    $prefilledProvince = 'th';
                } elseif (str_contains($cityStr, 'quảng bình') || str_contains($cityStr, 'quảng trị')) {
                    $prefilledProvince = 'qbi';
                } elseif (str_contains($cityStr, 'thừa thiên') || str_contains($cityStr, 'huế')) {
                    $prefilledProvince = 'hue';
                } elseif (str_contains($cityStr, 'gia lai') || str_contains($cityStr, 'kon tum')) {
                    $prefilledProvince = 'gl';
                } elseif (str_contains($cityStr, 'đắk lắk') || str_contains($cityStr, 'đắk nông')) {
                    $prefilledProvince = 'dkl';
                } elseif (str_contains($cityStr, 'lào cai') || str_contains($cityStr, 'yên bái')) {
                    $prefilledProvince = 'lc';
                } elseif (str_contains($cityStr, 'điện biên') || str_contains($cityStr, 'lai châu')) {
                    $prefilledProvince = 'dbi';
                } elseif (str_contains($cityStr, 'sơn la')) {
                    $prefilledProvince = 'ss';
                } elseif (str_contains($cityStr, 'cao bằng') || str_contains($cityStr, 'bắc kạn')) {
                    $prefilledProvince = 'cb';
                } elseif (str_contains($cityStr, 'lạng sơn') || str_contains($cityStr, 'hà giang')) {
                    $prefilledProvince = 'ls';
                } elseif (str_contains($cityStr, 'cà mau') || str_contains($cityStr, 'bạc liêu')) {
                    $prefilledProvince = 'cm';
                } else {
                    $prefilledProvince = 'other';
                }
            }
            // If saved address has its own phone, prefer it over user phone
            if (!empty($defaultAddress->phone)) {
              $prefilledPhone = $defaultAddress->phone;
            }
        } else {
            // Fallback: lấy từ users.address (format: "street, ward, district, city")
            if (!empty($user->address)) {
                $parts = explode(',', $user->address);
                if (count($parts) >= 2) {
                    $cityPart = trim(end($parts));
                    $cityStr = mb_strtolower($cityPart);
                    // Strip city (phần cuối) khỏi địa chỉ để tránh trùng lặp với dropdown
                    $addressParts = array_slice($parts, 0, count($parts) - 1);
                    $prefilledAddress = trim(implode(',', $addressParts));
                    // Map city → province code
                    if (str_contains($cityStr, 'hồ chí minh') || str_contains($cityStr, 'hcm')) {
                        $prefilledProvince = 'hcm';
                    } elseif (str_contains($cityStr, 'hà nội') || str_contains($cityStr, 'hn')) {
                        $prefilledProvince = 'hn';
                    } elseif (str_contains($cityStr, 'bình dương')) {
                        $prefilledProvince = 'bd';
                    } elseif (str_contains($cityStr, 'đồng nai')) {
                        $prefilledProvince = 'dnai';
                    } elseif (str_contains($cityStr, 'long an')) {
                        $prefilledProvince = 'la';
                    } elseif (str_contains($cityStr, 'tiền giang')) {
                        $prefilledProvince = 'tg';
                    } elseif (str_contains($cityStr, 'vũng tàu') || str_contains($cityStr, 'bà rịa')) {
                        $prefilledProvince = 'vt';
                    } elseif (str_contains($cityStr, 'bắc ninh')) {
                        $prefilledProvince = 'bn';
                    } elseif (str_contains($cityStr, 'hưng yên')) {
                        $prefilledProvince = 'hy';
                    } elseif (str_contains($cityStr, 'hà nam')) {
                        $prefilledProvince = 'hnam';
                    } elseif (str_contains($cityStr, 'vĩnh phúc')) {
                        $prefilledProvince = 'vp';
                    } elseif (str_contains($cityStr, 'hải phòng')) {
                        $prefilledProvince = 'hp';
                    } elseif (str_contains($cityStr, 'cần thơ')) {
                        $prefilledProvince = 'ct';
                    } elseif (str_contains($cityStr, 'hòa bình')) {
                        $prefilledProvince = 'hb';
                    } elseif (str_contains($cityStr, 'nam định') || str_contains($cityStr, 'ninh bình')) {
                        $prefilledProvince = 'nb';
                    } elseif (str_contains($cityStr, 'an giang')) {
                        $prefilledProvince = 'ag';
                    } elseif (str_contains($cityStr, 'kiên giang')) {
                        $prefilledProvince = 'kg';
                    } elseif (str_contains($cityStr, 'đồng tháp')) {
                        $prefilledProvince = 'dt';
                    } elseif (str_contains($cityStr, 'trà vinh') || str_contains($cityStr, 'vĩnh long')) {
                        $prefilledProvince = 'tv';
                    } elseif (str_contains($cityStr, 'bến tre') || str_contains($cityStr, 'sóc trăng')) {
                        $prefilledProvince = 'bte';
                    } elseif (str_contains($cityStr, 'đà nẵng')) {
                        $prefilledProvince = 'dn';
                    } elseif (str_contains($cityStr, 'quảng nam') || str_contains($cityStr, 'quảng ngãi')) {
                        $prefilledProvince = 'qng';
                    } elseif (str_contains($cityStr, 'bình định') || str_contains($cityStr, 'phú yên')) {
                        $prefilledProvince = 'bdinh';
                    } elseif (str_contains($cityStr, 'khánh hòa') || str_contains($cityStr, 'nha trang')) {
                        $prefilledProvince = 'nth';
                    } elseif (str_contains($cityStr, 'thanh hóa') || str_contains($cityStr, 'nghệ an')) {
                        $prefilledProvince = 'th';
                    } elseif (str_contains($cityStr, 'quảng bình') || str_contains($cityStr, 'quảng trị')) {
                        $prefilledProvince = 'qbi';
                    } elseif (str_contains($cityStr, 'thừa thiên') || str_contains($cityStr, 'huế')) {
                        $prefilledProvince = 'hue';
                    } elseif (str_contains($cityStr, 'gia lai') || str_contains($cityStr, 'kon tum')) {
                        $prefilledProvince = 'gl';
                    } elseif (str_contains($cityStr, 'đắk lắk') || str_contains($cityStr, 'đắk nông')) {
                        $prefilledProvince = 'dkl';
                    } elseif (str_contains($cityStr, 'lào cai') || str_contains($cityStr, 'yên bái')) {
                        $prefilledProvince = 'lc';
                    } elseif (str_contains($cityStr, 'điện biên') || str_contains($cityStr, 'lai châu')) {
                        $prefilledProvince = 'dbi';
                    } elseif (str_contains($cityStr, 'sơn la')) {
                        $prefilledProvince = 'ss';
                    } elseif (str_contains($cityStr, 'cao bằng') || str_contains($cityStr, 'bắc kạn')) {
                        $prefilledProvince = 'cb';
                    } elseif (str_contains($cityStr, 'lạng sơn') || str_contains($cityStr, 'hà giang')) {
                        $prefilledProvince = 'ls';
                    } elseif (str_contains($cityStr, 'cà mau') || str_contains($cityStr, 'bạc liêu')) {
                        $prefilledProvince = 'cm';
                    } else {
                        $prefilledProvince = 'other';
                    }
                } else {
                    $prefilledAddress = $user->address;
                }
            }
        }
    }
@endphp
<div class="bg-gray-50 min-h-screen py-8">
<div class="max-w-6xl mx-auto px-4">

  {{-- Breadcrumb --}}
  <nav class="text-sm text-gray-500 mb-6">
    <a href="{{ url('/') }}" class="hover:text-blue-600">Trang chủ</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cart.index') }}" class="hover:text-blue-600">Giỏ hàng</a>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-semibold">Thanh toán</span>
  </nav>

  {{-- Progress Steps --}}
  <div class="flex items-center gap-3 mb-8">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center text-sm font-bold">✓</div>
      <span class="text-sm font-semibold text-green-600 hidden sm:inline">Giỏ hàng</span>
    </div>
    <div class="flex-1 h-0.5 bg-blue-500"></div>
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold">2</div>
      <span class="text-sm font-semibold text-blue-600 hidden sm:inline">Thanh toán</span>
    </div>
    <div class="flex-1 h-0.5 bg-gray-200"></div>
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">3</div>
      <span class="text-sm font-semibold text-gray-400 hidden sm:inline">Xác nhận</span>
    </div>
  </div>

  <form id="checkout-form" method="POST" action="{{ route('cart.place-order') }}" class="flex flex-col lg:flex-row gap-6">
    @csrf
    <input type="hidden" name="payment_method" id="payment_method_input" value="COD">
    <input type="hidden" name="wallet_points_used" id="wallet_points_used_input" value="0">

    {{-- ===== CỘT TRÁI ===== --}}
    <div class="w-full lg:w-3/5 space-y-5">

      {{-- Thông tin người nhận --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-bold mb-4 flex items-center gap-2 text-gray-800">
          <span class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
          Thông tin người nhận
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Họ và tên *</label>
            <input id="inp-name" name="customer_name" type="text" required maxlength="50"
              class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
              value="{{ Auth::check() ? $prefilledName : '' }}" placeholder="Nguyễn Văn A">
            <p id="err-name" class="text-xs text-red-500 mt-1 hidden"></p>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Số điện thoại *</label>
            <input id="inp-phone" name="customer_phone" type="tel" required maxlength="10"
              class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
              value="{{ Auth::check() ? $prefilledPhone : '' }}" placeholder="0901234567">
            <p id="err-phone" class="text-xs text-red-500 mt-1 hidden"></p>
          </div>
        </div>
        <div class="mt-4">
          {{-- Ẩn Tỉnh/Thành phố theo yêu cầu --}}
          <div style="display: none;">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tỉnh/Thành phố *</label>
            <select id="inp-province" name="province"
              class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white transition text-sm cursor-pointer mb-3">
              <option value="" disabled {{ $prefilledProvince ? '' : 'selected' }}>-- Chọn Tỉnh/Thành phố --</option>

            {{-- Nhóm 1: Nội thành (< 30 km) --}}
            <optgroup label="🏙️ Nội thành — Phí 20.000đ (Miễn phí từ 10tr)">
                <option value="hcm" data-fee="20000" data-threshold="10000000" {{ $prefilledProvince === 'hcm' ? 'selected' : '' }}>TP. Hồ Chí Minh</option>
                <option value="hn"  data-fee="20000" data-threshold="10000000" {{ $prefilledProvince === 'hn' ? 'selected' : '' }}>TP. Hà Nội</option>
            </optgroup>

            {{-- Nhóm 2: Vùng lân cận (30 – 150 km) --}}
            <optgroup label="🚐 Vùng lân cận (30–150 km) — Phí 35.000đ (Miễn phí từ 10tr)">
                <option value="bd"   data-fee="35000" data-threshold="10000000" {{ $prefilledProvince === 'bd' ? 'selected' : '' }}>Tỉnh Bình Dương</option>
                <option value="dnai" data-fee="35000" data-threshold="10000000" {{ $prefilledProvince === 'dnai' ? 'selected' : '' }}>Tỉnh Đồng Nai</option>
                <option value="la"   data-fee="35000" data-threshold="10000000" {{ $prefilledProvince === 'la' ? 'selected' : '' }}>Tỉnh Long An</option>
                <option value="tg"   data-fee="35000" data-threshold="10000000" {{ $prefilledProvince === 'tg' ? 'selected' : '' }}>Tỉnh Tiền Giang</option>
                <option value="vt"   data-fee="35000" data-threshold="10000000" {{ $prefilledProvince === 'vt' ? 'selected' : '' }}>Tỉnh Bà Rịa – Vũng Tàu</option>
                <option value="bn"   data-fee="35000" data-threshold="10000000" {{ $prefilledProvince === 'bn' ? 'selected' : '' }}>Tỉnh Bắc Ninh</option>
                <option value="hy"   data-fee="35000" data-threshold="10000000" {{ $prefilledProvince === 'hy' ? 'selected' : '' }}>Tỉnh Hưng Yên</option>
                <option value="hnam" data-fee="35000" data-threshold="10000000" {{ $prefilledProvince === 'hnam' ? 'selected' : '' }}>Tỉnh Hà Nam</option>
                <option value="vp"   data-fee="35000" data-threshold="10000000" {{ $prefilledProvince === 'vp' ? 'selected' : '' }}>Tỉnh Vĩnh Phúc</option>
            </optgroup>

            {{-- Nhóm 3: Vùng trung bình (150 – 400 km) --}}
            <optgroup label="🚚 Vùng trung bình (150–400 km) — Phí 50.000đ (Miễn phí từ 10tr)">
                <option value="hp"   data-fee="50000" data-threshold="10000000" {{ $prefilledProvince === 'hp' ? 'selected' : '' }}>TP. Hải Phòng</option>
                <option value="ct"   data-fee="50000" data-threshold="10000000" {{ $prefilledProvince === 'ct' ? 'selected' : '' }}>TP. Cần Thơ</option>
                <option value="hb"   data-fee="50000" data-threshold="10000000" {{ $prefilledProvince === 'hb' ? 'selected' : '' }}>Tỉnh Hòa Bình</option>
                <option value="nb"   data-fee="50000" data-threshold="10000000" {{ $prefilledProvince === 'nb' ? 'selected' : '' }}>Tỉnh Nam Định & Ninh Bình</option>
                <option value="ag"   data-fee="50000" data-threshold="10000000" {{ $prefilledProvince === 'ag' ? 'selected' : '' }}>Tỉnh An Giang</option>
                <option value="kg"   data-fee="50000" data-threshold="10000000" {{ $prefilledProvince === 'kg' ? 'selected' : '' }}>Tỉnh Kiên Giang</option>
                <option value="dt"   data-fee="50000" data-threshold="10000000" {{ $prefilledProvince === 'dt' ? 'selected' : '' }}>Tỉnh Đồng Tháp</option>
                <option value="tv"   data-fee="50000" data-threshold="10000000" {{ $prefilledProvince === 'tv' ? 'selected' : '' }}>Tỉnh Trà Vinh & Vĩnh Long</option>
                <option value="bte"  data-fee="50000" data-threshold="10000000" {{ $prefilledProvince === 'bte' ? 'selected' : '' }}>Tỉnh Bến Tre & Sóc Trăng</option>
            </optgroup>

            {{-- Nhóm 4: Vùng xa (400 – 700 km) --}}
            <optgroup label="✈️ Vùng xa (400–700 km) — Phí 70.000đ (Miễn phí từ 10tr)">
                <option value="dn"   data-fee="70000" data-threshold="10000000" {{ $prefilledProvince === 'dn' ? 'selected' : '' }}>TP. Đà Nẵng</option>
                <option value="qng"  data-fee="70000" data-threshold="10000000" {{ $prefilledProvince === 'qng' ? 'selected' : '' }}>Tỉnh Quảng Nam & Quảng Ngãi</option>
                <option value="bdinh" data-fee="70000" data-threshold="10000000" {{ $prefilledProvince === 'bdinh' ? 'selected' : '' }}>Tỉnh Bình Định & Phú Yên</option>
                <option value="nth"  data-fee="70000" data-threshold="10000000" {{ $prefilledProvince === 'nth' ? 'selected' : '' }}>Tỉnh Khánh Hòa (Nha Trang)</option>
                <option value="th"   data-fee="70000" data-threshold="10000000" {{ $prefilledProvince === 'th' ? 'selected' : '' }}>Tỉnh Thanh Hóa & Nghệ An</option>
                <option value="qbi"  data-fee="70000" data-threshold="10000000" {{ $prefilledProvince === 'qbi' ? 'selected' : '' }}>Tỉnh Quảng Bình & Quảng Trị</option>
                <option value="hue"  data-fee="70000" data-threshold="10000000" {{ $prefilledProvince === 'hue' ? 'selected' : '' }}>Tỉnh Thừa Thiên – Huế</option>
            </optgroup>

            {{-- Nhóm 5: Vùng rất xa (> 700 km) --}}
            <optgroup label="🗺️ Vùng rất xa (> 700 km) — Phí 100.000đ (Miễn phí từ 10tr)">
                <option value="gl"   data-fee="100000" data-threshold="10000000" {{ $prefilledProvince === 'gl' ? 'selected' : '' }}>Tỉnh Gia Lai & Kon Tum</option>
                <option value="dkl"  data-fee="100000" data-threshold="10000000" {{ $prefilledProvince === 'dkl' ? 'selected' : '' }}>Tỉnh Đắk Lắk & Đắk Nông</option>
                <option value="lc"   data-fee="100000" data-threshold="10000000" {{ $prefilledProvince === 'lc' ? 'selected' : '' }}>Tỉnh Lào Cai & Yên Bái</option>
                <option value="dbi"  data-fee="100000" data-threshold="10000000" {{ $prefilledProvince === 'dbi' ? 'selected' : '' }}>Tỉnh Điện Biên & Lai Châu</option>
                <option value="ss"   data-fee="100000" data-threshold="10000000" {{ $prefilledProvince === 'ss' ? 'selected' : '' }}>Tỉnh Sơn La & Hòa Bình</option>
                <option value="cb"   data-fee="100000" data-threshold="10000000" {{ $prefilledProvince === 'cb' ? 'selected' : '' }}>Tỉnh Cao Bằng & Bắc Kạn</option>
                <option value="ls"   data-fee="100000" data-threshold="10000000" {{ $prefilledProvince === 'ls' ? 'selected' : '' }}>Tỉnh Lạng Sơn & Hà Giang</option>
                <option value="cm"   data-fee="100000" data-threshold="10000000" {{ $prefilledProvince === 'cm' ? 'selected' : '' }}>Tỉnh Cà Mau & Bạc Liêu</option>
                <option value="other" data-fee="100000" data-threshold="10000000" {{ $prefilledProvince === 'other' ? 'selected' : '' }}>Tỉnh thành khác / Hải đảo</option>
            </optgroup>
          </select>
          </div>

          @if(Auth::check() && isset($addresses) && $addresses->isNotEmpty())
            <div class="mb-4 flex items-center justify-between gap-3">
              <div>
                <div class="text-sm font-semibold text-gray-700">Chọn địa chỉ đã lưu</div>
                <p class="text-xs text-gray-500">Chọn địa chỉ đã lưu để tự động điền thông tin giao hàng.</p>
              </div>
              <div class="flex items-center gap-2">
                <button type="button" onclick="openSavedAddressModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                  <i class="fa-solid fa-map-marker-alt"></i>
                  Chọn từ địa chỉ đã lưu
                </button>
                {{-- Quản lý địa chỉ đã được ẩn theo yêu cầu: chỉ giữ nút chọn địa chỉ --}}
              </div>
            </div>
          @endif

          <input type="hidden" id="saved_address_id" name="saved_address_id" value="{{ $selectedAddressId }}">

          <div class="flex justify-between items-center mb-1">
            <label class="block text-sm font-semibold text-gray-700">Địa chỉ chi tiết (Số nhà, tên đường, phường/xã, quận/huyện) *</label>
            <span id="counter-address" class="text-xs text-gray-400 font-medium">0/150</span>
          </div>
          <input id="inp-address" name="shipping_address" type="text" required maxlength="150"
            class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
            value="{{ Auth::check() ? $prefilledAddress : '' }}" placeholder="VD: 123 Đường Lê Lợi, Phường Bến Thành, Quận 1">
          <p id="err-address" class="text-xs text-red-500 mt-1 hidden"></p>
        </div>

        @if(Auth::check() && isset($addresses) && $addresses->isNotEmpty())
          <div id="saved-address-modal" class="fixed inset-0 z-[99999] hidden flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl overflow-hidden border border-gray-100" style="max-height:90vh; overflow:auto;">
              <div class="flex items-start justify-between gap-4 p-6 border-b border-gray-200">
                <div>
                  <h3 class="text-base font-bold text-gray-900">Chọn địa chỉ nhận hàng</h3>
                  <p class="text-sm text-gray-500 mt-1">Nhấn vào địa chỉ để tự động điền thông tin giao hàng trên trang thanh toán.</p>
                </div>
                <button type="button" onclick="closeSavedAddressModal()" class="text-gray-400 hover:text-gray-700">
                  <i class="fa-solid fa-xmark text-lg"></i>
                </button>
              </div>
              <div class="max-h-[420px] overflow-y-auto p-6 space-y-3">
                @foreach($addresses as $address)
                  @php
                    $savedAddressLabel = trim($address->name ?: ($address->type ?: 'Địa chỉ'));
                    $savedAddressFull = trim(implode(', ', array_filter([$address->street, $address->ward, $address->district, $address->city])));
                    $savedSelected = $selectedAddressId === $address->id;
                  @endphp
                  <button type="button" onclick="selectSavedAddress(this)"
                    class="saved-address-card w-full text-left p-4 border rounded-3xl transition shadow-sm hover:border-blue-500 flex items-start justify-between gap-3 {{ $savedSelected ? 'border-blue-600 bg-blue-50' : 'border-gray-200 bg-white' }}"
                    data-address-id="{{ $address->id }}"
                    data-full-address="{{ e($savedAddressFull) }}"
                    data-city="{{ e($address->city) }}">
                    <div class="min-w-0">
                      <div class="font-semibold text-sm text-gray-900">{{ $savedAddressLabel }}</div>
                      <div class="text-xs text-gray-500 mt-1 break-words">{{ $savedAddressFull }}</div>
                    </div>
                    <div class="text-right">
                      @if($address->is_default)
                        <span class="inline-flex px-3 py-1 text-[11px] font-semibold uppercase tracking-wider bg-blue-100 text-blue-700 rounded-full">Mặc định</span>
                      @endif
                      <div class="text-xs text-gray-400 mt-2">{{ $user->phone_number }}</div>
                    </div>
                  </button>
                @endforeach
              </div>
              <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200">
                <a href="{{ route('profile.index') }}" class="text-sm text-blue-600 hover:underline">Thêm / sửa địa chỉ</a>
                <button type="button" onclick="closeSavedAddressModal()" class="px-5 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition">Đóng</button>
              </div>
            </div>
          </div>
        @endif
        <div class="mt-4">
          <div class="flex justify-between items-center mb-1">
            <label class="block text-sm font-semibold text-gray-700">Ghi chú (tùy chọn)</label>
            <span id="counter-note" class="text-xs text-gray-400 font-medium">0/250</span>
          </div>
          <textarea id="inp-note" name="note" rows="2" maxlength="250"
            class="w-full p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm resize-none"
            placeholder="Giao giờ hành chính, gọi trước khi giao..."></textarea>
          <p id="err-note" class="text-xs text-red-500 mt-1 hidden"></p>
        </div>
      </div>

      {{-- Phương thức thanh toán --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-bold mb-4 flex items-center gap-2 text-gray-800">
          <span class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
          Phương thức thanh toán
        </h2>
        <div class="space-y-3" id="payment-methods">

          {{-- QR Code --}}
          <div class="relative">
            <input type="radio" name="payment_method" id="pm-qr" value="qr" class="pay-radio sr-only" checked>
            <label for="pm-qr" onclick="selectMethod('qr')"
              class="pay-label flex items-center p-4 border-2 border-blue-500 bg-blue-50 rounded-xl cursor-pointer transition-all hover:border-blue-500">
              <div class="flex items-center gap-3 w-full">
                <div class="dot-outer w-5 h-5 rounded-full border-2 border-blue-500 flex items-center justify-center shrink-0">
                  <div class="dot-inner w-2.5 h-2.5 rounded-full bg-blue-500 opacity-100"></div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <p class="font-bold text-sm text-gray-800">Chuyển khoản qua mã QR (Ngân hàng)</p>
                    <span class="bg-red-100 text-red-600 text-[9px] px-2 py-0.5 rounded-full font-bold">KHUYÊN DÙNG</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-0.5">Hỗ trợ tất cả ứng dụng ngân hàng và ví điện tử. Tự động xác nhận.</p>
                </div>
                <i class="fa-solid fa-qrcode text-2xl text-blue-600 hidden sm:block"></i>
              </div>
            </label>
          </div>

          {{-- COD --}}
          <div class="relative">
            <input type="radio" name="payment_method" id="pm-cod" value="cod" class="pay-radio sr-only">
            <label for="pm-cod" onclick="selectMethod('cod')"
              class="pay-label flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer transition-all hover:border-green-400">
              <div class="flex items-center gap-3 w-full">
                <div class="dot-outer w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0">
                  <div class="dot-inner w-2.5 h-2.5 rounded-full bg-blue-500 opacity-0"></div>
                </div>
                <div class="flex-1">
                  <p class="font-bold text-sm text-gray-800">Thanh toán khi nhận hàng (COD)</p>
                  <p class="text-xs text-gray-500 mt-0.5">Trả tiền mặt cho nhân viên giao hàng.</p>
                </div>
                <i class="fa-solid fa-hand-holding-dollar text-2xl text-green-600 hidden sm:block"></i>
              </div>
            </label>
          </div>
        </div>


        {{-- COD Panel --}}
        <div id="cod-panel" class="mt-5 p-4 bg-green-50 border border-green-200 rounded-2xl method-panel">
          <div class="flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-green-600 mt-0.5"></i>
            <div class="text-sm text-green-800">
              <p class="font-bold">Thanh toán khi nhận hàng</p>
              <p class="mt-1 text-green-700">Bạn sẽ thanh toán bằng tiền mặt khi nhân viên giao hàng đến. Vui lòng chuẩn bị đúng số tiền để thuận tiện cho quá trình giao hàng.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== CỘT PHẢI ===== --}}
    <div class="w-full lg:w-2/5">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-4">
        <h2 class="text-base font-bold mb-4 text-gray-800 border-b pb-3 flex items-center justify-between">
          <span>Đơn hàng của bạn</span>
          <span id="item-badge" class="text-xs bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full font-bold">0 sản phẩm</span>
        </h2>

        {{-- Danh sách sản phẩm --}}
        <div id="order-items" class="space-y-3 mb-5 max-h-56 overflow-y-auto pr-1">
          <p class="text-sm text-gray-400 text-center py-6">Đang tải đơn hàng...</p>
        </div>

        {{-- Mã giảm giá --}}
        <div class="mb-5 bg-gray-50 rounded-xl border border-gray-100 p-4">
          <div class="flex justify-between items-center mb-2">
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Mã giảm giá</label>
            <a href="{{ route('cart.discount-code') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 transition">
              <i class="fa-solid fa-ticket text-sm"></i> Chọn Voucher
            </a>
          </div>
          <div class="flex gap-2">
            <input id="discount-code" type="text"
              class="flex-1 p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-400 outline-none"
              value="{{ session('applied_coupon_code') }}"
              placeholder="VD: SUMMER30">
            <button type="button" onclick="applyDiscount()" id="btn-discount"
              class="px-4 bg-gray-800 text-white text-sm rounded-lg font-semibold hover:bg-gray-900 transition whitespace-nowrap">
              Áp dụng
            </button>
          </div>
          <p id="discount-msg" class="text-xs mt-2 hidden font-medium"></p>
        </div>

        <div class="mb-5 bg-blue-50 rounded-xl border border-blue-100 p-4">
          <div class="flex items-center justify-between mb-2">
            <label class="block text-xs font-bold text-blue-700 uppercase tracking-wide">Điểm tiêu dùng</label>
            <span id="wallet-balance" class="text-xs font-semibold text-blue-600">{{ Auth::check() ? number_format(($balance['wallet_points'] ?? 0)) : 0 }} điểm</span>
          </div>
          <p class="text-[11px] text-blue-700 mb-2">Điểm đã được chuyển sang trang đổi thưởng <a href="{{ route('rewards.index') }}" class="font-semibold underline">/rewards</a>.</p>
        </div>

        {{-- Tóm tắt tiền --}}
        <div class="space-y-2.5 text-sm border-t pt-4">
          <div class="flex justify-between text-gray-600">
            <span>Tạm tính</span>
            <span id="sum-subtotal" class="font-medium">0đ</span>
          </div>
          <div class="flex justify-between text-gray-600">
            <span>Phí vận chuyển</span>
            <span id="sum-shipping" class="font-medium text-gray-800">Chưa chọn khu vực</span>
          </div>
          <div id="sum-discount-row" class="flex justify-between text-gray-600 hidden">
            <span>Giảm giá</span>
            <span id="sum-discount" class="font-medium text-green-600">-0đ</span>
          </div>
          <div id="sum-wallet-row" class="flex justify-between text-gray-600 hidden">
            <span>Điểm tiêu dùng</span>
            <span id="sum-wallet" class="font-medium text-green-600">-0đ</span>
          </div>
          <div class="flex justify-between items-end pt-3 border-t">
            <span class="font-bold text-gray-800">Thành tiền</span>
            <span id="sum-total" class="text-2xl font-bold text-red-600">0đ</span>
          </div>
          <p class="text-right text-xs text-gray-400 italic">Đã bao gồm VAT</p>
          <input type="hidden" name="discount_amount" id="discount_amount_input" value="0">
        </div>

        {{-- Nút đặt hàng --}}
        <button type="submit" id="btn-order"
          class="w-full mt-5 bg-red-600 text-white py-3.5 rounded-xl font-bold text-base hover:bg-red-700 transition-all shadow-md disabled:bg-gray-300 disabled:cursor-not-allowed disabled:shadow-none"
          disabled>
          <i class="fa-solid fa-lock mr-2 text-sm"></i>XÁC NHẬN ĐẶT HÀNG
        </button>

        <a href="{{ route('cart.index') }}" class="block mt-3 text-center text-sm text-blue-600 hover:underline">
          <i class="fa-solid fa-arrow-left mr-1"></i>Quay lại giỏ hàng
        </a>
      </div>
    </div>

  </form>
</div>
</div>

{{-- Success Overlay --}}
<div id="success-overlay" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center backdrop-blur-sm">
  <div class="bg-white rounded-3xl p-10 text-center max-w-sm mx-4 shadow-2xl">
    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
      <i class="fa-solid fa-circle-check text-5xl text-green-500"></i>
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-2">Đặt hàng thành công!</h3>
    <p class="text-gray-500 text-sm mb-6">Cảm ơn bạn đã mua hàng. Chúng tôi sẽ liên hệ xác nhận sớm nhất.</p>
    <a href="{{ url('/') }}" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
      Về trang chủ
    </a>
  </div>
</div>
@endsection

@push('scripts')
<script>
// ---- CONFIG ----
const BANK = { id: 'MB', account: '123456789', name: 'DIENMAYPRO' };

// ---- STATE ----
let cartItems = [];
let subtotalVal = 0;
let discountVal = 0;
let shippingVal = 0;
let currentMethod = 'cod';

// ---- FORMAT ----
const fmt = n => new Intl.NumberFormat('vi-VN').format(n || 0) + 'đ';

// ---- CALCULATE SHIPPING ----
function calculateShipping() {
  const provSel = document.getElementById('inp-province');
  if (!provSel) return;
  const opt = provSel.options[provSel.selectedIndex];
  if (!opt || opt.value === '') {
    shippingVal = 0;
    document.getElementById('sum-shipping').textContent = 'Chưa chọn khu vực';
    updateTotals();
    return;
  }

  const baseFee = parseInt(opt.getAttribute('data-fee')) || 0;
  const threshold = parseInt(opt.getAttribute('data-threshold')) || 0;
  
  // Tổng tạm tính sau giảm giá
  const currentTotal = subtotalVal - discountVal;

  if (currentTotal >= threshold) {
    shippingVal = 0;
    document.getElementById('sum-shipping').innerHTML = `<span class="line-through text-gray-400 mr-1.5">${fmt(baseFee)}</span><span class="text-green-600 font-bold">Miễn phí</span>`;
  } else {
    shippingVal = baseFee;
    document.getElementById('sum-shipping').textContent = fmt(baseFee);
  }

  updateTotals();
}

function findProvinceCodeFromCity(city) {
  if (!city) return '';
  const text = city.toLowerCase();
  if (text.includes('hồ chí minh') || text.includes('hcm')) return 'hcm';
  if (text.includes('hà nội') || text.includes('hn')) return 'hn';
  if (text.includes('bình dương')) return 'bd';
  if (text.includes('đồng nai')) return 'dnai';
  if (text.includes('long an')) return 'la';
  if (text.includes('tiền giang')) return 'tg';
  if (text.includes('vũng tàu') || text.includes('bà rịa')) return 'vt';
  if (text.includes('bắc ninh')) return 'bn';
  if (text.includes('hưng yên')) return 'hy';
  if (text.includes('hà nam')) return 'hnam';
  if (text.includes('vĩnh phúc')) return 'vp';
  if (text.includes('hải phòng')) return 'hp';
  if (text.includes('cần thơ')) return 'ct';
  if (text.includes('hòa bình')) return 'hb';
  if (text.includes('nam định') || text.includes('ninh bình')) return 'nb';
  if (text.includes('an giang')) return 'ag';
  if (text.includes('kiên giang')) return 'kg';
  if (text.includes('đồng tháp')) return 'dt';
  if (text.includes('trà vinh') || text.includes('vĩnh long')) return 'tv';
  if (text.includes('bến tre') || text.includes('sóc trăng')) return 'bte';
  if (text.includes('đà nẵng')) return 'dn';
  if (text.includes('quảng nam') || text.includes('quảng ngãi')) return 'qng';
  if (text.includes('bình định') || text.includes('phú yên')) return 'bdinh';
  if (text.includes('khánh hòa') || text.includes('nha trang')) return 'nth';
  if (text.includes('thanh hóa') || text.includes('nghệ an')) return 'th';
  if (text.includes('quảng bình') || text.includes('quảng trị')) return 'qbi';
  if (text.includes('thừa thiên') || text.includes('huế')) return 'hue';
  if (text.includes('gia lai') || text.includes('kon tum')) return 'gl';
  if (text.includes('đắk lắk') || text.includes('đắk nông')) return 'dkl';
  if (text.includes('lào cai') || text.includes('yên bái')) return 'lc';
  if (text.includes('điện biên') || text.includes('lai châu')) return 'dbi';
  if (text.includes('sơn la')) return 'ss';
  if (text.includes('cao bằng') || text.includes('bắc kạn')) return 'cb';
  if (text.includes('lạng sơn') || text.includes('hà giang')) return 'ls';
  if (text.includes('cà mau') || text.includes('bạc liêu')) return 'cm';
  return 'other';
}

function selectSavedAddress(button) {
  const fullAddress = button.dataset.fullAddress || '';
  const city = button.dataset.city || '';
  const addressId = button.dataset.addressId || '';

  document.getElementById('inp-address').value = fullAddress;
  const savedAddressIdInput = document.getElementById('saved_address_id');
  if (savedAddressIdInput) {
    savedAddressIdInput.value = addressId;
  }

  const provinceCode = findProvinceCodeFromCity(city);
  const provinceSelect = document.getElementById('inp-province');
  if (provinceSelect && provinceCode) {
    provinceSelect.value = provinceCode;
    calculateShipping();
  }

  document.querySelectorAll('.saved-address-card').forEach(card => {
    card.classList.remove('border-blue-600','bg-blue-50');
    card.classList.add('border-gray-200','bg-white');
  });

  button.classList.add('border-blue-600','bg-blue-50');
  button.classList.remove('border-gray-200','bg-white');
  checkFormValidity();
  closeSavedAddressModal();
}

function openSavedAddressModal() {
  const modal = document.getElementById('saved-address-modal');
  if (!modal) return;
  modal.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeSavedAddressModal() {
  const modal = document.getElementById('saved-address-modal');
  if (!modal) return;
  modal.classList.add('hidden');
  document.body.style.overflow = '';
}

// ---- LOAD CART FROM SESSIONSTORAGE ----
function loadCart() {
  try {
    const raw = '{!! json_encode($cartItems) !!}';
    cartItems = JSON.parse(raw);
  } catch(e) {
    console.error("Lỗi nạp giỏ hàng từ server:", e);
    cartItems = [];
  }

  renderItems();
}

function renderItems() {
  const el = document.getElementById('order-items');
  if (!cartItems.length) {
    el.innerHTML = '<p class="text-sm text-gray-400 text-center py-4">Không có sản phẩm.</p>';
    document.getElementById('btn-order').disabled = true;
    return;
  }
  subtotalVal = cartItems.reduce((s, i) => s + i.price * i.quantity, 0);
  el.innerHTML = cartItems.map(i => `
    <div class="flex justify-between items-start gap-3 text-sm">
      <div class="flex gap-1.5 flex-1 min-w-0">
        <span class="shrink-0 font-bold text-gray-500">${i.quantity}×</span>
        <p class="text-gray-800 font-medium leading-snug truncate" title="${i.name}">${i.name}</p>
      </div>
      <span class="shrink-0 font-bold text-gray-800">${fmt(i.price * i.quantity)}</span>
    </div>`).join('');
  document.getElementById('item-badge').textContent = cartItems.length + ' sản phẩm';
  // Nếu đã chọn tỉnh từ trước (sau khi reload), tính phí
  calculateShipping();
  checkFormValidity();
}

function updateTotals() {
  const total = subtotalVal - discountVal + shippingVal;
  document.getElementById('sum-subtotal').textContent = fmt(subtotalVal);
  document.getElementById('sum-total').textContent = fmt(total > 0 ? total : 0);
  document.getElementById('discount_amount_input').value = discountVal;
  if (discountVal > 0) {
    document.getElementById('sum-discount-row').classList.remove('hidden');
    document.getElementById('sum-discount').textContent = '-' + fmt(discountVal);
  } else {
    document.getElementById('sum-discount-row').classList.add('hidden');
  }
}

// ---- QR (Đã chuyển sang trang maQR) ----
// ---- PAYMENT METHOD ----
function selectMethod(method) {
  currentMethod = method;

  // Reset all labels
  document.querySelectorAll('.pay-label').forEach(l => {
    l.classList.remove('border-blue-500','bg-blue-50','border-pink-400','bg-pink-50','border-green-400','bg-green-50');
    l.classList.add('border-gray-200');
    l.querySelector('.dot-outer').style.borderColor = '#d1d5db';
    l.querySelector('.dot-inner').style.opacity = '0';
  });

  // Activate selected
  const sel = document.querySelector(`label[for="pm-${method}"]`);
  if (sel) {
    sel.classList.remove('border-gray-200');
    const colors = {qr:['border-blue-500','bg-blue-50','#2563eb'], cod:['border-green-500','bg-green-50','#16a34a']};
    const [bc, bg, dc] = colors[method] || colors.qr;
    sel.classList.add(bc, bg);
    sel.querySelector('.dot-outer').style.borderColor = dc;
    sel.querySelector('.dot-inner').style.opacity = '1';
    sel.querySelector('.dot-inner').style.backgroundColor = dc;
  }

  // Show/hide panels
  document.getElementById('cod-panel')?.classList.remove('active');
  if (method === 'qr') {
    // Không hiện panel QR nữa, sẽ redirect khi bấm xác nhận
  } else {
    document.getElementById('cod-panel')?.classList.add('active');
  }

  checkFormValidity();
}

// ---- DISCOUNT ----
function applyDiscount() {
  const inp = document.getElementById('discount-code');
  const btn = document.getElementById('btn-discount');
  const msg = document.getElementById('discount-msg');

  if (btn.textContent.trim() === 'Áp dụng') {
    const code = inp.value.trim().toUpperCase();
    if (!code) return;
    btn.textContent = '...';
    btn.disabled = true;

    fetch('{{ route("cart.apply-coupon") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ code: code, subtotal: subtotalVal })
    })
    .then(async (response) => {
      const payload = await response.json();
      if (!response.ok || !payload.success) {
        throw new Error(payload.message || 'Mã không hợp lệ!');
      }
      return payload;
    })
    .then((payload) => {
      discountVal = Number(payload.discount || 0);
      inp.readOnly = true;
      inp.classList.add('bg-green-50','border-green-400','text-green-700');
      btn.textContent = 'Xóa'; btn.disabled = false;
      btn.classList.replace('bg-gray-800','bg-red-500');
      msg.className = 'text-xs mt-2 font-medium text-green-600';
      msg.innerHTML = `<i class="fa-solid fa-circle-check mr-1"></i>${payload.message || 'Áp dụng mã thành công!'}`;
      msg.classList.remove('hidden');
      updateTotals();
    })
    .catch((error) => {
      discountVal = 0;
      btn.textContent = 'Áp dụng'; btn.disabled = false;
      msg.className = 'text-xs mt-2 font-medium text-red-500';
      msg.innerHTML = `<i class="fa-solid fa-circle-xmark mr-1"></i>${error.message}`;
      msg.classList.remove('hidden');
      updateTotals();
    });
  } else {
    fetch('{{ route("cart.apply-coupon") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ code: '' })
    })
    .then(r => r.json())
    .then(res => {
      discountVal = 0;
      inp.value = ''; inp.readOnly = false;
      inp.classList.remove('bg-green-50','border-green-400','text-green-700');
      btn.textContent = 'Áp dụng';
      btn.classList.replace('bg-red-500','bg-gray-800');
      msg.classList.add('hidden');
      updateTotals();
    });
  }
}

// ---- FORM VALIDITY ----
function checkFormValidity() {
  const nameInp = document.getElementById('inp-name');
  const phoneInp = document.getElementById('inp-phone');
  const addrInp = document.getElementById('inp-address');
  const noteInp = document.getElementById('inp-note');

  const name = nameInp ? nameInp.value : '';
  const phone = phoneInp ? phoneInp.value : '';
  const addr = addrInp ? addrInp.value : '';
  const note = noteInp ? noteInp.value : '';

  const errName = document.getElementById('err-name');
  const errPhone = document.getElementById('err-phone');
  const errAddr = document.getElementById('err-address');
  const errNote = document.getElementById('err-note');

  let nameValid = true;
  let phoneValid = true;
  let addrValid = true;
  let noteValid = true;

  // 1. Họ và tên validation
  if (name.length > 0 && /\d/.test(name)) {
    if (errName) {
      errName.textContent = 'Nhập họ và tên bằng chữ';
      errName.classList.remove('hidden');
    }
    nameValid = false;
  } else if (name.length > 0 && /[!@#$%^&*()_+=\[\]{}|\\:;"'<>,.?\/~`]/.test(name)) {
    if (errName) {
      errName.textContent = 'Họ và tên không được chứa ký tự đặc biệt';
      errName.classList.remove('hidden');
    }
    nameValid = false;
  } else if (name.trim().length > 0 && name.trim().length < 2) {
    if (errName) {
      errName.textContent = 'Họ và tên phải từ 2 ký tự trở lên';
      errName.classList.remove('hidden');
    }
    nameValid = false;
  } else if (name.trim().length > 50) {
    if (errName) {
      errName.textContent = 'Họ và tên tối đa 50 ký tự';
      errName.classList.remove('hidden');
    }
    nameValid = false;
  } else {
    if (errName) errName.classList.add('hidden');
    if (name.trim().length === 0) nameValid = false;
  }

  // 2. Số điện thoại validation
  if (/[a-zA-Z]/.test(phone)) {
    if (errPhone) {
      errPhone.textContent = 'Bạn chỉ nhập số';
      errPhone.classList.remove('hidden');
    }
    phoneValid = false;
  } else if (phone.length > 0 && !/^0[0-9]{8,9}$/.test(phone)) {
    if (errPhone) {
      errPhone.textContent = 'Số điện thoại phải từ 9-10 chữ số và bắt đầu bằng số 0';
      errPhone.classList.remove('hidden');
    }
    phoneValid = false;
  } else {
    if (errPhone) errPhone.classList.add('hidden');
    if (phone.length === 0) phoneValid = false;
  }

  // 3. Địa chỉ giao hàng validation
  const addrLen = addr.length;
  const counterAddr = document.getElementById('counter-address');
  if (counterAddr) {
    counterAddr.textContent = `${addrLen}/150`;
  }
  if (addrLen > 0 && addrLen < 10) {
    if (errAddr) {
      errAddr.textContent = 'Địa chỉ giao hàng phải từ 10 ký tự trở lên';
      errAddr.classList.remove('hidden');
    }
    addrValid = false;
  } else if (addrLen > 150) {
    if (errAddr) {
      errAddr.textContent = 'Địa chỉ giao hàng tối đa 150 ký tự';
      errAddr.classList.remove('hidden');
    }
    addrValid = false;
  } else if (addrLen > 0 && /[!@#$%^&*()_+=\[\]{}|\\:;"'<>?~`]/.test(addr)) {
    if (errAddr) {
      errAddr.textContent = 'Địa chỉ không chứa ký tự đặc biệt (ngoại trừ , . - /)';
      errAddr.classList.remove('hidden');
    }
    addrValid = false;
  } else {
    if (errAddr) errAddr.classList.add('hidden');
    if (addrLen === 0) addrValid = false;
  }

  // 4. Ghi chú validation
  const noteLen = note.length;
  const counterNote = document.getElementById('counter-note');
  if (counterNote) {
    counterNote.textContent = `${noteLen}/250`;
  }
  if (noteLen > 250) {
    if (errNote) {
      errNote.textContent = 'Ghi chú tối đa 250 ký tự';
      errNote.classList.remove('hidden');
    }
    noteValid = false;
  } else {
    if (errNote) errNote.classList.add('hidden');
  }

  const provSel = document.getElementById('inp-province');
  const provValid = true; // Bỏ qua validate province vì đã ẩn

  const btn = document.getElementById('btn-order');
  if (btn) {
    btn.disabled = !(nameValid && phoneValid && addrValid && noteValid);
  }
}

['inp-name', 'inp-phone', 'inp-address', 'inp-note'].forEach(id => {
  document.getElementById(id)?.addEventListener('input', checkFormValidity);
});
document.getElementById('inp-province')?.addEventListener('change', () => {
  calculateShipping();
  checkFormValidity();
});

// ---- SUBMIT ----
document.getElementById('checkout-form')?.addEventListener('submit', function (e) {
  e.preventDefault();

  const name = document.getElementById('inp-name').value.trim();
  const phone = document.getElementById('inp-phone').value.trim();
  const provSel = document.getElementById('inp-province');
  const province = provSel ? provSel.value : '';
  const addr = document.getElementById('inp-address').value.trim();
  const note = document.getElementById('inp-note').value.trim();
  const savedAddressId = document.getElementById('saved_address_id') ? document.getElementById('saved_address_id').value : '';
  const discountInp = document.getElementById('discount-code');
  const discountCode = discountInp && discountInp.readOnly ? discountInp.value.trim().toUpperCase() : '';

  const isNameInvalid = /\d/.test(name) || /[!@#$%^&*()_+=\[\]{}|\\:;"'<>,.?\/~`]/.test(name) || name.length < 2 || name.length > 50;
  const isPhoneInvalid = /[a-zA-Z]/.test(phone) || !/^0[0-9]{8,9}$/.test(phone);
  const isAddrInvalid = addr.length < 10 || addr.length > 150 || /[!@#$%^&*()_+=\[\]{}|\\:;"'<>?~`]/.test(addr);
  const isNoteInvalid = note.length > 250;

  if (isNameInvalid || isPhoneInvalid || isAddrInvalid || isNoteInvalid) {
    alert('Vui lòng kiểm tra lại thông tin nhập vào hợp lệ!');
    return;
  }

  const btn = document.getElementById('btn-order');
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Đang xử lý...';
  btn.disabled = true;

  const data = {
    name: name,
    phone: phone,
    province: province,
    address: addr,
    note: note,
    saved_address_id: savedAddressId,
    shipping_fee: shippingVal,
    payment_method: currentMethod,
    discount_code: discountCode
  };

  fetch('{{ route("cart.confirm") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(res => {
    if (res.status === 'success') {
      const badge = document.getElementById('headerCartBadge');
      if (badge) {
        fetch('{{ route("cart.count") }}')
          .then(r => r.json())
          .then(d => {
             badge.innerText = d.cart_count;
             if (d.cart_count === 0) badge.style.display = 'none';
          });
      }

      if (currentMethod === 'qr') {
        window.location.href = "{{ route('cart.qr') }}?order_id=" + res.order_id;
      } else {
        document.getElementById('success-overlay').classList.remove('hidden');
      }
    } else {
      // Hiện lỗi cụ thể từ server (validation errors hoặc logic errors)
      const msg = res.message || (res.errors ? Object.values(res.errors).flat().join('\n') : 'Đã xảy ra lỗi khi đặt hàng!');
      alert(msg);
      btn.innerHTML = '<i class="fa-solid fa-lock mr-2 text-sm"></i>XÁC NHẬN ĐẶT HÀNG';
      btn.disabled = false;
    }
  })
  .catch(err => {
    console.error(err);
    alert('Đã xảy ra lỗi hệ thống!');
    btn.innerHTML = '<i class="fa-solid fa-lock mr-2 text-sm"></i>XÁC NHẬN ĐẶT HÀNG';
    btn.disabled = false;
  });
});

// ---- INIT ----
document.addEventListener('DOMContentLoaded', () => {
  loadCart();
  selectMethod('cod');
  
  const initialCode = document.getElementById('discount-code').value.trim();
  if (initialCode) {
    applyDiscount();
  }
});
</script>
@endpush
