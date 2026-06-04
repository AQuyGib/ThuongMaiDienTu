<!-- KHỐI FOOTER CHÂN TRANG (FOOTER SECTION)
     Bao gồm 4 cột chính hiển thị thông tin hotline, giới thiệu doanh nghiệp, chính sách cửa hàng và liên kết mạng xã hội.
     Tất cả nội dung được bản địa hóa động qua hệ thống đa ngôn ngữ `__('ui.key')`.
-->
@php
    $col2Links = [];
    if (!empty($globalSettings['footer_col_2_links'])) {
        try {
            $col2Links = json_decode($globalSettings['footer_col_2_links'], true) ?: [];
        } catch(\Exception $e) {}
    }
    
    $col3Links = [];
    if (!empty($globalSettings['footer_col_3_links'])) {
        try {
            $col3Links = json_decode($globalSettings['footer_col_3_links'], true) ?: [];
        } catch(\Exception $e) {}
    }
@endphp
<footer class="footer">
    <div class="container footer-grid">
        <!-- Cột 1: Thông tin số hotline hỗ trợ khách hàng -->
        <div class="footer-col">
            <h4>{{ $globalSettings['footer_col_1_title'] ?? __('ui.footer_hotline') }}</h4>
            <ul>
                <li>{{ __('ui.footer_call_buy') }} <strong>{{ $globalSettings['footer_hotline_buy'] ?? '1800.1060' }}</strong> (7:30 - 22:00)</li>
                <li>{{ __('ui.footer_tech') }} <strong>{{ $globalSettings['footer_hotline_tech'] ?? '1800.1763' }}</strong> (7:30 - 22:00)</li>
                <li>{{ __('ui.footer_complaint') }} <strong>{{ $globalSettings['footer_hotline_complaint'] ?? '1800.1062' }}</strong> (8:00 - 21:30)</li>
                <li>{{ __('ui.footer_warranty_line') }} <strong>{{ $globalSettings['footer_hotline_warranty'] ?? '1800.1064' }}</strong> (8:00 - 21:00)</li>
                @if(!empty($globalSettings['address']))
                    <li style="margin-top: 15px; font-size: 12px; line-height: 1.4;"><i class="fa-solid fa-location-dot" style="margin-right: 5px;"></i> {{ $globalSettings['address'] }}</li>
                @endif
                @if(!empty($globalSettings['email']))
                    <li style="font-size: 12px;"><i class="fa-solid fa-envelope" style="margin-right: 5px;"></i> {{ $globalSettings['email'] }}</li>
                @endif
            </ul>
        </div>

        <!-- Cột 2: Giới thiệu và thông tin tuyển dụng, cửa hàng -->
        <div class="footer-col">
            <h4>{{ $globalSettings['footer_col_2_title'] ?? __('ui.footer_about') }}</h4>
            <ul>
                @if(!empty($col2Links))
                    @foreach($col2Links as $lnk)
                        <li><a href="{{ $lnk['url'] }}">{{ $lnk['label'] }}</a></li>
                    @endforeach
                @else
                    <li><a href="#">{{ __('ui.footer_about_company') }}</a></li>
                    <li><a href="{{ route('products.index') }}">{{ __('ui.footer_all_products') }}</a></li>
                    <li><a href="{{ route('articles.index') }}">{{ __('ui.tech_news') }}</a></li>
                    <li><a href="{{ route('videos.index') }}">{{ __('ui.video_corner') }}</a></li>
                    <li><a href="#">{{ __('ui.footer_careers') }}</a></li>
                    <li><a href="#">{{ __('ui.footer_feedback') }}</a></li>
                    <li><a href="#">{{ __('ui.footer_find_store') }}</a></li>
                @endif
            </ul>
        </div>

        <!-- Cột 3: Các chính sách dịch vụ quan trọng (Có đường dẫn tích hợp) -->
        <div class="footer-col">
            <h4>{{ $globalSettings['footer_col_3_title'] ?? __('ui.footer_policies') }}</h4>
            <ul>
                @if(!empty($col3Links))
                    @foreach($col3Links as $lnk)
                        <li><a href="{{ $lnk['url'] }}">{{ $lnk['label'] }}</a></li>
                    @endforeach
                @else
                    <!-- Đường dẫn đến trang tích điểm và vòng quay đổi quà -->
                    <li><a href="{{ route('rewards.index') }}">{{ __('ui.footer_vip_points') }}</a></li>
                    <li><a href="{{ route('rewards.history') }}">{{ __('ui.footer_rewards_history') }}</a></li>
                    <!-- Các chính sách bảo hành và đổi trả tĩnh phục vụ cho Chatbot RAG tham chiếu -->
                    <li><a href="{{ route('policy.warranty') }}">{{ __('ui.footer_warranty_policy') }}</a></li>
                    <li><a href="{{ route('policy.return') }}">{{ __('ui.footer_return_policy') }}</a></li>
                    <li><a href="{{ route('warranty.index') }}">{{ __('ui.footer_warranty_lookup') }}</a></li>
                    <li><a href="{{ route('cart.tracking') }}">{{ __('ui.topbar_track_order') }}</a></li>
                    <li><a href="{{ route('compare.index') }}">{{ __('ui.footer_compare') }}</a></li>
                    <li><a href="{{ route('security') }}">{{ __('ui.footer_privacy') }}</a></li>
                @endif
            </ul>
        </div>

        <!-- Cột 4: Biểu tượng liên kết mạng xã hội (Social Media) -->
        <div class="footer-col">
            <h4>{{ $globalSettings['footer_col_4_title'] ?? __('ui.footer_connect') }}</h4>
            <div class="social-icons">
                @if(!empty($globalSettings['social_facebook']))
                    <a href="{{ $globalSettings['social_facebook'] }}" target="_blank"><i class="fa-brands fa-facebook"></i></a>
                @else
                    <i class="fa-brands fa-facebook"></i>
                @endif

                @if(!empty($globalSettings['social_youtube']))
                    <a href="{{ $globalSettings['social_youtube'] }}" target="_blank"><i class="fa-brands fa-youtube"></i></a>
                @else
                    <i class="fa-brands fa-youtube"></i>
                @endif

                @if(!empty($globalSettings['social_tiktok']))
                    <a href="{{ $globalSettings['social_tiktok'] }}" target="_blank"><i class="fa-brands fa-tiktok"></i></a>
                @else
                    <i class="fa-brands fa-tiktok"></i>
                @endif

                @if(!empty($globalSettings['social_instagram']))
                    <a href="{{ $globalSettings['social_instagram'] }}" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                @else
                    <i class="fa-brands fa-instagram"></i>
                @endif
            </div>
        </div>

        <!-- Cột 5: Đăng ký nhận thông tin khuyến mãi -->
        <div class="footer-col footer-col-subscribe">
            <h4>{{ $globalSettings['footer_col_5_title'] ?? __('ui.footer_subscribe') ?? 'Khuyến mãi' }}</h4>
            <p style="font-size: 12px; color: #555; margin-bottom: 12px; line-height: 1.4;">
                {!! $globalSettings['footer_subscribe_desc'] ?? 'Đăng ký ngay để nhận ưu đãi <strong>giảm 10%</strong> cho đơn hàng đầu tiên!' !!}
            </p>
            <form action="#" method="POST" style="display:flex; flex-direction:column; gap:8px;" onsubmit="event.preventDefault(); showPromoSuccess();">
                <input type="email" placeholder="Email của bạn *" required style="width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size:12px; outline:none; background:#f8f9fa;">
                <input type="tel" placeholder="Số điện thoại *" required style="width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size:12px; outline:none; background:#f8f9fa;">
                <div style="display:flex; align-items:flex-start; gap:6px; margin: 4px 0;">
                    <input type="checkbox" id="footerAgreeTerms" required style="margin-top:2px; cursor:pointer; scale: 0.9;">
                    <label for="footerAgreeTerms" style="font-size:11px; color:#666; cursor:pointer; line-height: 1.2;">Đồng ý với các <a href="#" style="color:#0046ab; text-decoration:underline;">điều khoản</a></label>
                </div>
                <button type="submit" style="background:#0046ab; color:#fff; border:none; padding:10px; border-radius:6px; font-weight:bold; cursor:pointer; font-size:12px; text-transform:uppercase; transition:0.2s;">Đăng ký nhận mã</button>
            </form>
        </div>
    </div>

    <!-- Hàng liên kết nhanh các từ khóa sản phẩm phổ biến (CellphoneS Style) -->
    <div class="container footer-quick-links" style="margin-top: 40px; padding-top: 25px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #777; line-height: 2.0; font-family: sans-serif;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div>
                <a href="{{ route('search.index', ['query' => 'iPhone Air']) }}">iPhone Air</a> |
                <a href="{{ route('search.index', ['query' => 'iPhone 17']) }}">iPhone 17</a> |
                <a href="{{ route('search.index', ['query' => 'iPhone 17 Pro']) }}">iPhone 17 Pro</a> <br>
                <a href="{{ route('search.index', ['query' => 'iPhone 17 Pro Max']) }}">iPhone 17 Pro Max</a> |
                <a href="{{ route('search.index', ['query' => 'iPhone 16 Pro Max']) }}">iPhone 16 Pro Max</a> <br>
                <a href="{{ route('search.index', ['query' => 'iPhone 16']) }}">iPhone 16</a> |
                <a href="{{ route('search.index', ['query' => 'iPhone cũ']) }}">iPhone cũ</a> |
                <a href="{{ route('search.index', ['query' => 'Macbook Neo']) }}">Macbook Neo</a>
            </div>
            <div>
                <a href="{{ route('products.category', 'dien-thoai') }}">Điện thoại</a> |
                <a href="{{ route('products.category', 'iphone') }}">Điện thoại iPhone</a> |
                <a href="{{ route('products.category', 'xiaomi') }}">Xiaomi</a> <br>
                <a href="{{ route('search.index', ['query' => 'Samsung Galaxy']) }}">Điện thoại Samsung Galaxy</a> |
                <a href="{{ route('products.category', 'oppo') }}">Điện thoại OPPO</a> <br>
                <a href="{{ route('search.index', ['query' => 'OPPO Find X9s']) }}">OPPO Find X9s</a> |
                <a href="{{ route('search.index', ['query' => 'OPPO Find X9 Ultra']) }}">OPPO Find X9 Ultra</a>
            </div>
            <div>
                <a href="{{ route('products.category', 'laptop') }}">Laptop</a> |
                <a href="{{ route('search.index', ['query' => 'Acer']) }}">Laptop Acer</a> |
                <a href="{{ route('search.index', ['query' => 'Dell']) }}">Laptop Dell</a> |
                <a href="{{ route('search.index', ['query' => 'HP']) }}">Laptop HP</a> <br>
                <a href="{{ route('products.category', 'tivi-man-hinh') }}">Tivi</a> |
                <a href="{{ route('search.index', ['query' => 'Tivi Samsung']) }}">Tivi Samsung</a> |
                <a href="{{ route('search.index', ['query' => 'Tivi Sony']) }}">Tivi Sony</a> |
                <a href="{{ route('search.index', ['query' => 'Tivi LG']) }}">Tivi LG</a>
            </div>
            <div>
                <a href="{{ route('products.category', 'gia-dung-smarthome') }}">Đồ gia dụng</a> |
                <a href="{{ route('search.index', ['query' => 'Máy hút bụi']) }}">Máy hút bụi gia đình</a> |
                <a href="{{ route('search.index', ['query' => 'Build PC']) }}">Build PC</a> <br>
                <a href="{{ route('search.index', ['query' => 'Camera']) }}">Camera</a> |
                <a href="{{ route('search.index', ['query' => 'Trả góp']) }}">Trả góp</a> |
                <a href="{{ route('search.index', ['query' => 'Xiaomi 17T']) }}">Xiaomi 17T</a>
            </div>
        </div>
    </div>

    <div class="container footer-copyright-bar" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; text-align: center; opacity: 0.8; font-family: sans-serif; clear: both; width: 100%;">
        <p>{{ $globalSettings['footer_copyright'] ?? ('© ' . date('Y') . ' ' . ($globalSettings['site_name'] ?? 'DIENMAYPRO') . ' - DESIGNED BY DIENMAYPRO. All Rights Reserved.') }}</p>
    </div>

    <style>
        .footer-quick-links a {
            color: #666;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .footer-quick-links a:hover {
            color: #d70018 !important;
            text-decoration: underline;
        }
    </style>
</footer>

{{-- Promo Success Modal --}}
<div id="promoSuccessModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 10001; justify-content: center; align-items: center; display: none;">
    <div style="background: #fff; padding: 40px; border-radius: 12px; text-align: center; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <i class="fa-solid fa-circle-check" style="font-size: 60px; color: #16a34a; margin-bottom: 20px;"></i>
        <h3 style="font-size: 22px; color: #333; margin-bottom: 10px;">Cảm ơn quý khách!</h3>
        <p style="font-size: 15px; color: #555; line-height: 1.5; margin-bottom: 0;">Đăng ký nhận khuyến mãi thành công. Chúng tôi sẽ gửi mã giảm giá 10% qua Email và Số điện thoại của quý khách.</p>
    </div>
</div>

<script>
// Hiển thị modal đăng ký thành công ưu đãi nhận mã giảm giá
function showPromoSuccess() {
    const modal = document.getElementById('promoSuccessModal');
    if (modal) {
        modal.style.display = 'flex';
        // Reset all forms that might match
        document.querySelectorAll('form').forEach(form => {
            if (form.querySelector('input[type="email"]')) {
                form.reset();
            }
        });
        setTimeout(() => {
            modal.style.display = 'none';
        }, 2500);
    }
}
</script>
