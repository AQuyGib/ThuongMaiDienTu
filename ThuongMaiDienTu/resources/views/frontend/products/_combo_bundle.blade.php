{{-- =====================================================
     COMBO BUNDLE SECTION: "Mua kèm tiết kiệm"
     ===================================================== --}}

@if(isset($comboProducts) && $comboProducts->isNotEmpty())
@php
    $bundleItems = $comboProducts;
    $mainProductPrice = $effectivePrice ?? $product->base_price;
@endphp

@push('styles')
<style>
.combo-section {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    padding: 24px;
    margin-bottom: 24px;
    border: 1px solid #eef2ff;
}

.combo-header {
    margin-bottom: 20px;
}

.combo-title {
    font-size: 18px;
    font-weight: 800;
    color: #1a1a2e;
    display: flex;
    align-items: center;
    gap: 10px;
    text-transform: uppercase;
}

.combo-title i {
    color: #d70018;
}

.combo-wrapper {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.combo-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 120px;
    position: relative;
    text-align: center;
}

.combo-img-wrap {
    width: 100px;
    height: 100px;
    border: 1px solid #eee;
    border-radius: 10px;
    padding: 10px;
    background: #fff;
    margin-bottom: 8px;
    transition: .3s;
}

.combo-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.combo-plus {
    font-size: 20px;
    color: #888;
    font-weight: bold;
}

.combo-checkbox {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #0046ab;
    z-index: 5;
}

.combo-name {
    font-size: 11px;
    font-weight: 600;
    color: #444;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 32px;
}

.combo-price {
    font-size: 13px;
    font-weight: 700;
    color: #d70018;
    margin-top: 4px;
}

.combo-equal {
    font-size: 24px;
    color: #888;
    margin: 0 10px;
}

.combo-summary {
    flex: 1;
    min-width: 250px;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    border: 1px dashed #0046ab;
}

.combo-total-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

.combo-total-price {
    font-size: 24px;
    font-weight: 900;
    color: #d70018;
    margin-bottom: 15px;
}

.btn-combo-buy {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #0046ab, #003380);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-combo-buy:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,70,171,.3);
}

.combo-saving-badge {
    display: inline-block;
    background: #16a34a;
    color: #fff;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 4px;
    margin-top: 5px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .combo-wrapper { justify-content: center; }
    .combo-summary { width: 100%; margin-top: 15px; }
    .combo-equal { transform: rotate(90deg); margin: 10px 0; }
}
</style>
@endpush

