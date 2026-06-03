@extends('layouts.app')

@section('title', 'Đăng ký trả góp')

@section('content')
<div class="bg-gray-100 min-h-screen py-8 font-sans">
    
    <div class="max-w-4xl mx-auto mb-4 p-4 text-sm text-gray-500">
        <a href="{{ url('/') }}" class="hover:text-blue-600">Trang chủ</a> > 
        <a href="#" class="hover:text-blue-600">Sản phẩm</a> > Đăng ký trả góp
    </div>

    <!-- Modal Container -->
    <form id="installmentForm" action="{{ url('admin/installments') }}" method="GET" class="max-w-4xl mx-auto bg-white rounded-xl shadow-2xl overflow-hidden relative" x-data="installmentTabs()">
        
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <div class="flex items-center gap-2 text-blue-600 text-lg font-bold">
                <i class="fa-solid fa-credit-card"></i>
                <span class="text-gray-800">Thông tin các gói trả góp</span>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold px-2">&times;</button>
        </div>

        <!-- Product Summary -->
        <div class="p-5 flex items-start gap-4">
            <div class="w-16 h-16 rounded-full border border-gray-200 p-2 flex items-center justify-center bg-white shrink-0">
                <img src="https://cdn.tgdd.vn/Products/Images/54/278546/tai-nghe-bluetooth-chup-tai-sony-wh-1000xm5-270522-113543-600x600.jpg" alt="Product" class="w-12 h-12 object-contain">
            </div>
            <div>
                <h3 class="font-bold text-gray-800 text-[15px] leading-tight">Tai nghe không dây Sony WH-1000XM5 Black</h3>
                <p class="text-red-600 font-bold text-[17px] mt-1">7,990,000đ</p>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="flex text-center border-t border-b border-gray-100 bg-gray-50/50">
            <button type="button" @click="activeTab = 1" :class="activeTab === 1 ? 'bg-white border-blue-600 text-blue-600' : 'text-gray-600 border-transparent hover:bg-gray-50'" class="flex-1 py-3 border-b-2 transition">
                <div class="font-bold text-sm">Trả góp qua công ty tài chính</div>
                <div class="text-[11px] font-normal text-gray-500 mt-0.5">(Trả trước từ 30%)</div>
            </button>
            <button type="button" @click="activeTab = 2" :class="activeTab === 2 ? 'bg-white border-blue-600 text-blue-600' : 'text-gray-600 border-transparent hover:bg-gray-50'" class="flex-1 py-3 border-b-2 transition">
                <div class="font-semibold text-sm">Trả góp qua thẻ tín dụng</div>
                <div class="text-[11px] font-normal text-gray-500 mt-0.5">(Không phí chuyển đổi)</div>
            </button>
            <button type="button" @click="activeTab = 3" :class="activeTab === 3 ? 'bg-white border-blue-600 text-blue-600' : 'text-gray-600 border-transparent hover:bg-gray-50'" class="flex-1 py-3 border-b-2 transition">
                <div class="font-semibold text-sm">Mua trước trả sau</div>
                <div class="text-[11px] font-normal text-gray-500 mt-0.5">(Hạn mức đến 50 triệu)</div>
            </button>
        </div>

        <!-- TAB 1: CÔNG TY TÀI CHÍNH -->
        <div x-show="activeTab === 1" class="p-6">
            <div class="space-y-6 mb-6">
                <!-- Select Company -->
                <div>
                    <p class="text-sm text-gray-600 mb-3">Chọn công ty tài chính</p>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="company in ['Shinhan Finance', 'Home Credit', 'HD Saison', 'Mirae Asset']">
                            <button type="button" @click="selectedCompany = company; calculate()" 
                                    :class="selectedCompany === company ? 'border-blue-600 bg-blue-50/50 text-blue-700 font-bold border-2' : 'border-gray-300 border text-gray-700 hover:border-blue-400'" 
                                    class="rounded-lg px-4 py-2.5 text-sm transition" x-text="company"></button>
                        </template>
                    </div>
                </div>

                <!-- Select Prepay -->
                <div>
                    <p class="text-sm text-gray-600 mb-3">Chọn mức trả trước</p>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="percent in [10, 20, 30, 40, 50, 60, 70]">
                            <button type="button" @click="selectedPrepay = percent; calculate()" 
                                    :class="selectedPrepay === percent ? 'border-blue-600 bg-blue-50/50 text-blue-700 font-bold border-2' : 'border-gray-300 border text-gray-700 hover:border-blue-400'" 
                                    class="rounded-lg px-4 py-2.5 text-sm transition" x-text="percent + '%'"></button>
                        </template>
                    </div>
                </div>

                <!-- Select Months -->
                <div>
                    <p class="text-sm text-gray-600 mb-3">Chọn số tháng trả góp</p>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="month in [3, 4, 6, 9, 12]">
                            <button type="button" @click="selectedMonths = month; calculate()" 
                                    :class="selectedMonths === month ? 'border-blue-600 bg-blue-50/50 text-blue-700 font-bold border-2' : 'border-gray-300 border text-gray-700 hover:border-blue-400'" 
                                    class="rounded-lg px-4 py-2.5 text-sm transition" x-text="month + ' tháng'"></button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Calculation Table -->
            <div class="border border-gray-100 rounded-xl bg-white shadow-sm mb-6">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-5 text-gray-600">Công ty</td>
                            <td class="py-3 px-5 font-bold text-right text-gray-900" x-text="selectedCompany"></td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-5 text-gray-600">Giá mua trả góp</td>
                            <td class="py-3 px-5 font-bold text-right text-gray-900" x-text="fmt(currentPrice)"></td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-5 text-gray-600" x-text="`Trả trước (${selectedPrepay}%)`"></td>
                            <td class="py-3 px-5 font-bold text-right text-gray-900" x-text="fmt(prepayAmount)"></td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-5 text-gray-600">Lãi suất</td>
                            <td class="py-3 px-5 font-bold text-right text-green-600">0% / tháng</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-5 text-gray-600">Giấy tờ cần có</td>
                            <td class="py-3 px-5 font-bold text-right text-gray-900 text-xs">CMND/CCCD + Bằng lái xe/Hộ khẩu</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-5 text-gray-600">Tiền trả góp hàng tháng (Gốc)</td>
                            <td class="py-3 px-5 font-bold text-right text-red-600" x-text="fmt(monthlyTotal)"></td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-5 text-gray-600">Gốc + Lãi</td>
                            <td class="py-3 px-5 font-bold text-right text-gray-900" x-text="fmt(monthlyTotal)"></td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-5 text-gray-600">Phí thu hộ/Bảo hiểm</td>
                            <td class="py-3 px-5 font-bold text-right text-gray-900">0đ</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition bg-gray-50/30">
                            <td class="py-3 px-5 font-bold text-gray-800">Tổng tiền phải trả</td>
                            <td class="py-3 px-5 font-bold text-right text-gray-900" x-text="fmt(totalPay)"></td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-5 font-bold text-gray-600">Chênh lệch</td>
                            <td class="py-3 px-5 font-bold text-right text-red-600" x-text="(diff > 0 ? '+' : '') + fmt(diff)"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="py-3 px-4 text-center text-[11px] text-gray-400 font-medium italic bg-gray-50/50">
                    (Bảng tính tham khảo, số tiền trả trước và hạn mức tùy thuộc vào hồ sơ được duyệt.)
                </div>
            </div>
        </div>

        <!-- TAB 2: THẺ TÍN DỤNG -->
        <div x-show="activeTab === 2" class="p-6" style="display: none;">
            <!-- Select Method -->
            <p class="text-[11px] font-bold text-gray-400 mb-3 uppercase tracking-wider">CHỌN PHƯƠNG THỨC TRẢ GÓP</p>
            <div class="border-2 border-blue-600 rounded-lg p-3 flex justify-between items-center mb-6 bg-blue-50/30">
                <span class="font-bold text-gray-800 text-sm">Trả góp qua Onepay <span class="font-normal">(thẻ Visa/MasterCard/JCB/Napas)</span></span>
                <div class="flex gap-1 text-[10px] text-gray-500 font-bold uppercase">
                    <span class="border rounded px-1 py-0.5 bg-white">Visa</span>
                    <span class="border rounded px-1 py-0.5 bg-white">Master</span>
                    <span class="border rounded px-1 py-0.5 bg-white">JCB</span>
                </div>
            </div>

            <!-- Select Bank -->
            <p class="text-[11px] font-bold text-gray-400 mb-3 uppercase tracking-wider">1. CHỌN NGÂN HÀNG TRẢ GÓP</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <template x-for="bank in ['Vietcombank', 'Techcombank', 'Sacombank', 'ACB', 'MBBank', 'VPBank', 'VIB', 'BIDV', 'VietinBank', 'TPBank', 'HSBC', 'Shinhan Bank']">
                    <button type="button" @click="selectedBank = bank" :class="selectedBank === bank ? 'border-blue-600 border-2' : 'border-gray-200 border hover:border-blue-400'" class="rounded-lg py-3 text-[13px] font-bold text-gray-800 transition text-center shadow-sm bg-white" x-text="bank"></button>
                </template>
            </div>

            <!-- Select Card Type -->
            <p class="text-[11px] font-bold text-gray-400 mb-3 uppercase tracking-wider">2. CHỌN LOẠI THẺ</p>
            <div class="grid grid-cols-3 gap-3 mb-6">
                <template x-for="card in ['Visa', 'MasterCard', 'JCB']">
                    <button type="button" @click="selectedCard = card" :class="selectedCard === card ? 'border-blue-600 border-2' : 'border-gray-200 border hover:border-blue-400'" class="rounded-lg py-3 text-[13px] font-bold text-gray-800 transition text-center shadow-sm bg-white" x-text="card"></button>
                </template>
            </div>

            <!-- Select Months for Credit Card -->
            <p class="text-[11px] font-bold text-gray-400 mb-3 uppercase tracking-wider">3. CHỌN SỐ TIỀN VÀ KỲ HẠN TRẢ GÓP</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <button type="button" @click="selectedMonths = 3; calculate()" :class="selectedMonths === 3 ? 'border-blue-600 border-2' : 'border-gray-200 border hover:border-blue-400'" class="rounded-lg py-2 text-center transition shadow-sm bg-white">
                    <div class="font-bold text-[14px] text-gray-800">3 tháng</div>
                    <div class="text-[11px] text-green-600 font-bold">0% Lãi suất</div>
                </button>
                <button type="button" @click="selectedMonths = 6; calculate()" :class="selectedMonths === 6 ? 'border-blue-600 border-2' : 'border-gray-200 border hover:border-blue-400'" class="rounded-lg py-2 text-center transition shadow-sm bg-white">
                    <div class="font-bold text-[14px] text-gray-800">6 tháng</div>
                    <div class="text-[11px] text-red-500 font-bold">0.58% / tháng</div>
                </button>
                <button type="button" @click="selectedMonths = 9; calculate()" :class="selectedMonths === 9 ? 'border-blue-600 border-2' : 'border-gray-200 border hover:border-blue-400'" class="rounded-lg py-2 text-center transition shadow-sm bg-white">
                    <div class="font-bold text-[14px] text-gray-800">9 tháng</div>
                    <div class="text-[11px] text-red-500 font-bold">0.5% / tháng</div>
                </button>
                <button type="button" @click="selectedMonths = 12; calculate()" :class="selectedMonths === 12 ? 'border-blue-600 border-2' : 'border-gray-200 border hover:border-blue-400'" class="rounded-lg py-2 text-center transition shadow-sm bg-white">
                    <div class="font-bold text-[14px] text-gray-800">12 tháng</div>
                    <div class="text-[11px] text-red-500 font-bold">0.46% / tháng</div>
                </button>
            </div>

            <div class="border border-gray-100 rounded-xl bg-white shadow-sm mb-6 p-2">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-3 px-3 text-gray-600">Ngân hàng</td>
                            <td class="py-3 px-3 font-bold text-right text-gray-900" x-text="selectedBank"></td>
                        </tr>
                        <tr>
                            <td class="py-3 px-3 text-gray-600">Loại thẻ</td>
                            <td class="py-3 px-3 font-bold text-right text-gray-900" x-text="selectedCard"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: MUA TRƯỚC TRẢ SAU -->
        <div x-show="activeTab === 3" class="p-6" style="display: none;">
            <p class="text-[11px] font-bold text-gray-400 mb-3 uppercase tracking-wider">CHỌN NHÀ CUNG CẤP MUA TRƯỚC TRẢ SAU</p>
            <div class="grid grid-cols-3 gap-3 mb-6">
                <button type="button" @click="selectedProvider = 'Home PayLater'" :class="selectedProvider === 'Home PayLater' ? 'border-blue-600 border-2' : 'border-gray-200 border hover:border-blue-400'" class="rounded-lg py-3 text-center transition shadow-sm bg-white">
                    <div class="font-bold text-[14px] text-gray-800">Home PayLater</div>
                    <div class="text-[11px] text-red-600 font-bold mt-1"><i class="fa-solid fa-fire mr-1"></i> HOT NHẤT</div>
                </button>
                <button type="button" @click="selectedProvider = 'Fundiin'" :class="selectedProvider === 'Fundiin' ? 'border-blue-600 border-2' : 'border-gray-200 border hover:border-blue-400'" class="rounded-lg py-3 text-center transition shadow-sm bg-white">
                    <div class="font-bold text-[14px] text-gray-800">Fundiin</div>
                    <div class="text-[11px] text-yellow-500 font-bold mt-1"><i class="fa-solid fa-thumbs-up mr-1"></i> PHỔ BIẾN</div>
                </button>
                <button type="button" @click="selectedProvider = 'Kredivo'" :class="selectedProvider === 'Kredivo' ? 'border-blue-600 border-2' : 'border-gray-200 border hover:border-blue-400'" class="rounded-lg py-3 text-center transition shadow-sm bg-white">
                    <div class="font-bold text-[14px] text-gray-800">Kredivo</div>
                    <div class="text-[11px] text-gray-500 font-bold mt-1"><i class="fa-solid fa-globe mr-1"></i> TOÀN CẦU</div>
                </button>
            </div>

            <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-5 mb-6 text-sm text-gray-600 leading-relaxed shadow-sm">
                <h4 class="font-bold text-gray-900 text-base mb-2" x-text="selectedProvider"></h4>
                <p x-show="selectedProvider === 'Home PayLater'">Home PayLater là dịch vụ mua trước trả sau cực HOT của Home Credit. Hạn mức lên đến 25 triệu, không cần chứng minh thu nhập, lãi suất 0% cho kỳ hạn ngắn, xét duyệt siêu tốc chỉ trong 60 giây.</p>
                <p x-show="selectedProvider === 'Fundiin'" style="display: none;">Fundiin giúp bạn thanh toán trước, trả sau 3 kỳ miễn lãi suất, không phí ẩn. Phê duyệt tự động nhanh chóng thông qua ứng dụng Fundiin với 1 ảnh CMND/CCCD.</p>
                <p x-show="selectedProvider === 'Kredivo'" style="display: none;">Kredivo là giải pháp mua trước trả sau hàng đầu Đông Nam Á. Linh hoạt thanh toán trong 30 ngày hoặc chia nhỏ lên đến 12 tháng với lãi suất cạnh tranh.</p>
            </div>
        </div>

        <!-- COMMON FOOTER FORM -->
        <div class="px-6 pb-6 pt-0 border-t border-gray-100 mt-2">
            <div class="pt-6">
                <p class="text-xs font-bold text-gray-500 mb-4 uppercase tracking-wider">THÔNG TIN NGƯỜI ĐĂNG KÝ</p>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Họ và tên <span class="text-red-500">*</span></label>
                        <input type="text" placeholder="Nguyễn Văn A" value="{{ Auth::check() ? Auth::user()->full_name : '' }}" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm transition bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Số điện thoại <span class="text-red-500">*</span></label>
                        <input type="text" placeholder="VD: 0987654321" value="{{ Auth::check() ? Auth::user()->phone_number : '' }}" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm transition bg-white">
                    </div>
                </div>
                <label class="flex items-center gap-2.5 cursor-pointer group mb-6">
                    <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                    <span class="text-[13px] text-gray-700 font-semibold group-hover:text-blue-600 transition">Bạn có muốn đăng ký thu cũ lên đời? (Trợ giá lên đến 2 triệu)</span>
                </label>
            </div>

            <!-- Footer Buttons -->
            <div class="flex gap-3">
                <button type="button" class="px-8 py-3.5 border border-gray-200 rounded-lg text-sm font-bold text-gray-600 hover:bg-gray-50 transition">Đóng</button>
                <button type="submit" class="flex-1 bg-[#de2000] hover:bg-red-700 text-white rounded-lg text-sm font-bold uppercase transition shadow-md tracking-wide">
                    XÁC NHẬN TRẢ GÓP
                </button>
            </div>
        </div>
    </form>
