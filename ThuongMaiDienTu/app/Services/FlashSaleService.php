<?php

namespace App\Services;

use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FlashSaleService
{
    /**
     * Lấy chiến dịch Flash Sale đang hoạt động đầu tiên.
     * Trả về model FlashSale hoặc null nếu không có chương trình nào diễn ra tại thời điểm hiện tại.
     */
    public function getActiveFlashSale(): ?FlashSale
    {
        return $this->getActiveFlashSales()->first();
    }

    /**
     * Lấy danh sách toàn bộ các chiến dịch Flash Sale đang hoạt động tại thời điểm hiện tại.
     * Đồng thời nạp trước (Eager Loading) các sản phẩm tham gia chương trình được sắp xếp thứ tự hiển thị.
     */
    public function getActiveFlashSales()
    {
        $now = Carbon::now();

        return FlashSale::query()
            // Chỉ lấy các sản phẩm đang được kích hoạt và sắp xếp theo trường sort_order
            ->with(['products' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            // Điều kiện: Chiến dịch phải ở trạng thái kích hoạt
            ->where('is_active', true)
            // Thời gian hiện tại phải nằm trong khoảng từ lúc bắt đầu đến lúc kết thúc chiến dịch
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            // Đảm bảo chiến dịch Flash Sale đó phải có ít nhất một sản phẩm đang kích hoạt
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            // Ưu tiên các chiến dịch bắt đầu gần nhất lên trước
            ->orderByDesc('start_at')
            ->get();
    }

    /**
     * Lấy thông tin chi tiết (FlashSaleProduct) của một sản phẩm cụ thể thuộc chiến dịch Flash Sale đang chạy.
     */
    public function getFlashSaleProductFor(Product $product): ?FlashSaleProduct
    {
        return $this->getActiveFlashSale()?->products->firstWhere('product_id', $product->product_id);
    }

    /**
     * Lấy giá bán thực tế (Effective Price) của sản phẩm.
     * Nếu sản phẩm đang thuộc đợt Flash Sale đang chạy và còn tồn kho khuyến mãi, trả về giá sale_price.
     * Ngược lại, trả về giá gốc (base_price).
     */
    public function getEffectivePrice(Product $product): int
    {
        $flashSaleProduct = $this->getFlashSaleProductFor($product);

        return $flashSaleProduct && $this->canApplySale($flashSaleProduct)
            ? (int) $flashSaleProduct->sale_price
            : (int) $product->base_price;
    }

    /**
     * Kiểm tra nhanh xem sản phẩm này có đang nằm trong đợt Flash Sale nào đang hoạt động hay không.
     */
    public function isFlashSaleProduct(Product $product): bool
    {
        return (bool) $this->getFlashSaleProductFor($product);
    }

    /**
     * Tính toán số lượng tồn kho còn lại của một sản phẩm trong chương trình Flash Sale.
     * Không trả về giá trị âm (sử dụng hàm max để giới hạn tối thiểu bằng 0).
     */
    public function getRemainingQuantity(FlashSaleProduct $flashSaleProduct): int
    {
        return max(0, (int) $flashSaleProduct->stock_limit - (int) $flashSaleProduct->sold_quantity);
    }

    /**
     * Kiểm tra xem sản phẩm Flash Sale có đủ điều kiện để áp dụng giảm giá hay không.
     * Điều kiện: Sản phẩm phải kích hoạt và số lượng còn lại phải lớn hơn 0.
     */
    public function canApplySale(FlashSaleProduct $flashSaleProduct): bool
    {
        return $flashSaleProduct->is_active && $this->getRemainingQuantity($flashSaleProduct) > 0;
    }

    /**
     * Giữ chỗ trước số lượng sản phẩm (Reserve Quantity) khi khách hàng tiến hành thanh toán.
     * Sử dụng Transaction kết hợp với "lockForUpdate" để khóa dòng dữ liệu trong DB.
     * Việc này đảm bảo không có 2 request đồng thời đọc và ghi đè số lượng bán vượt mức tồn kho (Over-selling).
     */
    public function reserveQuantity(FlashSaleProduct $flashSaleProduct, int $quantity): bool
    {
        if ($quantity <= 0) {
            return true;
        }

        return DB::transaction(function () use ($flashSaleProduct, $quantity) {
            // Thực hiện khóa dòng dữ liệu của sản phẩm Flash Sale này bằng lockForUpdate
            $locked = FlashSaleProduct::query()
                ->whereKey($flashSaleProduct->getKey())
                ->lockForUpdate()
                ->first();

            // Nếu không tìm thấy, sản phẩm đã bị tắt kích hoạt, hoặc số lượng tồn kho còn lại ít hơn số lượng đặt mua
            if (! $locked || ! $this->canApplySale($locked) || $this->getRemainingQuantity($locked) < $quantity) {
                return false; // Thất bại, không đủ số lượng giữ chỗ
            }

            // Tăng số lượng đã bán lên tương ứng với số lượng đặt mua
            $locked->increment('sold_quantity', $quantity);
            return true; // Giữ chỗ thành công
        });
    }

    /**
     * Giải phóng số lượng đã giữ chỗ trước đó (Release Quantity) nếu giao dịch thanh toán bị hủy,
     * hoặc giỏ hàng bị cập nhật lại giảm số lượng.
     */
    public function releaseQuantity(FlashSaleProduct $flashSaleProduct, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($flashSaleProduct, $quantity) {
            // Sử dụng lockForUpdate để khóa và cập nhật an toàn số lượng đã bán
            $locked = FlashSaleProduct::query()
                ->whereKey($flashSaleProduct->getKey())
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                return;
            }

            // Giảm số lượng đã bán đi, đảm bảo không âm dưới mức 0
            $locked->sold_quantity = max(0, (int) $locked->sold_quantity - $quantity);
            $locked->save();
        });
    }

    /**
     * Khoá và giữ chỗ số lượng cho tất cả các mặt hàng Flash Sale có mặt trong giỏ hàng.
     * Trả về true nếu toàn bộ các mặt hàng Flash Sale đều được giữ chỗ thành công.
     * Nếu có bất kỳ một mặt hàng nào không đủ số lượng để giữ chỗ, hệ thống sẽ thực hiện ROLLBACK,
     * giải phóng toàn bộ số lượng của các mặt hàng đã khoá trước đó trong lượt duyệt này để đảm bảo tính nhất quán.
     */
    public function lockCartFlashSale(array $cart): bool
    {
        $lockedItems = [];

        // Duyệt qua danh sách các sản phẩm thuộc chương trình Flash Sale trong giỏ hàng
        foreach ($this->flashSaleCartItems($cart) as $productId => $item) {
            $product = Product::find($productId);
            $flashSaleProduct = $product ? $this->getFlashSaleProductFor($product) : null;
            $quantity = (int) ($item['quantity'] ?? 0);

            // Nếu không tìm thấy sản phẩm Flash Sale hợp lệ hoặc không thể giữ chỗ đủ số lượng
            if (! $flashSaleProduct || ! $this->reserveQuantity($flashSaleProduct, $quantity)) {
                // Giải phóng ngay lập tức toàn bộ số lượng các sản phẩm đã khóa thành công trước đó
                $this->releaseLockedItems($lockedItems);
                return false; // Thất bại toàn bộ quá trình khóa giỏ hàng
            }

            // Ghi nhận sản phẩm và số lượng đã khóa tạm thời để dự phòng giải phóng khi lỗi
            $lockedItems[] = [$flashSaleProduct, $quantity];
        }

        return true; // Tất cả sản phẩm Flash Sale trong giỏ hàng đều được giữ chỗ thành công
    }

    /**
     * Xác nhận đơn hàng thành công chứa sản phẩm Flash Sale.
     * Do số lượng đã được giữ chỗ từ trước ở phương thức lockCartFlashSale(),
     * phương thức này được thiết kế như một hook rỗng để tích hợp lưu trữ hóa đơn/tích điểm sau này.
     */
    public function confirmCartFlashSale(array $cart): void
    {
        // Số lượng đã được giữ chỗ sẵn qua lockCartFlashSale() trước đó.
        // Giữ lại phương thức này để phục vụ việc tích hợp lưu trữ trạng thái đơn hàng trong tương lai.
    }

    /**
     * Giải phóng số lượng tồn kho Flash Sale đã khóa cho toàn bộ giỏ hàng.
     * Thường dùng khi phiên thanh toán giỏ hàng bị hết hạn hoặc khách hàng xóa giỏ.
     */
    public function releaseCartFlashSale(array $cart): void
    {
        foreach ($this->flashSaleCartItems($cart) as $productId => $item) {
            $product = Product::find($productId);
            $flashSaleProduct = $product ? $this->getFlashSaleProductFor($product) : null;

            if ($flashSaleProduct) {
                $this->releaseQuantity($flashSaleProduct, (int) ($item['quantity'] ?? 0));
            }
        }
    }

    /**
     * Hàm lọc: Chỉ lấy ra những phần tử trong giỏ hàng có chứa thuộc tính 'flash_sale_price'.
     */
    private function flashSaleCartItems(array $cart): array
    {
        return array_filter($cart, static fn (array $item) => isset($item['flash_sale_price']));
    }

    /**
     * Giải phóng danh sách các mặt hàng đã được giữ chỗ tạm thời.
     * Dùng để hoàn tác (Rollback) số lượng khi quá trình khóa giỏ hàng bị thất bại nửa chừng.
     */
    private function releaseLockedItems(array $lockedItems): void
    {
        foreach ($lockedItems as [$lockedProduct, $lockedQuantity]) {
            $this->releaseQuantity($lockedProduct, $lockedQuantity);
        }
    }
}
