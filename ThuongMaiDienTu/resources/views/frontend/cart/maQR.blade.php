<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán QR Thông Minh - DIENMAYPRO AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #020617;
            background-image: 
                radial-gradient(at 0% 0%, rgba(30, 58, 138, 0.3) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(15, 23, 42, 0.3) 0px, transparent 50%);
        }
        .orbitron { font-family: 'Orbitron', sans-serif; }
        
        /* Glassmorphism */
        .glass-panel {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* QR Scanner Effect */
        .qr-wrapper {
            position: relative;
            padding: 12px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
        }
        .qr-scanner-line {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(to bottom, transparent, #3b82f6, transparent);
            box-shadow: 0 0 15px #3b82f6;
            animation: scanLine 3s ease-in-out infinite;
            z-index: 10;
        }
        @keyframes scanLine {
            0%, 100% { top: 0%; opacity: 0; }
            50% { top: 100%; opacity: 1; }
        }

        .glow-blue { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
        .glow-text { text-shadow: 0 0 10px rgba(59, 130, 246, 0.5); }
        
        .payment-method-card.active {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.02); }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(0, 0, 0, 0.2);
        }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
        }
        
        /* Đảm bảo body có thể cuộn trên màn hình nhỏ */
        @media (max-height: 850px) {
            body { overflow-y: auto !important; }
            .flex-col.h-full { height: auto !important; min-height: 100vh; }
            main.flex-1 { height: auto !important; overflow: visible !important; }
        }
    </style>
