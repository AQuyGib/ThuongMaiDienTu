{{-- ============================================================
CHATBOT AI - Cửa sổ chat Floating + FAB Button
============================================================
Tích hợp Google Gemini API qua Backend RAG.
- Cửa sổ chat floating ở góc dưới phải
- Nút FAB (Floating Action Button) với animation ping
- Gợi ý nhanh (Quick messages)
- Tự nhận diện context sản phẩm đang xem
============================================================ --}}

{{-- CSS cho AI Chat --}}
<style>
    /* Cửa sổ chat - fixed ở góc dưới phải */
    #ai-chat-window {
        display: none;
        position: fixed;
        bottom: 80px;
        right: 10px;
        width: calc(100% - 20px);
        max-width: 380px;
        height: 520px;
        max-height: 80vh;
        background: var(--white, #fff);
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 70, 171, 0.1);
        z-index: 10002;
        flex-direction: column;
        overflow: hidden;
    }

    @media (min-width: 768px) {
        #ai-chat-window {
            bottom: 90px;
            right: 24px;
            height: 560px;
        }
    }

    #ai-chat-window.active {
        display: flex;
        animation: chatSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes chatSlideUp {
        from {
            opacity: 0;
            transform: translateY(24px) scale(0.96);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Header chat gradient */
    .chatbot-header {
        background: linear-gradient(135deg, var(--primary-color, #0046ab) 0%, #0061f2 100%);
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    /* Khu vực tin nhắn */
    .chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        scroll-behavior: smooth;
    }

    .chatbot-messages::-webkit-scrollbar {
        width: 4px;
    }

    .chatbot-messages::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    /* Bong bóng tin nhắn */
    .chatbot-msg {
        max-width: 85%;
        padding: 10px 14px;
        font-size: 13.5px;
        line-height: 1.6;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        overflow-wrap: break-word;
        word-break: break-word;
    }

    .chatbot-msg.user {
        align-self: flex-end;
        background: linear-gradient(135deg, #0046ab 0%, #0061f2 100%);
        color: white;
        border-radius: 16px 16px 4px 16px;
    }

    .chatbot-msg.ai {
        align-self: flex-start;
        background: var(--white, #fff);
        color: #1f2937;
        border-radius: 16px 16px 16px 4px;
        border: 1px solid #e5e7eb;
    }

    .chatbot-msg.ai b {
        color: var(--primary-color, #0046ab);
    }

    /* Link sản phẩm dạng chip nổi bật */
    .chatbot-msg.ai a.chatbot-product-link {
        display: inline-block;
        color: var(--primary-color, #0046ab);
        font-weight: 600;
        font-size: 12px;
        background: #eff6ff;
        padding: 2px 10px;
        border-radius: 12px;
        border: 1px solid #dbeafe;
        text-decoration: none;
        transition: all 0.2s;
        margin: 1px 0;
    }

    .chatbot-msg.ai a.chatbot-product-link:hover {
        background: var(--primary-color, #0046ab);
        color: white;
        border-color: var(--primary-color, #0046ab);
    }

    /* Loading dots animation */
    .chatbot-loading-dots span {
        display: inline-block;
        width: 7px;
        height: 7px;
        background: #94a3b8;
        border-radius: 50%;
        margin: 0 2px;
        animation: chatBounce 1.4s infinite ease-in-out both;
    }

    .chatbot-loading-dots span:nth-child(1) {
        animation-delay: -0.32s;
    }

    .chatbot-loading-dots span:nth-child(2) {
        animation-delay: -0.16s;
    }

    @keyframes chatBounce {

        0%,
        80%,
        100% {
            transform: scale(0);
        }

        40% {
            transform: scale(1.0);
        }
    }

    /* Quick message buttons */
    .chatbot-quick-btn {
        display: inline-block;
        padding: 6px 12px;
        background: #eff6ff;
        color: var(--primary-color, #0046ab);
        font-size: 11px;
        font-weight: 600;
        border-radius: 20px;
        border: 1px solid #dbeafe;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .chatbot-quick-btn:hover {
        background: #dbeafe;
        border-color: var(--primary-color, #0046ab);
        transform: translateY(-1px);
    }

    /* Input area */
    .chatbot-input-area {
        padding: 12px 16px;
        background: var(--white, #fff);
        border-top: 1px solid #f1f5f9;
        display: flex;
        gap: 8px;
        align-items: center;
        flex-shrink: 0;
    }

    .chatbot-input {
        flex: 1;
        font-size: 13px;
        background: #f1f5f9;
        border: 2px solid transparent;
        border-radius: 24px;
        padding: 10px 16px;
        outline: none;
        transition: all 0.2s;
        font-family: 'Inter', sans-serif;
    }

    .chatbot-input:focus {
        border-color: var(--primary-color, #0046ab);
        background: var(--white, #fff);
        box-shadow: 0 0 0 3px rgba(0, 70, 171, 0.1);
    }

    .chatbot-send-btn {
        width: 38px;
        height: 38px;
        background: linear-gradient(135deg, #0046ab 0%, #0061f2 100%);
        color: white;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0, 70, 171, 0.3);
    }

    .chatbot-send-btn:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 12px rgba(0, 70, 171, 0.4);
    }

    /* FAB Button */
    .chatbot-fab {
        position: fixed;
        bottom: 16px;
        right: 16px;
        z-index: 10001;
        cursor: pointer;
    }

    @media (min-width: 768px) {
        .chatbot-fab {
            bottom: 24px;
            right: 24px;
        }
    }

    .chatbot-fab-inner {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #0046ab 0%, #0061f2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 20px rgba(0, 70, 171, 0.35);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
    }

    .chatbot-fab-inner:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 28px rgba(0, 70, 171, 0.45);
    }

    .chatbot-fab-inner i {
        font-size: 24px;
        color: white;
    }

    /* Ping effect */
    .chatbot-fab-ping {
        position: absolute;
        top: 0;
        right: 0;
        display: flex;
        width: 14px;
        height: 14px;
    }

    .chatbot-fab-ping .ping-wave {
        position: absolute;
        display: inline-flex;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #ef4444;
        opacity: 0.75;
        animation: chatPing 1.2s cubic-bezier(0, 0, 0.2, 1) infinite;
    }

    .chatbot-fab-ping .ping-dot {
        position: relative;
        display: inline-flex;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #ef4444;
        border: 2px solid white;
    }

    @keyframes chatPing {

        75%,
        100% {
            transform: scale(2);
            opacity: 0;
        }
    }

    /* Pending payment alert container */
    #pending-payment-alert {
        position: fixed;
        bottom: 80px;
        right: 16px;
        z-index: 10000;
        display: none;
        align-items: center;
        gap: 10px;
        animation: slideInUpAlert 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    @media (min-width: 768px) {
        #pending-payment-alert {
            bottom: 92px;
            right: 24px;
        }
    }

    @keyframes slideInUpAlert {
        from {
            opacity: 0;
            transform: translateY(15px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Pulse effect for the alert message */
    .alert-pulse {
        animation: alertPulse 2s infinite ease-in-out;
    }

    @keyframes alertPulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.02);
        }
    }
</style>

{{-- Cửa sổ Chat AI --}}
<div id="ai-chat-window">
    {{-- Header --}}
    <div class="chatbot-header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div
                style="width: 34px; height: 34px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px;">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div>
                <div style="font-weight: 700; font-size: 14px; color: white;">{{ __('ui.chatbot_title') }}</div>
                <div
                    style="font-size: 10px; color: rgba(255,255,255,0.7); display: flex; align-items: center; gap: 4px;">
                    <span
                        style="width: 6px; height: 6px; background: #4ade80; border-radius: 50%; display: inline-block;"></span>
                    {{ __('ui.chatbot_status') }}
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 6px; align-items: center;">
            <button onclick="clearChat()" title="{{ __('ui.chatbot_clear_tooltip') }}"
                style="background: rgba(255,255,255,0.15); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;"
                onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="fa-solid fa-trash-can" style="font-size: 14px;"></i>
            </button>
            <button onclick="chatbotToggle()"
                style="background: rgba(255,255,255,0.15); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;"
                onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="fa-solid fa-xmark" style="font-size: 16px;"></i>
            </button>
        </div>
    </div>

    @guest
        {{-- Giao diện yêu cầu đăng nhập --}}
        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; text-align: center; background: #fff;">
            <div style="width: 56px; height: 56px; background: rgba(0, 70, 171, 0.1); color: #0046ab; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h4 style="font-weight: 700; font-size: 15px; color: #1e293b; margin-bottom: 6px;">Yêu Cầu Đăng Nhập</h4>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 16px; line-height: 1.5; max-width: 220px;">Vui lòng đăng nhập để trò chuyện và đặt lịch sửa chữa với Trợ lý AI.</p>
            <a href="{{ route('login') }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: linear-gradient(135deg, #0046ab 0%, #0061f2 100%); color: white; padding: 8px 18px; border-radius: 20px; font-size: 12px; font-weight: 600; text-decoration: none; transition: 0.2s; box-shadow: 0 4px 10px rgba(0, 70, 171, 0.2);" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                <i class="fa-solid fa-right-to-bracket"></i> Đăng nhập ngay
            </a>
        </div>
    @else
        @if(auth()->user()->chatbot_banned_until && auth()->user()->chatbot_banned_until > now())
            {{-- Giao diện tài khoản bị cấm Chatbot --}}
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; text-align: center; background: #fff;">
                <div style="width: 56px; height: 56px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                    <i class="fa-solid fa-user-slash"></i>
                </div>
                <h4 style="font-weight: 700; font-size: 15px; color: #1e293b; margin-bottom: 6px;">Tính Năng Bị Khóa</h4>
                <p style="font-size: 12px; color: #64748b; margin-bottom: 12px; line-height: 1.5; max-width: 220px;">Quyền truy cập Trợ lý AI của bạn bị khóa tạm thời do vi phạm chính sách gửi tin nhắn (spam).</p>
                <span style="font-size: 10px; background: #fee2e2; color: #b91c1c; padding: 3px 8px; border-radius: 12px; font-weight: 600;">
                    Mở khóa lúc: {{ \Carbon\Carbon::parse(auth()->user()->chatbot_banned_until)->format('d/m/Y H:i') }}
                </span>
            </div>
        @else
            {{-- Khu vực tin nhắn --}}
            <div class="chatbot-messages" id="chatbot-messages"></div>

            {{-- Gợi ý nhanh --}}
            <div
                style="padding: 8px 12px; background: white; overflow-x: auto; white-space: nowrap; border-top: 1px solid #f1f5f9; display: flex; gap: 6px;">
                <button class="chatbot-quick-btn"
                    onclick="chatbotQuickMsg('{{ addslashes(__('ui.chatbot_quick_phone_query')) }}')">{{ __('ui.chatbot_quick_phone_btn') }}</button>
                <button class="chatbot-quick-btn"
                    onclick="chatbotQuickMsg('{{ addslashes(__('ui.chatbot_quick_laptop_query')) }}')">{{ __('ui.chatbot_quick_laptop_btn') }}</button>
                <button class="chatbot-quick-btn"
                    onclick="chatbotQuickMsg('{{ addslashes(__('ui.chatbot_quick_promo_query')) }}')">{{ __('ui.chatbot_quick_promo_btn') }}</button>
            </div>

            {{-- Input gửi tin nhắn --}}
            <div class="chatbot-input-area">
                <input type="text" id="chatbot-input" class="chatbot-input" placeholder="{{ __('ui.chatbot_placeholder') }}"
                    maxlength="500" onkeypress="if(event.key==='Enter') chatbotSend()">
                <button id="chatbot-send-btn" class="chatbot-send-btn" onclick="chatbotSend()">
                    <i class="fa-solid fa-paper-plane" style="font-size: 14px;"></i>
                </button>
            </div>
        @endif
    @endguest
</div>

{{-- Alert giỏ hàng chờ thanh toán --}}
<div id="pending-payment-alert">
    <!-- Message box -->
    <a href="#" id="pending-payment-link" class="alert-pulse shadow-lg"
        style="background: #ef4444; color: white; font-weight: 600; padding: 10px 16px; border-radius: 12px; font-size: 12px; white-space: nowrap; text-decoration: none; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(255,255,255,0.2);">
        <span
            style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: white; animation: chatPing 1s infinite;"></span>
        Bạn đang có đơn chờ thanh toán
    </a>
    <!-- Cart button icon -->
    <a href="#" id="pending-payment-btn"
        style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #ef4444 0%, #ff5722 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4); text-decoration: none; transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <i class="fa-solid fa-cart-shopping" style="font-size: 20px; color: white;"></i>
    </a>
</div>

{{-- Nút FAB mở chat --}}
<div class="chatbot-fab" onclick="chatbotToggle()" id="chatbot-fab">
    <div class="chatbot-fab-inner">
        <i class="fa-solid fa-robot"></i>
        <span class="chatbot-fab-ping">
            <span class="ping-wave"></span>
            <span class="ping-dot"></span>
        </span>
    </div>
</div>

{{-- DOMPurify Library for DOM XSS prevention --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.9/purify.min.js"></script>

{{-- JavaScript Chatbot --}}
<script>
    // Sử dụng biểu thức tự chạy (IIFE) để đóng gói mã nguồn, tránh rò rỉ biến ra phạm vi global
    (function () {
        'use strict';

        // Lấy các phần tử giao diện từ DOM
        const chatWindow = document.getElementById('ai-chat-window');
        const chatMessages = document.getElementById('chatbot-messages');
        const chatInput = document.getElementById('chatbot-input');
        const chatFab = document.getElementById('chatbot-fab');
        const chatSendBtn = document.getElementById('chatbot-send-btn');

        let hasWelcomed = false; // Đánh dấu đã hiển thị tin nhắn chào mừng chưa
        let messageList = []; // Mảng lưu lịch sử tin nhắn trong phiên làm việc hiện tại

        const HISTORY_KEY = 'chatbot_history'; // Key lưu trữ lịch sử tin nhắn trong LocalStorage
        const SESSION_TTL = 60 * 60 * 1000; // Thời hạn hiệu lực của một phiên chat (60 phút)

        /**
         * Lưu danh sách tin nhắn vào LocalStorage kèm theo thời gian hết hạn (Expires).
         * @param {Array} messages Danh sách các tin nhắn cần lưu
         */
        function saveHistory(messages) {
            try {
                const data = {
                    messages: messages,
                    expires_at: Date.now() + SESSION_TTL
                };
                localStorage.setItem(HISTORY_KEY, JSON.stringify(data));
            } catch (e) {
                console.warn('Cannot save chatbot history:', e);
            }
        }

        /**
         * Tải danh sách tin nhắn cũ từ LocalStorage nếu chưa bị hết hạn.
         * Tự động xóa lịch sử và trả về null nếu phiên chat đã quá hạn 60 phút.
         * @return {Array|null} Trả về danh sách tin nhắn hoặc null
         */
        function loadHistory() {
            try {
                const raw = localStorage.getItem(HISTORY_KEY);
                if (!raw) return null;

                const data = JSON.parse(raw);
                if (!data || !data.expires_at || !Array.isArray(data.messages)) {
                    localStorage.removeItem(HISTORY_KEY);
                    return null;
                }

                // Kiểm tra xem thời gian hiện tại đã vượt quá thời gian hết hạn của phiên chat chưa
                if (Date.now() > data.expires_at) {
                    localStorage.removeItem(HISTORY_KEY);
                    return null;
                }

                // Tự động gia hạn thêm 60 phút kể từ thời điểm khách hàng tiếp tục tương tác
                data.expires_at = Date.now() + SESSION_TTL;
                localStorage.setItem(HISTORY_KEY, JSON.stringify(data));

                return data.messages;
            } catch (e) {
                localStorage.removeItem(HISTORY_KEY);
                return null;
            }
        }

        /**
         * Khởi tạo hoặc khôi phục lại lịch sử trò chuyện khi khách tải lại trang web.
         * Đảm bảo tính nhất quán ngôn ngữ: Nếu khách chuyển đổi ngôn ngữ giao diện (Vi/En),
         * hệ thống tự động xóa lịch sử cũ để tránh hiện tin nhắn cũ sai ngôn ngữ.
         */
        function initChatSession() {
            const currentLocale = document.documentElement.lang || 'vi';
            const savedLocale = localStorage.getItem('chatbot_locale');
            if (savedLocale && savedLocale !== currentLocale) {
                localStorage.removeItem(HISTORY_KEY);
            }
            localStorage.setItem('chatbot_locale', currentLocale);

            /**
             * Xóa cache lịch sử chat khi phát hiện thay đổi tài khoản người dùng.
             * Blade render sẵn ID tài khoản hiện tại (hoặc 'guest' nếu chưa đăng nhập) vào biến JS.
             * So sánh với ID đã lưu trong localStorage từ phiên trước:
             *   - Nếu khác nhau (ví dụ: User A logout → User B login) → xóa toàn bộ lịch sử chat cũ
             *     để đảm bảo tài khoản mới không thấy nội dung hội thoại của tài khoản trước đó.
             *   - Nếu giống nhau → giữ nguyên lịch sử chat, không làm gì thêm.
             * Luôn cập nhật lại chatbot_user_id sau khi kiểm tra để đồng bộ cho lần tải tiếp theo.
             */
            const currentUserId = "{{ auth()->check() ? auth()->id() : 'guest' }}";
            const savedUserId = localStorage.getItem('chatbot_user_id');
            if (savedUserId && savedUserId !== currentUserId) {
                localStorage.removeItem(HISTORY_KEY);
            }
            localStorage.setItem('chatbot_user_id', currentUserId);

            const cached = loadHistory();
            if (cached && cached.length > 0) {
                messageList = cached;
                hasWelcomed = true;
                // Render lại toàn bộ bong bóng chat cũ mà không kích hoạt lưu đè lên LocalStorage
                cached.forEach(msg => {
                    appendMsg(msg.text, msg.role, false);
                });
            }
        }

        /**
         * Kiểm tra đơn hàng đang chờ thanh toán qua mã QR (QR Code Payment).
         * Nếu phát hiện có đơn hàng chưa hoàn tất thanh toán lưu ở LocalStorage,
         * hệ thống hiển thị một banner thông báo nổi (Alert) ngay phía trên nút robot chat.
         */
        function checkPendingPayment() {
            const orderId = localStorage.getItem('pending_payment_order_id');
            const alertEl = document.getElementById('pending-payment-alert');

            // Bảo mật giao diện: Không hiển thị banner cảnh báo này nếu khách hàng đang đứng ở trang thanh toán QR
            const isQRPage = window.location.pathname.includes('/maQR') || window.location.pathname.includes('/ma-qr');

            if (orderId && !isQRPage) {
                if (alertEl) {
                    alertEl.style.display = 'flex';
                    const link = `/maQR?order_id=${orderId}`;

                    const linkEl = document.getElementById('pending-payment-link');
                    const btnEl = document.getElementById('pending-payment-btn');
                    if (linkEl) linkEl.href = link;
                    if (btnEl) btnEl.href = link;
                }
            } else {
                if (alertEl) {
                    alertEl.style.display = 'none';
                }
            }
        }

        // Tự động khôi phục phiên chat và quét đơn hàng chờ thanh toán khi hoàn tất tải DOM
        document.addEventListener('DOMContentLoaded', () => {
            if (chatMessages) {
                initChatSession();
            }
            checkPendingPayment();
        });

        /**
         * Hàm bật/tắt hiển thị cửa sổ robot chat AI (SlideUp animation).
         */
        window.chatbotToggle = function () {
            chatWindow.classList.toggle('active');
            if (chatWindow.classList.contains('active')) {
                if (chatInput) chatInput.focus(); // Tự động trỏ con nháy chuột vào ô nhập tin nhắn
                
                // Nếu là lần đầu mở cửa sổ chat trong phiên hiện tại, hiển thị lời chào mặc định
                if (!hasWelcomed && chatMessages) {
                    hasWelcomed = true;
                    let greeting = {!! json_encode(__('ui.chatbot_greeting'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

                    // Nếu khách đang xem chi tiết sản phẩm, đổi lời chào
                    if (typeof window.chatbotProductContext !== 'undefined' && window.chatbotProductContext) {
                        const prodName = window.chatbotProductName || '';
                        greeting = {!! json_encode(__('ui.chatbot_product_greeting'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}.replace(':product', prodName);
                    }
                    appendMsg(greeting, 'ai', true);
                }
            }
        };

        /**
         * Hàm làm trống hộp thoại chat (Xóa tin nhắn hiển thị, xóa lịch sử lưu trữ).
         * Sử dụng SweetAlert2 thay cho confirm() mặc định của trình duyệt để giao diện đẹp hơn.
         * Có fallback về confirm() nếu thư viện SweetAlert2 chưa được tải.
         */
        window.clearChat = function () {
            if (!chatMessages) return;
            // Lấy nội dung câu hỏi xác nhận từ file ngôn ngữ (đa ngôn ngữ Vi/En)
            const confirmMsg = {!! json_encode(__('ui.chatbot_clear_confirm'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!} || 'Bạn có chắc chắn muốn làm trống hộp thoại chat?';
            
            // Ưu tiên dùng SweetAlert2 (đã được nạp sẵn trong layout app.blade.php)
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: confirmMsg,                    // Tiêu đề popup xác nhận
                    icon: 'warning',                      // Biểu tượng cảnh báo tam giác vàng
                    showCancelButton: true,                // Hiển thị nút Hủy bên cạnh nút Đồng ý
                    confirmButtonColor: '#0046ab',         // Màu nút Đồng ý = màu thương hiệu chính
                    cancelButtonColor: '#d70018',          // Màu nút Hủy = màu đỏ nhấn
                    confirmButtonText: 'Đồng ý',           // Nhãn nút xác nhận
                    cancelButtonText: 'Hủy',               // Nhãn nút hủy bỏ
                    background: '#ffffff',                 // Nền popup trắng
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-100',
                        title: 'text-lg font-bold text-slate-800',
                        confirmButton: 'px-4 py-2 text-sm font-semibold rounded-lg',
                        cancelButton: 'px-4 py-2 text-sm font-semibold rounded-lg'
                    }
                }).then((result) => {
                    // Chỉ thực hiện xóa khi người dùng nhấn nút "Đồng ý"
                    if (result.isConfirmed) {
                        performClearChat();
                    }
                });
            } else {
                // Fallback: Sử dụng confirm() gốc của trình duyệt nếu SweetAlert2 chưa tải
                if (confirm(confirmMsg)) {
                    performClearChat();
                }
            }
        };

        /**
         * Thực hiện xóa sạch toàn bộ nội dung hội thoại chatbot.
         * Được tách thành hàm riêng để tái sử dụng cho cả nhánh SweetAlert2 lẫn fallback confirm().
         * Quy trình:
         *   1. Xóa toàn bộ HTML bong bóng chat khỏi DOM.
         *   2. Reset mảng lưu trữ tin nhắn trong bộ nhớ JS.
         *   3. Xóa lịch sử chat khỏi localStorage.
         *   4. Hiển thị lại lời chào mặc định của robot để khung chat không bị trống rỗng.
         */
        function performClearChat() {
            chatMessages.innerHTML = '';             // Xóa toàn bộ DOM bong bóng tin nhắn
            messageList = [];                        // Reset mảng lưu tin nhắn trong bộ nhớ
            localStorage.removeItem(HISTORY_KEY);    // Xóa lịch sử chat khỏi localStorage
            hasWelcomed = false;                     // Reset cờ đã chào để cho phép chào lại

            // Hiển thị lại lời chào mặc định của robot AI
            hasWelcomed = true;
            let greeting = {!! json_encode(__('ui.chatbot_greeting'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

            // Nếu khách đang xem trang chi tiết sản phẩm, dùng lời chào riêng theo sản phẩm
            if (typeof window.chatbotProductContext !== 'undefined' && window.chatbotProductContext) {
                const prodName = window.chatbotProductName || '';
                greeting = {!! json_encode(__('ui.chatbot_product_greeting'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}.replace(':product', prodName);
            }
            appendMsg(greeting, 'ai', true);         // Chèn lời chào vào khung chat và lưu vào localStorage
        }

        /**
         * Thêm một tin nhắn (Bong bóng chat) vào khung hội thoại.
         * @param {string} text Nội dung tin nhắn (Hỗ trợ HTML an sau)
         * @param {string} role Đối tượng gửi ('user' hoặc 'ai')
         * @param {boolean} save Chỉ thị có lưu tin nhắn này vào LocalStorage để khôi phục phiên hay không
         */
        function appendMsg(text, role, save = true) {
            if (!chatMessages) return;
            const div = document.createElement('div');
            div.className = 'chatbot-msg ' + role;
            // Dọn dẹp mã HTML độc hại bằng DOMPurify để ngăn chặn lỗ hổng DOM-based XSS
            const cleanText = (typeof DOMPurify !== 'undefined') ? DOMPurify.sanitize(text, { ADD_ATTR: ['target'] }) : text;
            div.innerHTML = cleanText;
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight; // Tự động cuộn khung chat xuống đáy tin nhắn mới nhất

            if (save) {
                messageList.push({ text: text, role: role });
                saveHistory(messageList);
            }
        }

        /**
         * Hiển thị biểu tượng 3 dấu chấm nhấp nháy (Loading Dots) báo hiệu AI đang xử lý câu trả lời.
         */
        function showLoading() {
            if (!chatMessages) return;
            const loader = document.createElement('div');
            loader.className = 'chatbot-msg ai';
            loader.id = 'chatbot-loading';
            loader.innerHTML = '<div class="chatbot-loading-dots"><span></span><span></span><span></span></div>';
            chatMessages.appendChild(loader);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        /**
         * Loại bỏ biểu tượng Loading Dots khỏi giao diện chat.
         */
        function removeLoading() {
            const el = document.getElementById('chatbot-loading');
            if (el) el.remove();
        }

        /**
         * Sự kiện khi click chọn một câu hỏi gợi ý nhanh (Quick message).
         * @param {string} text Nội dung câu hỏi thô
         */
        window.chatbotQuickMsg = function (text) {
            if (!chatInput) return;
            if (!chatWindow.classList.contains('active')) chatbotToggle();
            chatInput.value = text;
            chatbotSend();
        };

        /**
         * Hỏi nhanh thông tin/đánh giá về một sản phẩm (gọi từ nút "Hỏi đáp AI" ở trang chi tiết sản phẩm).
         * @param {string} productName Tên sản phẩm
         */
        window.askChatbotAboutProduct = function (productName) {
            if (!chatInput) return;
            if (!chatWindow.classList.contains('active')) chatbotToggle();
            chatInput.value = 'Phân tích ưu nhược điểm của: ' + productName;
            chatbotSend();
        };

        /**
         * Gửi request AJAX POST chứa câu hỏi thô và ngữ cảnh sản phẩm lên Backend RAG Chatbot Controller.
         * @param {string} prompt Câu hỏi của khách hàng
         * @return {Promise<object>} Trả về đối tượng phản hồi đầy đủ từ máy chủ
         */
        async function callBackend(prompt) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const context = (typeof window.chatbotProductContext !== 'undefined') ? window.chatbotProductContext : '';

            const response = await fetch('/chatbot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    prompt: prompt,
                    context: context, // Truyền kèm ngữ cảnh sản phẩm đang xem
                    message_count: messageList.length
                }),
            });

            const data = await response.json();

            if (data.success) {
                return data;
            } else {
                throw new Error(data.message || 'Lỗi phản hồi từ máy chủ');
            }
        }

        /**
         * Chèn Card UI (Form Đặt Lịch Sửa Chữa) trực quan vào khung chat
         */
        function appendRepairCard(defaultData) {
            const cardId = 'repair-card-' + Date.now();
            const div = document.createElement('div');
            div.className = 'chatbot-msg ai chatbot-repair-card-container';
            div.style.alignSelf = 'flex-start';
            div.style.background = '#f0f7ff';
            div.style.color = '#1f2937';
            div.style.borderRadius = '16px 16px 16px 4px';
            div.style.border = '1px solid #dbeafe';
            div.style.padding = '14px';
            div.style.width = '95%';
            div.style.maxWidth = '340px';
            div.style.boxShadow = '0 10px 25px -5px rgba(0, 70, 171, 0.1), 0 8px 10px -6px rgba(0, 70, 171, 0.1)';

            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            tomorrow.setHours(9, 0, 0, 0);
            
            const pad = (num) => String(num).padStart(2, '0');
            const defaultDateStr = tomorrow.getFullYear() + '-' + pad(tomorrow.getMonth() + 1) + '-' + pad(tomorrow.getDate()) + 'T' + pad(tomorrow.getHours()) + ':' + pad(tomorrow.getMinutes());

            const nameVal = defaultData.customer_name || '';
            const phoneVal = defaultData.customer_phone || '';
            const emailVal = defaultData.customer_email || '';
            const issueVal = defaultData.issue_desc || '';
            const imeiVal = defaultData.imei_serial || 'N/A';

            div.innerHTML = `
                <form id="${cardId}" onsubmit="submitRepairCard(event, '${cardId}')" style="display:flex; flex-direction:column; gap:8px;">
                    <div style="font-weight:700; font-size:13.5px; color:#0046ab; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid fa-screwdriver-wrench"></i> Đặt lịch sửa chữa nhanh
                    </div>
                    
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <label style="font-size:10.5px; font-weight:600; color:#475569;">Họ và tên <span style="color:#ef4444">*</span></label>
                        <input type="text" name="customer_name" required value="${nameVal}" placeholder="Họ và tên khách hàng" 
                            style="padding:6px 10px; font-size:12px; border:1px solid #cbd5e1; border-radius:6px; outline:none; transition: border-color 0.2s;" 
                            onfocus="this.style.borderColor='#0046ab'" onblur="this.style.borderColor='#cbd5e1'" />
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:6px;">
                        <div style="display:flex; flex-direction:column; gap:2px;">
                            <label style="font-size:10.5px; font-weight:600; color:#475569;">Số điện thoại <span style="color:#ef4444">*</span></label>
                            <input type="text" name="customer_phone" required value="${phoneVal}" placeholder="SĐT liên hệ" 
                                style="padding:6px 10px; font-size:12px; border:1px solid #cbd5e1; border-radius:6px; outline:none;" 
                                onfocus="this.style.borderColor='#0046ab'" onblur="this.style.borderColor='#cbd5e1'" />
                        </div>
                        <div style="display:flex; flex-direction:column; gap:2px;">
                            <label style="font-size:10.5px; font-weight:600; color:#475569;">Email (nếu có)</label>
                            <input type="email" name="customer_email" value="${emailVal}" placeholder="Địa chỉ email" 
                                style="padding:6px 10px; font-size:12px; border:1px solid #cbd5e1; border-radius:6px; outline:none;" 
                                onfocus="this.style.borderColor='#0046ab'" onblur="this.style.borderColor='#cbd5e1'" />
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap:6px;">
                        <div style="display:flex; flex-direction:column; gap:2px;">
                            <label style="font-size:10.5px; font-weight:600; color:#475569;">Ngày giờ hẹn <span style="color:#ef4444">*</span></label>
                            <input type="datetime-local" name="schedule_date" required value="${defaultDateStr}" 
                                style="padding:5px 8px; font-size:12px; border:1px solid #cbd5e1; border-radius:6px; outline:none;" 
                                onfocus="this.style.borderColor='#0046ab'" onblur="this.style.borderColor='#cbd5e1'" />
                        </div>
                        <div style="display:flex; flex-direction:column; gap:2px;">
                            <label style="font-size:10.5px; font-weight:600; color:#475569;">IMEI / Serial</label>
                            <input type="text" name="imei_serial" value="${imeiVal}" placeholder="IMEI (nếu có)" 
                                style="padding:6px 10px; font-size:12px; border:1px solid #cbd5e1; border-radius:6px; outline:none;" 
                                onfocus="this.style.borderColor='#0046ab'" onblur="this.style.borderColor='#cbd5e1'" />
                        </div>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <label style="font-size:10.5px; font-weight:600; color:#475569;">Mô tả tình trạng lỗi <span style="color:#ef4444">*</span></label>
                        <input type="text" name="issue_desc" required value="${issueVal}" placeholder="Tình trạng hư hỏng thiết bị" 
                            style="padding:6px 10px; font-size:12px; border:1px solid #cbd5e1; border-radius:6px; outline:none;" 
                            onfocus="this.style.borderColor='#0046ab'" onblur="this.style.borderColor='#cbd5e1'" />
                    </div>

                    <button type="submit" class="chatbot-submit-card-btn" 
                        style="margin-top:6px; padding:8px; font-size:12px; font-weight:700; color:white; background:#0046ab; border:none; border-radius:6px; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center; gap:6px;"
                        onmouseover="this.style.background='#003580'" onmouseout="this.style.background='#0046ab'">
                        <i class="fa-solid fa-calendar-check"></i> Xác nhận đặt lịch
                    </button>
                </form>
            `;
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        /**
         * Xử lý gửi AJAX tạo phiếu khi nhấn Xác nhận đặt lịch trên Card UI
         */
        window.submitRepairCard = async function(event, cardId) {
            event.preventDefault();
            const form = document.getElementById(cardId);
            if (!form) return;

            const submitBtn = form.querySelector('.chatbot-submit-card-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.background = '#64748b';
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
            }

            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            try {
                const response = await fetch('/chatbot/create-ticket', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const res = await response.json();
                
                const container = form.closest('.chatbot-repair-card-container');
                if (container) {
                    if (res.success) {
                        container.style.background = '#f0fdf4';
                        container.style.borderColor = '#bbf7d0';
                        container.innerHTML = `
                            <div style="color:#15803d; font-size:12.5px; line-height:1.5; font-weight:600; display:flex; flex-direction:column; gap:4px;">
                                <div>${res.message}</div>
                            </div>
                        `;
                        // Lưu thông báo thành công vào lịch sử hội thoại chat
                        messageList.push({ text: res.message, role: 'ai' });
                        saveHistory(messageList);
                    } else {
                        // Đặt lịch thất bại (server trả về success=false): Khôi phục nút submit về trạng thái ban đầu
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.style.background = '#0046ab';
                            submitBtn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> Xác nhận đặt lịch';
                        }
                        // Hiển thị popup thông báo lỗi bằng SweetAlert2 thay vì alert() thô sơ
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Thông báo',                           // Tiêu đề popup
                                text: res.message || 'Lỗi đặt lịch sửa chữa.', // Nội dung lỗi từ server
                                icon: 'error',                                 // Biểu tượng lỗi tròn đỏ
                                confirmButtonColor: '#0046ab'                  // Màu nút OK = màu thương hiệu
                            });
                        } else {
                            // Fallback: Dùng alert() gốc nếu SweetAlert2 chưa tải
                            alert(res.message || 'Lỗi đặt lịch sửa chữa.');
                        }
                    }
                }
            } catch (error) {
                // Lỗi mạng hoặc server không phản hồi (timeout, 500,...): Khôi phục nút submit
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.style.background = '#0046ab';
                    submitBtn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> Xác nhận đặt lịch';
                }
                console.error('Lỗi khi gửi form sửa chữa:', error);
                // Hiển thị popup thông báo lỗi kết nối bằng SweetAlert2
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Lỗi kết nối',                                     // Tiêu đề popup
                        text: 'Có lỗi xảy ra khi kết nối máy chủ. Vui lòng thử lại.', // Mô tả lỗi
                        icon: 'error',                                             // Biểu tượng lỗi
                        confirmButtonColor: '#0046ab'                              // Màu nút OK = thương hiệu
                    });
                } else {
                    // Fallback: Dùng alert() gốc nếu SweetAlert2 chưa tải
                    alert('Có lỗi xảy ra khi kết nối máy chủ. Vui lòng thử lại.');
                }
            }
        };

        /**
         * Xử lý gửi tin nhắn của người dùng:
         * 1. Đọc nội dung ở ô input, kiểm tra rỗng.
         * 2. Append tin nhắn người dùng lên khung chat.
         * 3. Hiển thị Loading animation.
         * 4. Gọi API Backend, nhận phản hồi từ Gemini AI.
         * 5. Làm sạch mã HTML & Loại bỏ các cú pháp Markdown thừa nếu AI vô tình trả về.
         * 6. Render tin nhắn trả lời lên khung chat.
         */
        window.chatbotSend = async function () {
            if (!chatInput) return;
            const text = chatInput.value.trim();
            if (!text) return;
 
            // Vô hiệu hóa input và nút gửi để tránh spam request liên tục
            chatInput.disabled = true;
            if (chatSendBtn) chatSendBtn.disabled = true;
 
            appendMsg(text, 'user');
            chatInput.value = ''; // Làm sạch ô nhập liệu
            showLoading();
 
            try {
                const resultData = await callBackend(text);
                removeLoading();
 
                // Lấy phản hồi AI, nếu lỗi dùng câu dịch thông báo lỗi mặc định từ hệ thống
                let cleanResponse = (resultData.response || {!! json_encode(__('ui.chatbot_error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}).trim();
 
                // Dọn dẹp và chuẩn hóa định dạng văn bản (Chuyển Markdown sang HTML an toàn)
                cleanResponse = cleanResponse
                    .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')    // Chuyển in đậm **text** -> <b>text</b>
                    .replace(/^[\s]*[-•*]\s/gm, '👉 ')           // Chuyển dấu gạch đầu dòng Markdown -> 👉
                    .replace(/^[\s]*#{1,4}\s*(.*)/gm, '<b>$1</b>') // Chuyển định dạng header Markdown -> <b>text</b>
                    .replace(/\r\n/g, '\n')
                    .replace(/\n{2,}/g, '\n\n')                 // Gom nhóm các dòng trống liên tiếp
                    .replace(/\n/g, '<br>')
                    .replace(/(<br>\s*){3,}/gi, '<br><br>')     // Giới hạn tối đa khoảng trống cách dòng là 1 dòng trống
                    .replace(/^(<br>\s*)+/i, '')                // Xóa thẻ xuống dòng thừa ở đầu
                    .replace(/(<br>\s*)+$/i, '');               // Xóa thẻ xuống dòng thừa ở cuối
 
                appendMsg(cleanResponse, 'ai');
 
                // Nếu có cờ form sửa chữa, render Card UI
                if (resultData.is_repair_form) {
                    appendRepairCard(resultData.default_data);
                }
            } catch (error) {
                removeLoading();
                console.error('Chatbot error:', error);
                
                // Nếu bị ban hoặc cần đăng nhập giữa chừng, tự động reload trang sau 2 giây để cập nhật giao diện
                const errLower = error.message.toLowerCase();
                if (errLower.includes('cấm') || errLower.includes('khóa') || errLower.includes('banned') || errLower.includes('đăng nhập') || errLower.includes('login')) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }

                // Hiển thị thông báo lỗi màu đỏ nổi bật để người dùng nhận biết
                appendMsg('<b style="color:#ef4444">' + {!! json_encode(__('ui.chatbot_error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!} + '</b><br><span style="font-size:12px;color:#6b7280">' + error.message + '</span>', 'ai', false);
            } finally {
                // Kích hoạt lại input và nút gửi sau khi hoàn thành phản hồi hoặc gặp lỗi
                if (chatInput) {
                    chatInput.disabled = false;
                    chatInput.focus();
                }
                if (chatSendBtn) chatSendBtn.disabled = false;
            }
        };
    })();
</script>