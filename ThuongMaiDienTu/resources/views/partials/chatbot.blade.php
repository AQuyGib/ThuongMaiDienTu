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
        <button onclick="chatbotToggle()"
            style="background: rgba(255,255,255,0.15); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;">
            <i class="fa-solid fa-xmark" style="font-size: 16px;"></i>
        </button>
    </div>

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
            onkeypress="if(event.key==='Enter') chatbotSend()">
        <button class="chatbot-send-btn" onclick="chatbotSend()">
            <i class="fa-solid fa-paper-plane" style="font-size: 14px;"></i>
        </button>
    </div>
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

{{-- JavaScript Chatbot --}}
<script>
    (function () {
        'use strict';

        const chatWindow = document.getElementById('ai-chat-window');
        const chatMessages = document.getElementById('chatbot-messages');
        const chatInput = document.getElementById('chatbot-input');
        const chatFab = document.getElementById('chatbot-fab');

        let hasWelcomed = false;
        let messageList = []; // Mảng lưu các tin nhắn hợp lệ để phục vụ khôi phục lịch sử

        const HISTORY_KEY = 'chatbot_history';
        const SESSION_TTL = 60 * 60 * 1000; // 60 phút (mili-giây)

        /**
         * Lưu danh sách tin nhắn và cập nhật thời gian hết hạn
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
         * Tải danh sách tin nhắn nếu phiên chat chưa hết hạn
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

                if (Date.now() > data.expires_at) {
                    // Phiên chat đã hết hạn (quá 60 phút không tương tác)
                    localStorage.removeItem(HISTORY_KEY);
                    return null;
                }

                // Gia hạn phiên chat thêm 60 phút kể từ lần truy cập này
                data.expires_at = Date.now() + SESSION_TTL;
                localStorage.setItem(HISTORY_KEY, JSON.stringify(data));

                return data.messages;
            } catch (e) {
                localStorage.removeItem(HISTORY_KEY);
                return null;
            }
        }

        /**
         * Khôi phục hoặc tạo mới phiên chat khi load trang
         */
        function initChatSession() {
            // Xóa lịch sử chat nếu ngôn ngữ thay đổi (tránh hiển thị tin nhắn cũ sai ngôn ngữ)
            const currentLocale = document.documentElement.lang || 'vi';
            const savedLocale = localStorage.getItem('chatbot_locale');
            if (savedLocale && savedLocale !== currentLocale) {
                localStorage.removeItem(HISTORY_KEY);
            }
            localStorage.setItem('chatbot_locale', currentLocale);

            const cached = loadHistory();
            if (cached && cached.length > 0) {
                messageList = cached;
                hasWelcomed = true;
                // Vẽ lại toàn bộ tin nhắn từ cache (không cần lưu đè lại vào cache)
                cached.forEach(msg => {
                    appendMsg(msg.text, msg.role, false);
                });
            }
        }

        /**
         * Check pending payment order and show alert above chatbot FAB
         */
        function checkPendingPayment() {
            const orderId = localStorage.getItem('pending_payment_order_id');
            const alertEl = document.getElementById('pending-payment-alert');

            // Don't show if we are currently on the maQR page
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

        // Tự động khởi chạy khôi phục lịch sử và kiểm tra thanh toán khi load trang
        document.addEventListener('DOMContentLoaded', () => {
            initChatSession();
            checkPendingPayment();
        });
        /**
         * Mở/đóng cửa sổ chat
         */
        window.chatbotToggle = function () {
            chatWindow.classList.toggle('active');
            if (chatWindow.classList.contains('active')) {
                chatInput.focus();
                if (!hasWelcomed) {
                    hasWelcomed = true;
                    let greeting = {!! json_encode(__('ui.chatbot_greeting'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

                    // Nếu đang xem chi tiết sản phẩm, đổi lời chào
                    if (typeof window.chatbotProductContext !== 'undefined' && window.chatbotProductContext) {
                        const prodName = window.chatbotProductName || '';
                        greeting = {!! json_encode(__('ui.chatbot_product_greeting'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}.replace(':product', prodName);
                    }
                    appendMsg(greeting, 'ai', true);
                }
            }
        };

        /**
         * Thêm tin nhắn vào chat
         * @param {string} text - Nội dung tin nhắn
         * @param {string} role - 'user' hoặc 'ai'
         * @param {boolean} save - Có lưu vào LocalStorage hay không
         */
        function appendMsg(text, role, save = true) {
            const div = document.createElement('div');
            div.className = 'chatbot-msg ' + role;
            div.innerHTML = text;
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            if (save) {
                messageList.push({ text: text, role: role });
                saveHistory(messageList);
            }
        }

        /**
         * Hiện loading animation
         */
        function showLoading() {
            const loader = document.createElement('div');
            loader.className = 'chatbot-msg ai';
            loader.id = 'chatbot-loading';
            loader.innerHTML = '<div class="chatbot-loading-dots"><span></span><span></span><span></span></div>';
            chatMessages.appendChild(loader);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function removeLoading() {
            const el = document.getElementById('chatbot-loading');
            if (el) el.remove();
        }

        /**
         * Gửi tin nhắn nhanh
         */
        window.chatbotQuickMsg = function (text) {
            if (!chatWindow.classList.contains('active')) chatbotToggle();
            chatInput.value = text;
            chatbotSend();
        };

        /**
         * Hỏi AI về sản phẩm cụ thể (gọi từ nút bên ngoài)
         */
        window.askChatbotAboutProduct = function (productName) {
            if (!chatWindow.classList.contains('active')) chatbotToggle();
            chatInput.value = 'Phân tích ưu nhược điểm của: ' + productName;
            chatbotSend();
        };

        /**
         * Gọi Backend RAG Chatbot
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
                    context: context,
                }),
            });

            const data = await response.json();

            if (data.success) {
                return data.response;
            } else {
                throw new Error(data.message || 'Lỗi phản hồi từ máy chủ');
            }
        }

        /**
         * Xử lý gửi tin nhắn
         */
        window.chatbotSend = async function () {
            const text = chatInput.value.trim();
            if (!text) return;

            appendMsg(text, 'user');
            chatInput.value = '';
            showLoading();

            try {
                const aiResponse = await callBackend(text);
                removeLoading();

                let cleanResponse = (aiResponse || {!! json_encode(__('ui.chatbot_error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}).trim();

                // Loại bỏ markdown thừa mà AI có thể vẫn trả về
                cleanResponse = cleanResponse
                    .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')    // **text** → <b>text</b>
                    .replace(/^[\s]*[-•*]\s/gm, '👉 ')           // Dấu gạch đầu dòng Markdown -> 👉
                    .replace(/^[\s]*#{1,4}\s*(.*)/gm, '<b>$1</b>') // Heading Markdown -> <b>text</b>
                    .replace(/\r\n/g, '\n')
                    .replace(/\n{2,}/g, '\n\n')                 // Gom các dòng trắng liên tiếp
                    .replace(/\n/g, '<br>')
                    .replace(/(<br>\s*){3,}/gi, '<br><br>')     // Giới hạn tối đa 2 thẻ <br> liên tiếp (khoảng cách 1 dòng trống)
                    .replace(/^(<br>\s*)+/i, '')                // Xóa các thẻ <br> thừa ở đầu tin nhắn
                    .replace(/(<br>\s*)+$/i, '');               // Xóa các thẻ <br> thừa ở cuối tin nhắn

                appendMsg(cleanResponse, 'ai');
            } catch (error) {
                removeLoading();
                console.error('Chatbot error:', error);
                appendMsg('<b style="color:#ef4444">' + {!! json_encode(__('ui.chatbot_error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!} + '</b><br><span style="font-size:12px;color:#6b7280">' + error.message + '</span>', 'ai', false);
            }
        };
    })();
</script>