<!-- KHỐI FOOTER CHÂN TRANG (FOOTER SECTION)
     Bao gồm 4 cột chính hiển thị thông tin hotline, giới thiệu doanh nghiệp, chính sách cửa hàng và liên kết mạng xã hội.
     Tất cả nội dung được bản địa hóa động qua hệ thống đa ngôn ngữ `__('ui.key')`.
-->
<footer class="footer">
    <div class="container footer-grid">
        <!-- Cột 1: Thông tin số hotline hỗ trợ khách hàng -->
        <div class="footer-col">
            <h4>{{ __('ui.footer_hotline') }}</h4>
            <ul>
                <li>{{ __('ui.footer_call_buy') }} <strong>1800.1060</strong> (7:30 - 22:00)</li>
                <li>{{ __('ui.footer_tech') }} <strong>1800.1763</strong> (7:30 - 22:00)</li>
                <li>{{ __('ui.footer_complaint') }} <strong>1800.1062</strong> (8:00 - 21:30)</li>
                <li>{{ __('ui.footer_warranty_line') }} <strong>1800.1064</strong> (8:00 - 21:00)</li>
            </ul>
        </div>

        <!-- Cột 2: Giới thiệu và thông tin tuyển dụng, cửa hàng -->
        <div class="footer-col">
            <h4>{{ __('ui.footer_about') }}</h4>
            <ul>
                <li><a href="#">{{ __('ui.footer_about_company') }}</a></li>
                <li><a href="#">{{ __('ui.footer_careers') }}</a></li>
                <li><a href="#">{{ __('ui.footer_feedback') }}</a></li>
                <li><a href="#">{{ __('ui.footer_find_store') }}</a></li>
            </ul>
        </div>

        <!-- Cột 3: Các chính sách dịch vụ quan trọng (Có đường dẫn tích hợp) -->
        <div class="footer-col">
            <h4>{{ __('ui.footer_policies') }}</h4>
            <ul>
                <!-- Đường dẫn đến trang tích điểm và vòng quay đổi quà -->
                <li><a href="{{ route('rewards.index') }}">{{ __('ui.footer_vip_points') }}</a></li>
                <!-- Các chính sách bảo hành và đổi trả tĩnh phục vụ cho Chatbot RAG tham chiếu -->
                <li><a href="{{ route('policy.warranty') }}">{{ __('ui.footer_warranty_policy') }}</a></li>
                <li><a href="{{ route('policy.return') }}">{{ __('ui.footer_return_policy') }}</a></li>
                <li><a href="#">{{ __('ui.footer_privacy') }}</a></li>
            </ul>
        </div>

        <!-- Cột 4: Biểu tượng liên kết mạng xã hội (Social Media) -->
        <div class="footer-col">
            <h4>{{ __('ui.footer_connect') }}</h4>
            <div class="social-icons">
                <i class="fa-brands fa-facebook"></i>
                <i class="fa-brands fa-youtube"></i>
                <i class="fa-brands fa-tiktok"></i>
                <i class="fa-brands fa-instagram"></i>
            </div>
        </div>
    </div>
</footer>
