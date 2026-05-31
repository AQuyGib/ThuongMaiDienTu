@extends('layouts.app')
@section('title', 'Xác nhận đơn hàng')

@section('content')
<div class="bg-gray-50 min-h-screen py-10">
  <div class="max-w-4xl mx-auto px-4">
    <!-- Khung chính thông báo đặt hàng thành công -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
      
      <!-- Banner màu xanh lá báo thành công -->
      <div class="bg-green-600 text-white px-6 py-5">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full bg-white/15 flex items-center justify-center">
            <i class="fa-solid fa-circle-check text-2xl"></i>
          </div>
          <div>
            <h1 class="text-2xl font-bold">Đặt hàng thành công</h1>
            <p class="text-green-100 text-sm mt-1">Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đã được ghi nhận.</p>
          </div>
        </div>
      </div>

      <!-- Layout 3 cột phân vùng chi tiết:
           - 2 cột trái: Thông tin giao nhận và Danh sách sản phẩm mua.
           - 1 cột phải: Tóm tắt thanh toán, Điểm thành viên và Nút điều hướng.
      -->
      <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- CỘT TRÁI & GIỮA: CHI TIẾT ĐƠN HÀNG VÀ CÁC SẢN PHẨM -->
        <div class="lg:col-span-2 space-y-5">
          
          <!-- Phần 1: Thông tin khách hàng & Vận chuyển -->
          <div class="border rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
              <h2 class="font-bold text-gray-800">Thông tin đơn hàng</h2>
              <span class="text-sm text-gray-500">#{{ $order->order_code ?? $order->order_id }}</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div><span class="text-gray-500">Người nhận:</span> <span class="font-semibold text-gray-800">{{ $order->customer_name ?? '' }}</span></div>
              <div><span class="text-gray-500">Số điện thoại:</span> <span class="font-semibold text-gray-800">{{ $order->customer_phone ?? '' }}</span></div>
              <div class="md:col-span-2"><span class="text-gray-500">Địa chỉ:</span> <span class="font-semibold text-gray-800">{{ $order->shipping_address ?? '' }}</span></div>
              <div><span class="text-gray-500">Thanh toán:</span> <span class="font-semibold text-gray-800">{{ $order->payment_method }}</span></div>
              <div><span class="text-gray-500">Trạng thái:</span> <span class="font-semibold text-amber-600">{{ $order->status }}</span></div>
            </div>
          </div>

          <!-- Phần 2: Danh sách các sản phẩm và số lượng tương ứng trong đơn hàng -->
          <div class="border rounded-2xl p-5">
            <h2 class="font-bold text-gray-800 mb-4">Sản phẩm đã đặt</h2>
            <div class="space-y-3">
              @foreach ($order->details as $detail)
                <div class="flex items-center gap-4 border-b pb-3 last:border-0 last:pb-0">
                  <!-- Ảnh đại diện sản phẩm -->
                  <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                    @if (!empty($detail->inventoryItem?->variant?->image) || !empty($detail->inventoryItem?->variant?->thumbnail))
                      <img src="{{ $detail->inventoryItem->variant->image ?? $detail->inventoryItem->variant->thumbnail }}" alt="{{ $detail->product_name }}" class="w-full h-full object-cover">
                    @else
                      <i class="fa-solid fa-box text-gray-400"></i>
                    @endif
                  </div>
                  <!-- Thông tin chi tiết: Tên sản phẩm, số lượng, đơn giá -->
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 truncate">{{ $detail->product_name ?? ('Mã sản phẩm #' . $detail->item_id) }}</p>
                    <p class="text-gray-500 text-sm">Số lượng: {{ $detail->quantity ?? 1 }}</p>
                    <p class="text-gray-500 text-sm">Đơn giá: {{ number_format($detail->unit_price ?? $detail->price) }}đ</p>
                  </div>
                  <!-- Thành tiền của dòng hàng -->
                  <span class="font-bold text-gray-800">{{ number_format($detail->price) }}đ</span>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <!-- CỘT PHẢI: BẢNG TỔNG KẾT TÀI CHÍNH & TÍCH LŨY THÀNH VIÊN -->
        <div class="space-y-5">
          <!-- Phần 3: Phân tích các nguồn chiết khấu và tổng số tiền thanh toán cuối cùng -->
          <div class="border rounded-2xl p-5 bg-gray-50">
            <h2 class="font-bold text-gray-800 mb-4">Tóm tắt thanh toán</h2>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between"><span class="text-gray-500">Tạm tính</span><span>{{ number_format($order->total_amount) }}đ</span></div>
              <div class="flex justify-between"><span class="text-gray-500">Phí giao hàng</span><span>{{ number_format($order->shipping_fee) }}đ</span></div>
              <div class="flex justify-between"><span class="text-gray-500">Giảm giá</span><span class="text-green-600">-{{ number_format($order->discount_amount ?? 0) }}đ</span></div>
              <div class="flex justify-between"><span class="text-gray-500">Điểm tiêu dùng</span><span class="text-green-600">-{{ number_format(($order->wallet_points_used ?? 0) * \App\Services\PointsService::POINT_VALUE) }}đ</span></div>
              <div class="flex justify-between pt-3 border-t font-bold text-lg"><span>Thành tiền</span><span class="text-red-600">{{ number_format($order->final_amount) }}đ</span></div>
            </div>
          </div>

          <!-- Phần 4: Số lượng điểm thưởng nhận lại được sau đơn hàng này (Loyalty Points Earned) -->
          <div class="border rounded-2xl p-5">
            <h2 class="font-bold text-gray-800 mb-3">Điểm tích lũy</h2>
            <div class="text-sm space-y-2">
              <div class="flex justify-between"><span class="text-gray-500">Điểm tiêu dùng nhận được</span><span class="font-semibold">{{ number_format($order->wallet_points_earned ?? 0) }}</span></div>
              <div class="flex justify-between"><span class="text-gray-500">Điểm rank nhận được</span><span class="font-semibold">{{ number_format($order->rank_points_earned ?? 0) }}</span></div>
            </div>
          </div>

          <!-- Các liên kết điều hướng tiếp tục -->
          <a href="{{ route('home') }}" class="block text-center w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">Về trang chủ</a>
          <a href="{{ route('cart.tracking') }}" class="block text-center w-full bg-gray-100 text-gray-800 py-3 rounded-xl font-bold hover:bg-gray-200 transition">Theo dõi đơn hàng</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
