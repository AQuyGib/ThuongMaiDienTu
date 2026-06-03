@extends('layouts.app')

@section('title', 'Tra cứu hành trình đơn hàng - DIENMAYPRO')

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
        /* ============================================================
           CSS ĐƯỜNG DẪN TIẾN TRÌNH VÀ ICON TRẠNG THÁI (TIMELINE)
           ============================================================ */

        /* Đường kẻ dọc kết nối giữa các mốc trạng thái vận chuyển */
        .tracking-line {
            position: absolute;
            left: 24px;
            top: 50px;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
            z-index: 0;
        }

        /* Chấm tròn biểu tượng cho mỗi mốc trạng thái */
        .tracking-dot {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            position: relative;
            flex-shrink: 0;
            transition: all 0.4s ease;
        }

        /* Khi mốc trạng thái đã hoàn thành (Xanh lam đậm) */
        .step-completed .tracking-dot {
            background: #0046ab;
            border-color: #0046ab;
            color: #fff;
        }

        /* Khi mốc trạng thái đang diễn ra (Bóng mờ tỏa xung quanh) */
        .step-active .tracking-dot {
            background: #fff;
            border-color: #0046ab;
            color: #0046ab;
            box-shadow: 0 0 0 6px rgba(0, 70, 171, 0.12);
        }

        /* Hiệu ứng trượt lên nhẹ khi hiển thị thông tin tra cứu */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up {
            animation: fadeInUp 0.4s ease-out both;
        }
    </style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4">

        <!-- Khối Tiêu đề chính -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#0046ab] text-white rounded-2xl mb-4 text-2xl shadow-lg shadow-blue-200">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <h1 class="text-3xl font-black text-gray-800 mb-2">Tra cứu hành trình đơn hàng</h1>
            <p class="text-gray-500 max-w-md mx-auto">Nhập mã đơn hàng của bạn để theo dõi trạng thái vận chuyển</p>
        </div>

        <!-- Khung tìm kiếm mã đơn hàng -->
        <div class="bg-white rounded-3xl shadow-xl p-8 mb-8 border border-gray-100">
            <form id="form-code" onsubmit="doSearch(event)">
                <div class="flex flex-col md:flex-row gap-4">
                    <!-- Ô nhập mã đơn hàng (Ví dụ: DMP-123456) -->
                    <div class="flex-1 relative">
                        <i class="fa-solid fa-barcode absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                        <input type="text" id="input-code" placeholder="VD: DMP-481552"
                                class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-transparent rounded-2xl focus:border-[#0046ab] focus:bg-white transition-all outline-none font-bold text-gray-800 text-lg">
                    </div>
                    <button type="submit"
                        class="px-10 py-4 bg-[#0046ab] text-white rounded-2xl font-bold hover:bg-blue-800 transition-all shadow-lg shadow-blue-100 flex items-center justify-center gap-2 whitespace-nowrap">
                        Tra cứu ngay <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
                <div class="flex items-center gap-2 mt-4 text-sm text-gray-500 bg-blue-50/50 p-3 rounded-xl border border-blue-100/50">
                    <i class="fa-solid fa-circle-info text-[#0046ab]"></i>
                    <span>Mã đơn hàng được gửi kèm trong email xác nhận hoặc hóa đơn của bạn.</span>
                </div>
            </form>
        </div>

        <!-- TRẠNG THÁI LOADING: Hiển thị khi đang chờ API tìm kiếm -->
        <div id="loading" class="hidden text-center py-16">
            <i class="fa-solid fa-circle-notch fa-spin text-4xl text-[#0046ab] mb-4 block"></i>
            <p class="text-gray-500 font-medium">Đang kết nối hệ thống vận chuyển...</p>
        </div>

        <!-- TRẠNG THÁI LỖI: Không tìm thấy đơn hàng tương ứng với mã cung cấp -->
        <div id="noResult" class="hidden">
            <div class="bg-white rounded-3xl shadow-xl p-12 border border-gray-100 text-center fade-in-up">
                <div class="w-20 h-20 bg-red-50 text-red-400 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Mã đơn hàng không hợp lệ</h3>
                <p class="text-gray-500 mb-6">Chúng tôi không tìm thấy thông tin đơn hàng này. Vui lòng kiểm tra lại mã DMP-XXXXXX.</p>
                <button onclick="resetSearch()" class="px-8 py-3 border-2 border-[#0046ab] text-[#0046ab] rounded-xl font-bold hover:bg-blue-50 transition-all">
                    Thử lại
                </button>
            </div>
        </div>

        <!-- TRẠNG THÁI HIỂN THỊ KẾT QUẢ TRA CỨU ĐƠN HÀNG (RESULT PANEL) -->
        <div id="trackingResult" class="hidden fade-in-up">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- 1. BẢNG TIẾN TRÌNH HÀNH TRÌNH CHÂN THỰC (TIMELINE BÊN TRÁI) -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                        <div class="flex justify-between items-center mb-8">
                            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                                <i class="fa-solid fa-route text-[#0046ab]"></i> Hành trình đơn hàng
                            </h3>
                            <span id="order-id-badge"
                                class="bg-blue-50 text-[#0046ab] text-sm font-bold px-4 py-2 rounded-full"></span>
                        </div>

                        <!-- Cấu trúc trục dọc chứa 5 mốc cố định:
                             JS sẽ động hóa việc thêm bớt các class (step-completed, step-active) 
                             để mô tả chính xác thực trạng của đơn hàng.
                        -->
                        <div class="relative space-y-10">
                            <div class="tracking-line"></div>

                            <!-- Mốc 1: Đặt hàng thành công -->
                            <div class="flex items-start gap-5 step-completed relative">
                                <div class="tracking-dot"><i class="fa-solid fa-file-invoice"></i></div>
                                <div class="pt-2">
                                    <h4 class="font-bold text-gray-800 text-base">Đã đặt hàng thành công</h4>
                                    <p class="text-gray-500 text-sm">Hệ thống đã ghi nhận đơn hàng của bạn.</p>
                                    <span class="text-xs text-gray-400 mt-1 block"><i class="fa-regular fa-clock mr-1"></i>10:15 – 10/05/2026</span>
                                </div>
                            </div>

                            <!-- Mốc 2: Xác nhận thanh toán -->
                            <div class="flex items-start gap-5 step-completed relative">
                                <div class="tracking-dot"><i class="fa-solid fa-check-double"></i></div>
                                <div class="pt-2">
                                    <h4 class="font-bold text-gray-800 text-base">Đã xác nhận thanh toán</h4>
                                    <p class="text-gray-500 text-sm">Giao dịch đã được xác thực thành công.</p>
                                    <span class="text-xs text-gray-400 mt-1 block"><i class="fa-regular fa-clock mr-1"></i>10:30 – 10/05/2026</span>
                                </div>
                            </div>

                            <!-- Mốc 3: Đóng gói chuẩn bị sản phẩm -->
                            <div class="flex items-start gap-5 step-active relative">
                                <div class="tracking-dot"><i class="fa-solid fa-box-open"></i></div>
                                <div class="pt-2">
                                    <h4 class="font-bold text-[#0046ab] text-base">Đang đóng gói</h4>
                                    <p class="text-gray-500 text-sm">Sản phẩm đang được kiểm tra và đóng gói.</p>
                                    <span class="text-xs text-[#0046ab] font-bold mt-1 block flex items-center gap-1">
                                        <span class="w-2 h-2 bg-[#0046ab] rounded-full inline-block animate-pulse"></span>
                                        Cập nhật: Vừa xong
                                    </span>
                                </div>
                            </div>

                            <!-- Mốc 4: Bàn giao giao hàng vận chuyển -->
                            <div class="flex items-start gap-5 relative">
                                <div class="tracking-dot"><i class="fa-solid fa-truck text-gray-300"></i></div>
                                <div class="pt-2">
                                    <h4 class="font-bold text-gray-300 text-base">Đang vận chuyển</h4>
                                    <p class="text-gray-300 text-sm">Chờ bàn giao cho đối tác vận chuyển.</p>
                                </div>
                            </div>

                            <!-- Mốc 5: Hoàn tất giao hàng -->
                            <div class="flex items-start gap-5 relative">
                                <div class="tracking-dot"><i class="fa-solid fa-house-circle-check text-gray-300"></i></div>
                                <div class="pt-2">
                                    <h4 class="font-bold text-gray-300 text-base">Đã giao hàng</h4>
                                    <p class="text-gray-300 text-sm">Dự kiến giao hàng trong 1–3 ngày tới.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. BẢNG TỔNG HỢP THÔNG TIN KHÁCH HÀNG & NÚT CHI TIẾT (BÊN PHẢI) -->
                <div class="space-y-6">
                    <div class="bg-white rounded-3xl shadow-xl p-6 border border-gray-100">
                        <h3 class="text-base font-bold text-gray-800 mb-5 border-b pb-4 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-[#0046ab]"></i> Thông tin tóm tắt
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Người nhận:</span>
                                <span id="result-name" class="font-bold text-gray-800"></span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Trạng thái:</span>
                                <span id="result-status" class="text-[10px] font-black px-2 py-0.5 rounded"></span>
                            </div>
                            <div class="pt-3 border-t border-gray-50 text-sm">
                                <span class="text-gray-500 block mb-1">Địa chỉ giao hàng:</span>
                                <p id="result-address" class="font-medium text-gray-800 leading-relaxed"></p>
                            </div>
                        </div>
                        <div class="mt-6 pt-5 border-t-2 border-dashed border-gray-100 flex justify-between items-end">
                            <span class="text-sm text-gray-500 font-bold">TỔNG TIỀN:</span>
                            <span id="result-total" class="text-2xl font-black text-red-600"></span>
                        </div>
                    </div>

                    <!-- Nút mở Popup Modal xem chi tiết các mặt hàng đã mua -->
                    <div>
                        <button onclick="openProductsModal()"
                            class="w-full py-3.5 px-6 bg-[#0046ab] hover:bg-blue-800 active:scale-[0.98] text-white rounded-2xl font-bold shadow-md shadow-blue-200 flex items-center justify-center gap-3 transition-all duration-200">
                            <i class="fa-solid fa-box-open text-lg"></i>
                            <span>Xem chi tiết sản phẩm đã đặt</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-white/80 ml-auto"></i>
                        </button>
                    </div>

                    <!-- Nhập mã tra cứu đơn khác -->
                    <button onclick="resetSearch()"
                        class="w-full py-4 bg-gray-100 text-gray-600 rounded-2xl font-bold hover:bg-gray-200 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-rotate-left"></i> Nhập mã khác
                    </button>

                    <a href="{{ url('/') }}"
                        class="w-full py-4 border-2 border-[#0046ab] text-[#0046ab] rounded-2xl font-bold hover:bg-blue-50 transition-all text-center block">
                        Về trang chủ
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============================================================
     POPUP MODAL: LIÊN KẾT XEM CHI TIẾT CÁC SẢN PHẨM ĐÃ ĐẶT
     ============================================================ -->
<div id="products-modal" class="fixed inset-0 z-[9999] flex items-center justify-center hidden" role="dialog" aria-modal="true">
    <!-- Nền đen mờ bao quanh ngoài (Backdrop) -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeProductsModal()"></div>

    <!-- Hộp thoại Modal trượt nhẹ -->
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[85vh] animate-modal-in overflow-hidden">

        <!-- Header Modal -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i class="fa-solid fa-list-check text-[#0046ab] text-lg"></i>
                </div>
                <div>
                    <h2 class="font-bold text-gray-800 text-base leading-tight">Sản phẩm đã đặt</h2>
                    <p id="modal-order-code" class="text-xs text-gray-400 font-medium"></p>
                </div>
            </div>
            <button onclick="closeProductsModal()" class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-50 hover:text-red-500 text-gray-500 transition-all">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Khung chứa danh sách chi tiết các item (tự động có thanh cuộn nếu danh sách dài) -->
        <div id="result-products" class="overflow-y-auto px-6 py-4 space-y-0 flex-1"></div>

        <!-- Footer Modal: Hiển thị tổng tiền -->
        <div class="flex-shrink-0 border-t border-dashed border-gray-200 px-6 py-4 flex justify-between items-center bg-gray-50">
            <span class="text-sm font-bold text-gray-500">TỔNG TIỀN ĐƠN HÀNG:</span>
            <span id="modal-total" class="text-xl font-black text-red-600"></span>
        </div>
    </div>
</div>

<style>
/* CSS Keyframe hoạt ảnh trượt lên khi mở Modal */
@keyframes modalIn {
    from { opacity: 0; transform: translateY(32px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0)   scale(1);    }
}
.animate-modal-in { animation: modalIn 0.28s cubic-bezier(.22,1,.36,1) both; }
</style>

<script>
    // Mở hộp thoại Modal và tạm khóa thanh cuộn toàn trang web
    function openProductsModal() {
        document.getElementById('products-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Đóng hộp thoại Modal và trả lại thanh cuộn bình thường
    function closeProductsModal() {
        document.getElementById('products-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Lắng nghe phím ESC để đóng nhanh Modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeProductsModal();
    });

    /**
     * 1. AJAX: THỰC THI TRA CỨU ĐƠN HÀNG (DO SEARCH REQUEST)
     * Gửi yêu cầu GET lên route `/orders/search?code=...`.
     * Quản lý ẩn/hiện các khung thông báo Loading, No Result và Panel kết quả hợp lý.
     */
    function doSearch(e) {
        e.preventDefault();
        closeProductsModal();
        
        const loading   = document.getElementById('loading');
        const result    = document.getElementById('trackingResult');
        const noResult  = document.getElementById('noResult');
        const codeInput = document.getElementById('input-code');

        // Reset trạng thái hiển thị
        result.classList.add('hidden');
        noResult.classList.add('hidden');
        
        const code = codeInput.value.trim();
        if (!code) return;

        loading.classList.remove('hidden');

        fetch(`/orders/search?code=${encodeURIComponent(code)}`)
            .then(res => {
                if (!res.ok) throw new Error('Không tìm thấy đơn hàng');
                return res.json();
            })
            .then(data => {
                loading.classList.add('hidden');
                if (data.success) {
                    showResult(data);
                } else {
                    noResult.classList.remove('hidden');
                }
            })
            .catch(err => {
                loading.classList.add('hidden');
                noResult.classList.remove('hidden');
            });
    }

    /**
     * 2. HIỂN THỊ KẾT QUẢ ĐƠN HÀNG LÊN GIAO DIỆN
     * Điền đầy đủ họ tên, địa chỉ, tổng tiền, mã đơn hàng.
     * Render danh sách sản phẩm trong đơn hàng kèm hình ảnh (hỗ trợ onerror xử lý ảnh lỗi).
     * Gọi hàm cập nhật Timeline tương ứng.
     */
    function showResult(data) {
        document.getElementById('order-id-badge').textContent = '#' + (data.order_code || data.order_id);
        document.getElementById('result-name').textContent    = data.customer_name;
        
        // Cập nhật nhãn trạng thái và tô màu phù hợp (VD: Chờ duyệt, Thành công)
        const statusEl = document.getElementById('result-status');
        statusEl.textContent = data.status_label;
        statusEl.className = 'text-[10px] font-black px-2 py-0.5 rounded ' + data.status_color;

        // Định dạng tiền tệ VND
        document.getElementById('result-address').textContent = data.shipping_address;
        const formatted = new Intl.NumberFormat('vi-VN').format(data.final_amount) + 'đ';
        document.getElementById('result-total').textContent  = formatted;
        document.getElementById('modal-total').textContent   = formatted;
        
        const orderCode = '#' + (data.order_code || data.order_id);
        document.getElementById('modal-order-code').textContent = 'Mã đơn: ' + orderCode;

        // Dựng HTML danh sách sản phẩm chi tiết
        const productsContainer = document.getElementById('result-products');
        productsContainer.innerHTML = '';
        if (data.items && data.items.length > 0) {
            data.items.forEach(item => {
                const imgUrl = item.image || 'https://via.placeholder.com/56x56?text=SP';
                productsContainer.innerHTML += `
                    <div style="display:flex; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid #f3f4f6;">
                        <img src="${imgUrl}" style="width:56px; height:56px; min-width:56px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb;" onerror="this.src='/images/no-image.png'">
                        <div style="flex:1; min-width:0; word-break:normal; overflow-wrap:anywhere;">
                            <div style="font-size:13px; font-weight:700; color:#1f2937; line-height:1.4;">${item.product_name}</div>
                            <div style="font-size:12px; color:#6b7280; margin-top:4px;">Số lượng: ${item.quantity}</div>
                        </div>
                        <div style="font-size:13px; font-weight:800; color:#1f2937; white-space:nowrap; padding-left:8px;">${new Intl.NumberFormat('vi-VN').format(item.price)}đ</div>
                    </div>
                `;
            });
        }

        // Cập nhật mốc trạng thái timeline hành trình
        updateTimeline(data.status);

        // Hiển thị khung kết quả và tự động cuộn màn hình xuống vùng kết quả
        const result = document.getElementById('trackingResult');
        result.classList.remove('hidden');
        result.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * 3. ĐỒNG BỘ HÓA SƠ ĐỒ HÀNH TRÌNH ĐƠN HÀNG (TIMELINE DYNAMIC LOGIC)
     * Đọc chuỗi `status` trả về từ server để lần lượt gán class active/completed cho các mốc:
     * - 'Pending' hoặc 'BaoCK' (Đã báo chuyển khoản): Mốc 2 hiển thị spin chờ duyệt.
     * - 'Cancelled' (Đơn hàng bị hủy): Mốc 2 hiển thị thông báo đã hủy.
     * - 'Shipping' (Đang giao): Mốc 1, 2, 3 hiển thị tích xanh completed, mốc 4 active.
     * - 'Delivered' hoặc 'Completed' (Giao thành công): Kích hoạt tích xanh cho tất cả 5 mốc.
     */
    function updateTimeline(status) {
        const steps = document.querySelectorAll('.relative.space-y-10 > .flex');
        
        // Reset sạch tất cả mốc về trạng thái xám ban đầu
        steps.forEach(step => {
            step.classList.remove('step-completed', 'step-active');
            step.querySelector('.tracking-dot').className = 'tracking-dot';
            
            const h4 = step.querySelector('h4');
            const p = step.querySelector('p');
            if (h4) h4.className = 'font-bold text-gray-300 text-base';
            if (p) p.className = 'text-gray-300 text-sm';
        });

        // Hàm gán mốc đang diễn ra (Active)
        const setActive = (stepIndex, title, desc, iconClass) => {
            const step = steps[stepIndex];
            if (!step) return;
            step.classList.add('step-active');
            step.querySelector('.tracking-dot').className = 'tracking-dot border-[#0046ab] text-[#0046ab]';
            step.querySelector('.tracking-dot').innerHTML = `<i class="${iconClass}"></i>`;
            const h4 = step.querySelector('h4');
            const p = step.querySelector('p');
            if (h4) {
                h4.textContent = title;
                h4.className = 'font-bold text-[#0046ab] text-base';
            }
            if (p) {
                p.textContent = desc;
                p.className = 'text-gray-500 text-sm';
            }
        };

        // Hàm gán mốc đã hoàn thành xong (Completed)
        const setCompleted = (stepIndex, title, desc, iconClass) => {
            const step = steps[stepIndex];
            if (!step) return;
            step.classList.add('step-completed');
            step.querySelector('.tracking-dot').className = 'tracking-dot bg-[#0046ab] border-[#0046ab] text-white';
            step.querySelector('.tracking-dot').innerHTML = `<i class="${iconClass}"></i>`;
            const h4 = step.querySelector('h4');
            const p = step.querySelector('p');
            if (h4) {
                h4.textContent = title;
                h4.className = 'font-bold text-gray-800 text-base';
            }
            if (p) {
                p.textContent = desc;
                p.className = 'text-gray-500 text-sm';
            }
        };

        // Bước 1 mặc định luôn hoàn thành (Đã đặt hàng thành công)
        setCompleted(0, 'Đã đặt hàng thành công', 'Hệ thống đã ghi nhận đơn hàng của bạn.', 'fa-solid fa-file-invoice');

        if (status === 'Pending' || status === 'BaoCK') {
            setActive(1, 'Chờ duyệt thanh toán', 'Giao dịch đang chờ Admin xác thực.', 'fa-solid fa-circle-notch fa-spin');
        } else if (status === 'Cancelled') {
            setActive(1, 'Đơn hàng đã bị hủy', 'Đơn hàng này không còn hiệu lực.', 'fa-solid fa-circle-xmark text-red-500');
        } else {
            setCompleted(1, 'Đã xác nhận thanh toán', 'Giao dịch đã được xác thực thành công.', 'fa-solid fa-check-double');
            
            if (status === 'Shipping') {
                setCompleted(2, 'Đang đóng gói', 'Sản phẩm đang được đóng gói chuẩn bị giao.', 'fa-solid fa-box-open');
                setActive(3, 'Đang vận chuyển', 'Sản phẩm đang được vận chuyển tới quý khách.', 'fa-solid fa-truck text-blue-500');
            } else if (status === 'Delivered' || status === 'Completed') {
                setCompleted(2, 'Đã đóng gói', 'Sản phẩm đã được kiểm tra và đóng gói.', 'fa-solid fa-box-open');
                setCompleted(3, 'Đang vận chuyển', 'Sản phẩm đã được giao cho đơn vị vận chuyển.', 'fa-solid fa-truck');
                setCompleted(4, 'Đã giao hàng thành công', 'Đơn hàng đã được giao đến tay quý khách.', 'fa-solid fa-house-circle-check');
            } else {
                setActive(2, 'Đang xử lý chuẩn bị hàng', 'Sản phẩm đang được chuẩn bị.', 'fa-solid fa-circle-notch fa-spin');
            }
        }
    }

    // Làm sạch form để tìm kiếm đơn mới
    function resetSearch() {
        document.getElementById('trackingResult').classList.add('hidden');
        document.getElementById('noResult').classList.add('hidden');
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('input-code').value = '';
        document.getElementById('input-code').focus();
    }
</script>
@endsection