</div>

<!-- AlpineJS cho Tab & Tính toán (Nếu chưa có trong project thì có thể load qua CDN) -->
@if(!Str::contains(file_get_contents(public_path('index.php') ?? ''), 'alpinejs'))
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endif

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('installmentTabs', () => ({
            activeTab: 1,
            
            // Tab 1 Data
            currentPrice: 7990000,
            selectedCompany: 'Shinhan Finance',
            selectedPrepay: 30, // %
            selectedMonths: 6,
            
            // Tab 2 Data
            selectedBank: 'Vietcombank',
            selectedCard: 'Visa',

            // Tab 3 Data
            selectedProvider: 'Home PayLater',

            // Calc properties
            prepayAmount: 0,
            loanAmount: 0,
            monthlyTotal: 0,
            totalPay: 0,
            diff: 0,

            init() {
                this.calculate();
            },

            fmt(n) {
                let str = Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                return str + 'đ';
            },

            calculate() {
                const interestRate = 0; // Tùy chỉnh sau
                this.prepayAmount = this.currentPrice * (this.selectedPrepay / 100);
                this.loanAmount = this.currentPrice - this.prepayAmount;
                
                let monthlyBase = this.loanAmount / this.selectedMonths;
                this.monthlyTotal = Math.floor(monthlyBase + (this.loanAmount * interestRate));
                
                this.totalPay = this.prepayAmount + (this.monthlyTotal * this.selectedMonths);
                this.diff = this.totalPay - this.currentPrice;
            }
        }));
    });
</script>
@endsection