</head>
<body class="h-full text-slate-200 overflow-hidden">

    <div class="flex flex-col h-full">
        <!-- Header -->
        <header class="h-20 flex items-center justify-between px-8 glass-panel border-b border-white/5 z-20">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-[0_0_20px_rgba(37,99,235,0.4)]">
                    <i class="fa-solid fa-bolt-lightning text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold orbitron tracking-[0.2em] bg-clip-text text-transparent bg-gradient-to-r from-white to-blue-400">DIENMAYPRO <span class="text-blue-500">AI</span></h1>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Terminal Online • Node-01</p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-8">
                <div class="hidden md:block text-right">
                    <p class="text-[10px] text-slate-500 uppercase font-bold">Nhân viên trực</p>
                    <p class="text-sm font-semibold text-slate-200">Quản Trị Viên</p>
                </div>
                <button class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-red-500/20 hover:border-red-500/50 transition-all group">
                    <i class="fa-solid fa-power-off text-slate-400 group-hover:text-red-500"></i>
                </button>
            </div>
        </header>

        <main class="flex-1 flex overflow-hidden p-6 gap-6">
            
            <!-- Cột trái: Giỏ hàng -->
            <div class="flex-1 flex flex-col gap-6">
                <!-- Product List Container -->
                <div class="flex-1 glass-panel rounded-[2rem] flex flex-col overflow-hidden">
                    <div class="p-6 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
                        <h2 class="text-lg font-bold orbitron flex items-center gap-3">
                            <i class="fa-solid fa-cart-shopping text-blue-500"></i>
                            GIỎ HÀNG
                        </h2>
                        <span id="item-count" class="px-4 py-1 bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold rounded-full">0 sản phẩm</span>
                    </div>

                    <div id="cart-items" class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-4">
                        <!-- Danh sách sản phẩm -->
                        <div class="flex flex-col items-center justify-center h-full text-slate-500 opacity-40">
                             <i class="fa-solid fa-box-open text-6xl mb-4"></i>
                             <p class="orbitron text-xs tracking-widest">CHƯA CÓ DỮ LIỆU</p>
                        </div>
                    </div>

                    <!-- Scan Prompt -->
                    <div class="p-6 bg-blue-500/5 border-t border-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-4 text-blue-400">
                            <i class="fa-solid fa-barcode text-2xl animate-pulse"></i>
                            <div>
                                <p class="text-xs font-bold uppercase orbitron text-blue-400">Đang đợi quét mã...</p>
                                <p class="text-[10px] text-slate-500">Sử dụng máy quét để thêm sản phẩm nhanh</p>
                            </div>
                        </div>
                        <button onclick="clearCart()" class="text-xs font-bold text-slate-500 hover:text-red-400 transition-colors uppercase tracking-widest">Hủy giỏ hàng</button>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Thanh toán -->
            <div class="w-[450px] flex flex-col gap-6">
                
                <!-- Payment Panel -->
                <div class="flex-1 glass-panel rounded-[2rem] p-8 flex flex-col relative overflow-y-auto custom-scrollbar max-h-full">
                    <!-- Decor element -->
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-600/10 rounded-full blur-3xl"></div>

                    <h2 class="text-2xl font-bold orbitron mb-8 flex items-center gap-3">
                        <i class="fa-solid fa-receipt text-blue-500"></i>
                        THANH TOÁN
                    </h2>

                    <!-- Payment Methods -->
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <button onclick="setPaymentMethod('cash')" id="method-cash" class="payment-method-card active p-4 rounded-2xl border border-white/5 bg-white/5 transition-all text-center group">
                            <i class="fa-solid fa-money-bill-transfer text-2xl mb-2 text-slate-400 group-hover:text-blue-400 transition-colors"></i>
                            <p class="text-[10px] font-bold orbitron">TIỀN MẶT</p>
                        </button>
                        <button onclick="setPaymentMethod('qr')" id="method-qr" class="payment-method-card p-4 rounded-2xl border border-white/5 bg-white/5 transition-all text-center group">
                            <i class="fa-solid fa-qrcode text-2xl mb-2 text-slate-400 group-hover:text-blue-400 transition-colors"></i>
                            <p class="text-[10px] font-bold orbitron">QUÉT QR</p>
                        </button>
                    </div>

                    <!-- Payment Content Area -->
                    <div class="flex-1 flex flex-col">
                        <!-- QR Display (Hidden by default) -->
                        <div id="qr-display" class="hidden animate-in fade-in zoom-in duration-500 flex-1 flex flex-col items-center justify-center">
                            <div class="qr-wrapper glow-blue mb-6">
                                <div class="qr-scanner-line"></div>
                                <img id="vietqr-img" src="" alt="VietQR" class="w-48 h-48">
                            </div>
                            <p class="text-[10px] text-blue-400 font-bold orbitron tracking-widest mb-1">MÃ THANH TOÁN VIETQR</p>
                            <p class="text-xs text-slate-500 text-center px-8">Quét mã bằng ứng dụng Ngân hàng hoặc Ví điện tử để thanh toán</p>
                        </div>

                        <!-- Summary Display -->
                        <div id="summary-display" class="space-y-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Tạm tính</span>
                                <span id="subtotal" class="font-bold">0đ</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Giảm giá</span>
                                <span class="text-green-400 font-bold">-0đ</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Phí dịch vụ</span>
                                <span class="font-bold">0đ</span>
                            </div>
                        </div>

                        <div class="mt-auto pt-8">
                            <div class="bg-blue-600/10 border border-blue-500/20 rounded-3xl p-6 text-center">
                                <p class="text-[10px] text-blue-400 font-bold orbitron tracking-[0.3em] mb-2 uppercase">Tổng tiền thanh toán</p>
                                <p id="total-price" class="text-4xl font-bold orbitron text-white glow-text">0đ</p>
                            </div>
                            
                            <button onclick="processCheckout()" class="w-full mt-6 bg-blue-600 hover:bg-blue-500 text-white py-5 rounded-2xl font-bold orbitron tracking-widest shadow-[0_10px_20px_rgba(37,99,235,0.3)] transition-all active:scale-95 flex items-center justify-center gap-3">
                                <span id="btn-text">XÁC NHẬN THANH TOÁN</span>
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Transaction -->
                <div class="h-24 glass-panel rounded-[1.5rem] p-5 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center text-green-500">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Giao dịch mới nhất</p>
                        <p id="last-tx" class="text-sm font-bold text-slate-300">Chưa có giao dịch nào</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Hidden Scanner Input -->
    <input type="text" id="scanner-input" class="fixed -top-10 left-0 opacity-0" autofocus>

    <!-- Notification -->
    <div id="notif" class="fixed top-24 left-1/2 -translate-x-1/2 glass-panel px-6 py-3 rounded-full border-blue-500/30 flex items-center gap-3 transition-all duration-500 opacity-0 -translate-y-10 z-50">
        <i class="fa-solid fa-circle-check text-green-500"></i>
        <span id="notif-text" class="text-xs font-bold tracking-wider uppercase">THÀNH CÔNG</span>
    </div>

    <script>
        // --- CẤU HÌNH NGÂN HÀNG ---
        const BANK_CONFIG = {
            ID: 'MB',
            ACCOUNT: '123456789',
            NAME: 'NGUYEN VAN A'
        };

        const PRODUCT_DB = {
            '8934567890123': { name: 'Android Tivi Sony 4K 65 inch', price: 33980000, img: 'https://placehold.co/100x100/1e293b/3b82f6?text=TV' },
            '8934567890124': { name: 'Tủ lạnh Aqua Inverter 189 lít', price: 4990000, img: 'https://placehold.co/100x100/1e293b/3b82f6?text=REF' },
            '8934567890125': { name: 'Máy giặt Samsung AI 9kg', price: 8500000, img: 'https://placehold.co/100x100/1e293b/3b82f6?text=WSH' }
        };

        let cart = [
            { ...PRODUCT_DB['8934567890123'], quantity: 1, code: '8934567890123' }
        ];
        let currentMethod = 'cash';

        // --- SCANNER LOGIC ---
        const scannerInput = document.getElementById('scanner-input');
        document.addEventListener('click', () => scannerInput.focus());
        
        scannerInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const code = scannerInput.value.trim();
                if (code) handleScan(code);
                scannerInput.value = '';
            }
        });

        function handleScan(code) {
            const product = PRODUCT_DB[code];
            if (product) {
                const existing = cart.find(i => i.code === code);
                if (existing) existing.quantity++;
                else cart.push({ ...product, quantity: 1, code });
                
                showNotif(`Đã thêm ${product.name}`);
                renderCart();
                playBeep();
            } else {
                showNotif("Mã sản phẩm không tồn tại", "error");
            }
        }

        // --- CORE LOGIC ---
        function setPaymentMethod(method) {
            currentMethod = method;
            document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('active'));
            document.getElementById(`method-${method}`).classList.add('active');
            
            const qrDisplay = document.getElementById('qr-display');
            const summaryDisplay = document.getElementById('summary-display');
            const btnText = document.getElementById('btn-text');

            if (method === 'qr') {
                qrDisplay.classList.remove('hidden');
                summaryDisplay.classList.add('hidden');
                btnText.innerText = "XÁC NHẬN ĐÃ QUÉT";
                updateQR();
            } else {
                qrDisplay.classList.add('hidden');
                summaryDisplay.classList.remove('hidden');
                btnText.innerText = "XÁC NHẬN THANH TOÁN";
            }
        }

        function updateQR() {
            const total = cart.reduce((s, i) => s + (i.price * i.quantity), 0);
            if (total === 0) return;
            const orderId = "ORD" + Math.random().toString(36).substr(2, 6).toUpperCase();
            const url = `https://img.vietqr.io/image/${BANK_CONFIG.ID}-${BANK_CONFIG.ACCOUNT}-compact.png?amount=${total}&addInfo=${orderId}&accountName=${encodeURIComponent(BANK_CONFIG.NAME)}`;
            document.getElementById('vietqr-img').src = url;
        }

        function formatMoney(n) {
            return new Intl.NumberFormat('vi-VN').format(n) + 'đ';
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-slate-500 opacity-40">
                         <i class="fa-solid fa-box-open text-6xl mb-4"></i>
                         <p class="orbitron text-xs tracking-widest">CHƯA CÓ DỮ LIỆU</p>
                    </div>
                `;
            } else {
                container.innerHTML = cart.map((item, idx) => `
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-white/[0.03] border border-white/5 animate-in slide-in-from-right duration-300">
                        <img src="${item.img}" class="w-14 h-14 rounded-xl object-cover">
                        <div class="flex-1">
                            <p class="font-bold text-sm text-slate-200">${item.name}</p>
                            <p class="text-xs text-blue-400 font-bold">${formatMoney(item.price)}</p>
                        </div>
                        <div class="flex items-center gap-3 bg-black/40 px-3 py-1.5 rounded-xl border border-white/5">
                            <button onclick="updateQty(${idx}, -1)" class="text-slate-400 hover:text-white transition-colors">-</button>
                            <span class="text-xs font-bold w-4 text-center">${item.quantity}</span>
                            <button onclick="updateQty(${idx}, 1)" class="text-slate-400 hover:text-white transition-colors">+</button>
                        </div>
                        <div class="text-right min-w-[100px]">
                            <p class="text-sm font-bold text-white">${formatMoney(item.price * item.quantity)}</p>
                        </div>
                    </div>
                `).join('');
            }

            const total = cart.reduce((s, i) => s + (i.price * i.quantity), 0);
            document.getElementById('subtotal').innerText = formatMoney(total);
            document.getElementById('total-price').innerText = formatMoney(total);
            document.getElementById('item-count').innerText = `${cart.length} sản phẩm`;
            
            if (currentMethod === 'qr') updateQR();
        }

        function updateQty(idx, delta) {
            cart[idx].quantity += delta;
            if (cart[idx].quantity <= 0) cart.splice(idx, 1);
            renderCart();
        }

        function clearCart() {
            if (confirm("Hủy đơn hàng hiện tại?")) {
                cart = [];
                renderCart();
            }
        }

        function processCheckout() {
            if (cart.length === 0) return showNotif("Giỏ hàng trống", "error");
            
            const total = cart.reduce((s, i) => s + (i.price * i.quantity), 0);
            const txId = "ORD-" + Math.random().toString(36).substr(2, 5).toUpperCase();
            
            showNotif(currentMethod === 'qr' ? "Thanh toán thành công!" : "Đã thanh toán tiền mặt!");
            document.getElementById('last-tx').innerText = `${txId} • ${formatMoney(total)}`;
            
            setTimeout(() => {
                cart = [];
                renderCart();
                setPaymentMethod('cash');
                window.print();
            }, 1000);
        }

        function showNotif(txt, type = "success") {
            const el = document.getElementById('notif');
            document.getElementById('notif-text').innerText = txt.toUpperCase();
            el.querySelector('i').className = type === 'success' ? "fa-solid fa-circle-check text-green-500" : "fa-solid fa-circle-xmark text-red-500";
            el.classList.remove('opacity-0', '-translate-y-10');
            setTimeout(() => el.classList.add('opacity-0', '-translate-y-10'), 3000);
        }

        function playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain); gain.connect(ctx.destination);
                osc.frequency.value = 880; gain.gain.value = 0.1;
                osc.start(); osc.stop(ctx.currentTime + 0.1);
            } catch(e) {}
        }

        // Init
        renderCart();
    </script>
</body>
</html>