<div class="combo-section">
    <div class="combo-header">
        <h2 class="combo-title">
            <i class="fa-solid fa-layer-group"></i>
            Mua kèm Combo tiết kiệm
        </h2>
        <p style="font-size: 13px; color: #666; margin-top: 4px;">Tiết kiệm hơn khi mua trọn bộ phụ kiện chính hãng</p>
    </div>

    <div class="combo-wrapper">
        {{-- Sản phẩm chính --}}
        <div class="combo-item">
            <input type="checkbox" checked disabled class="combo-checkbox">
            <div class="combo-img-wrap">
                <img src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=150' }}" alt="{{ $product->name }}">
            </div>
            <div class="combo-name">Sản phẩm hiện tại</div>
            <div class="combo-price">{{ number_format($mainProductPrice, 0, ',', '.') }}đ</div>
        </div>

        @foreach($bundleItems as $item)
            @php
                $basePrice = $item->flash_sale_price ?? $item->base_price;
                $pivot = $item->pivot;
                $discountedPrice = $basePrice;
                $savedAmount = 0;
                if ($pivot) {
                    if ($pivot->discount_type === 'percentage') {
                        $savedAmount = $basePrice * ($pivot->discount_value / 100);
                        $discountedPrice = $basePrice - $savedAmount;
                    } else {
                        $savedAmount = $pivot->discount_value;
                        $discountedPrice = $basePrice - $savedAmount;
                    }
                    if ($discountedPrice < 0) {
                        $discountedPrice = 0;
                        $savedAmount = $basePrice;
                    }
                }
            @endphp
            <div class="combo-plus"><i class="fa-solid fa-plus"></i></div>

            <div class="combo-item">
                <input type="checkbox" 
                       checked 
                       class="combo-checkbox accessory-check" 
                       data-price="{{ $discountedPrice }}"
                       data-saved="{{ $savedAmount }}"
                       data-id="{{ $item->product_id }}"
                       onchange="updateComboTotal()">
                <div class="combo-img-wrap">
                    <img src="{{ $item->thumbnail ?? 'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=150' }}" alt="{{ $item->name }}">
                </div>
                <div class="combo-name">{{ $item->name }}</div>
                <div class="combo-price">
                    {{ number_format($discountedPrice, 0, ',', '.') }}đ
                    @if($savedAmount > 0)
                        <div style="font-size: 11px; text-decoration: line-through; color: #94a3b8; font-weight: normal; margin-top: 2px;">
                            {{ number_format($basePrice, 0, ',', '.') }}đ
                        </div>
                        <div style="font-size: 10px; color: #16a34a; font-weight: bold; margin-top: 1px;">
                            -{{ $pivot->discount_type === 'percentage' ? (float)$pivot->discount_value . '%' : number_format($pivot->discount_value, 0, ',', '.') . 'đ' }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="combo-equal"><i class="fa-solid fa-equals"></i></div>

        <div class="combo-summary">
            <div class="combo-total-label">Tổng cộng <span id="comboCount">3</span> sản phẩm:</div>
            <div class="combo-total-price" id="comboTotalPrice">0đ</div>
            <div id="comboTotalSavingContainer" style="margin-bottom: 12px; display: none;">
                <span style="font-size: 13px; color: #16a34a; font-weight: 600;">Tiết kiệm thêm: </span>
                <span id="comboTotalSaving" style="font-size: 14px; color: #16a34a; font-weight: 800;">0đ</span>
            </div>
            <button class="btn-combo-buy" onclick="buyCombo()">
                <i class="fa-solid fa-cart-shopping"></i> THÊM COMBO VÀO GIỎ
            </button>
            <div class="combo-saving-badge">Combo ưu đãi chính hãng</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Giá bán thực tế của sản phẩm chính hiện tại (đã bao gồm giá Flash Sale nếu có)
const mainPrice = {{ $mainProductPrice }};

/**
 * Hàm: updateComboTotal
 * Công dụng: Tính toán và hiển thị tổng số tiền và số tiền tiết kiệm được của combo phụ kiện.
 *            - Duyệt qua tất cả các checkbox sản phẩm phụ kiện đang được chọn.
 *            - Cộng dồn giá bán đã giảm (`data-price`) và số tiền tiết kiệm (`data-saved`).
 *            - Cập nhật số lượng sản phẩm, tổng giá tiền và số tiền tiết kiệm lên giao diện theo thời gian thực.
 */
function updateComboTotal() {
    let total = mainPrice;
    let count = 1;
    let totalSaving = 0;
    
    document.querySelectorAll('.accessory-check').forEach(cb => {
        if (cb.checked) {
            total += parseFloat(cb.dataset.price);
            totalSaving += parseFloat(cb.dataset.saved || 0);
            count++;
        }
    });

    document.getElementById('comboTotalPrice').innerText = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
    document.getElementById('comboCount').innerText = count;

    const savingContainer = document.getElementById('comboTotalSavingContainer');
    if (totalSaving > 0) {
        document.getElementById('comboTotalSaving').innerText = new Intl.NumberFormat('vi-VN').format(totalSaving) + 'đ';
        savingContainer.style.display = 'block';
    } else {
        savingContainer.style.display = 'none';
    }
}

// Gọi cập nhật tổng tiền combo ngay khi trang web tải xong
document.addEventListener('DOMContentLoaded', updateComboTotal);

/**
 * Hàm: buyCombo
 * Công dụng: Thêm toàn bộ các sản phẩm trong combo đang được chọn vào giỏ hàng.
 * Hoạt động:
 *   - Tạo danh sách các sản phẩm cần thêm, bao gồm sản phẩm chính và các phụ kiện đi kèm (có kèm theo parent_id).
 *   - Gửi yêu cầu POST liên tiếp (sequential) đến endpoint `/cart/add` để thêm từng sản phẩm.
 *   - Hiển thị thông báo toast thành công và cập nhật lại số lượng sản phẩm trên icon giỏ hàng ở header.
 */
async function buyCombo() {
    const btn = document.querySelector('.btn-combo-buy');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
    btn.disabled = true;

    const itemsToAdd = [
        { product_id: {{ $product->product_id }}, quantity: 1 }
    ];

    document.querySelectorAll('.accessory-check:checked').forEach(cb => {
        itemsToAdd.push({ 
            product_id: cb.dataset.id, 
            quantity: 1,
            parent_id: {{ $product->product_id }}
        });
    });

    try {
        for (const item of itemsToAdd) {
            await fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(item)
            });
        }

        if (typeof showToast === 'function') {
            showToast('Đã thêm trọn bộ combo vào giỏ hàng thành công!');
        }
        
        // Cập nhật lại số lượng hiển thị trên giỏ hàng của Header
        if(typeof updateCartCount === 'function') updateCartCount();

    } catch (error) {
        console.error('Lỗi khi thêm combo:', error);
        alert('Có lỗi xảy ra khi thêm combo. Vui lòng thử lại!');
    } finally {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
}
</script>
@endpush
@endif
