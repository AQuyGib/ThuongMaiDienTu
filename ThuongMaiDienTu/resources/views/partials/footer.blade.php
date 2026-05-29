<!-- Footer -->
<footer class="footer">
    <div class="container footer-grid">
        <div class="footer-col">
            <h4>{{ __('ui.footer_hotline') }}</h4>
            <ul>
                <li>{{ __('ui.footer_call_buy') }} <strong>1800.1060</strong> (7:30 - 22:00)</li>
                <li>{{ __('ui.footer_tech') }} <strong>1800.1763</strong> (7:30 - 22:00)</li>
                <li>{{ __('ui.footer_complaint') }} <strong>1800.1062</strong> (8:00 - 21:30)</li>
                <li>{{ __('ui.footer_warranty_line') }} <strong>1800.1064</strong> (8:00 - 21:00)</li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>{{ __('ui.footer_about') }}</h4>
            <ul>
                <li><a href="#">{{ __('ui.footer_about_company') }}</a></li>
                <li><a href="#">{{ __('ui.footer_careers') }}</a></li>
                <li><a href="#">{{ __('ui.footer_feedback') }}</a></li>
                <li><a href="#">{{ __('ui.footer_find_store') }}</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>{{ __('ui.footer_policies') }}</h4>
            <ul>
                <li><a href="{{ route('rewards.index') }}">{{ __('ui.footer_vip_points') }}</a></li>
                <li><a href="{{ route('policy.warranty') }}">{{ __('ui.footer_warranty_policy') }}</a></li>
                <li><a href="{{ route('policy.return') }}">{{ __('ui.footer_return_policy') }}</a></li>
                <li><a href="#">{{ __('ui.footer_privacy') }}</a></li>
            </ul>
        </div>
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
