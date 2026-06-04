<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WarrantyController extends Controller
{
    /**
     * Hiển thị trang tra cứu bảo hành
     */
    public function index()
    {
        return view('policy.warranty');
    }

    /**
     * Hiển thị trang chính sách đổi trả
     */
    public function returnPolicy()
    {
        return view('policy.return_policy');
    }

    /**
     * Xử lý tra cứu bảo hành theo IMEI (AJAX)
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'imei' => 'required|string|min:8|max:30',
        ]);

        $imei = trim($request->input('imei'));

        // Tìm thiết bị theo IMEI
        $item = InventoryItem::where('imei_serial', $imei)->first();

        if (!$item) {
            // 1. Thử kiểm tra xem có phải mã đơn hàng hay không
            $order = \App\Models\Order::with(['details.inventoryItem.variant.product'])
                ->where('order_code', $imei)
                ->orWhere('order_id', $imei)
                ->first();

            if ($order) {
                $foundItems = $order->details->map(function ($d) {
                    return $d->inventoryItem;
                })->filter();

                if ($foundItems->isNotEmpty()) {
                    $itemHtml = $foundItems->map(function ($fi) {
                        $pName = $fi->variant->product->name ?? 'Sản phẩm';
                        $colorRom = $fi->variant ? ' (' . $fi->variant->color . ' / ' . $fi->variant->rom_capacity . ')' : '';
                        return "<li><strong>{$pName}{$colorRom}</strong>: <code style='background:#f1f5f9; padding:2px 6px; border-radius:4px; font-weight:bold; color:#0f172a; font-family:monospace; user-select:all;'>{$fi->imei_serial}</code></li>";
                    })->join('');

                    return response()->json([
                        'success' => false,
                        'message' => "Không tìm thấy thiết bị trực tiếp, nhưng phát hiện đơn hàng <strong>#{$order->order_code}</strong> chứa các sản phẩm dưới đây. Vui lòng sao chép mã IMEI/Serial tương ứng để thực hiện tra cứu:<br><ul style='text-align:left; margin-top:12px; display:inline-block; padding-left:20px; line-height:2.0;'>{$itemHtml}</ul>",
                    ], 404);
                }
            }

            // 2. Thử kiểm tra xem có phải số điện thoại của khách hàng không
            if (preg_match('/^[0-9+]{9,15}$/', $imei)) {
                $phoneClean = preg_replace('/[^0-9+]/', '', $imei);
                $orders = \App\Models\Order::with(['details.inventoryItem.variant.product'])
                    ->where('customer_phone', $phoneClean)
                    ->orWhere('customer_phone', 'like', '%' . $phoneClean)
                    ->get();

                if ($orders->isNotEmpty()) {
                    $foundItems = collect();
                    foreach ($orders as $ord) {
                        foreach ($ord->details as $d) {
                            if ($d->inventoryItem) {
                                $foundItems->push($d->inventoryItem);
                            }
                        }
                    }

                    if ($foundItems->isNotEmpty()) {
                        $itemHtml = $foundItems->map(function ($fi) {
                            $pName = $fi->variant->product->name ?? 'Sản phẩm';
                            $colorRom = $fi->variant ? ' (' . $fi->variant->color . ' / ' . $fi->variant->rom_capacity . ')' : '';
                            return "<li><strong>{$pName}{$colorRom}</strong>: <code style='background:#f1f5f9; padding:2px 6px; border-radius:4px; font-weight:bold; color:#0f172a; font-family:monospace; user-select:all;'>{$fi->imei_serial}</code></li>";
                        })->unique('imei_serial')->join('');

                        return response()->json([
                            'success' => false,
                            'message' => "Tìm thấy các thiết bị đã mua bằng số điện thoại này. Vui lòng sao chép mã IMEI/Serial bên dưới để tra cứu bảo hành:<br><ul style='text-align:left; margin-top:12px; display:inline-block; padding-left:20px; line-height:2.0;'>{$itemHtml}</ul>",
                        ], 404);
                    }
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thiết bị với mã IMEI/Serial này trong hệ thống.',
            ], 404);
        }

        // Lấy thông tin sản phẩm qua variant
        $variant = $item->variant;
        $product = $variant ? $variant->product : null;

        // Tìm bảo hành
        $warranty = Warranty::where('item_id', $item->item_id)
            ->orderBy('end_date', 'desc')
            ->first();

        $now   = Carbon::now();
        // Dùng today() (midnight) để tránh lỗi float khi diffInDays so sánh datetime vs date
        $today = Carbon::today();

        // Xây dựng response
        $result = [
            'success'        => true,
            'imei'           => $item->imei_serial,
            'product_name'   => $product ? $product->name : 'Không xác định',
            'product_image'  => $product ? $product->thumbnail : null,
            'variant_label'  => $variant ? ($variant->color . ' / ' . $variant->rom_capacity) : '',
            'device_status'  => $item->status,
        ];

        // 1. Kiểm tra nếu sản phẩm chưa được bán ra (không phải Sold)
        if ($item->status !== 'Sold') {
            $result['has_warranty']       = false;
            $result['warranty_status']    = 'none';
            $result['can_claim_warranty'] = false;
            $result['can_claim_return']   = false;
            $result['note']               = 'Sản phẩm này chưa được bán ra (Đang trong kho). Không thể gửi yêu cầu bảo hành hoặc đổi trả.';
        } else {
            // 2. Nếu đã bán, kiểm tra bảo hành
            if ($warranty) {
                // So sánh thuần ngày (không có giờ) để tránh bug diffInDays = 0 khi còn <24h
                $isExpired      = $today->greaterThan($warranty->end_date);
                $daysLeft       = $isExpired ? 0 : (int) $today->diffInDays($warranty->end_date);
                $daysSinceStart = (int) $warranty->start_date->diffInDays($today);

                $result['has_warranty']    = true;
                $result['start_date']      = $warranty->start_date->format('d/m/Y');
                $result['end_date']        = $warranty->end_date->format('d/m/Y');
                $result['warranty_status'] = $isExpired ? 'expired' : $warranty->warranty_status;
                $result['warranty_type']   = $warranty->warranty_type;
                $result['days_left']       = $daysLeft;
                $result['note']            = $warranty->note;

                // Nút bảo hành: Phải có trạng thái active và chưa hết hạn
                $result['can_claim_warranty'] = ($result['warranty_status'] === 'active' && !$isExpired);

                // Nút đổi trả: Khoảng cách từ start_date tới nay <= số ngày đổi trả quy định của danh mục
                $returnDays = $this->getReturnPeriodDays($item);
                $result['return_days'] = $returnDays;
                if ($returnDays === 0 || $isExpired) {
                    $result['can_claim_return'] = false;
                    $result['return_days_left'] = 0;
                } else {
                    $result['can_claim_return'] = ($daysSinceStart <= $returnDays);
                    $result['return_days_left'] = max(0, $returnDays - $daysSinceStart);
                }
            } else {
                $result['has_warranty']       = false;
                $result['warranty_status']    = 'none';
                $result['can_claim_warranty'] = false;
                $result['can_claim_return']   = false;
                $result['note']               = 'Thiết bị này chưa được kích hoạt bảo hành. Vui lòng liên hệ hotline để được hỗ trợ kích hoạt.';
            }
        }

        // Lịch sử sửa chữa liên quan
        $repairHistory = \App\Models\RepairTicket::where('imei_serial', $imei)
            ->orderBy('ticket_id', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'ticket_id'   => $ticket->ticket_id,
                    'status'      => $ticket->status,
                    'issue'       => $ticket->issue_desc,
                    'cost'        => $ticket->estimated_cost,
                ];
            });

        $result['repair_history'] = $repairHistory;

        // Lịch sử yêu cầu bảo hành/đổi trả liên quan
        $claimsHistory = \App\Models\WarrantyClaim::where('imei_serial', $imei)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($c) {
                return [
                    'id'            => $c->id,
                    'claim_type'    => $c->claim_type,
                    'status'        => $c->status,
                    'reason'        => $c->reason,
                    'media_path'    => $c->media_path ? asset($c->media_path) : null,
                    'admin_note'    => $c->admin_note,
                    'created_at'    => $c->created_at ? $c->created_at->format('d/m/Y H:i') : null,
                ];
            });

        $result['claims_history'] = $claimsHistory;

        return response()->json($result);
    }

    /**
     * BƯỚC XỬ LÝ: GỬI YÊU CẦU DỊCH VỤ (BẢO HÀNH HOẶC ĐỔI TRẢ SẢN PHẨM)
     * Hàm này tiếp nhận dữ liệu từ form khách gửi lên, kiểm tra tính hợp lệ và lưu vào cơ sở dữ liệu.
     */
    public function storeClaim(Request $request)
    {
        // [Tương thích ngược] Nếu khách chọn "Đổi trả" (return) mà chưa chọn cách nhận tiền, tự động gán là chuyển khoản.
        if ($request->input('claim_type') === 'return' && !$request->has('refund_method')) {
            $request->merge(['refund_method' => 'bank_transfer']);
        }

        // ==========================================
        // 1. KIỂM TRA ĐỊNH DẠNG DỮ LIỆU ĐẦU VÀO (VALIDATION)
        // Đảm bảo dữ liệu khách gửi không bị thiếu, không bị quá dài và đúng kiểu dữ liệu.
        // ==========================================
        $request->validate([
            'imei_serial'    => 'required|string|exists:inventory_items,imei_serial', // Bắt buộc nhập IMEI, và IMEI này phải có thực trong hệ thống.
            'customer_name'  => 'required|string|max:100', // Bắt buộc nhập họ tên, tối đa 100 chữ cái.
            'customer_phone' => 'required|string|max:20',  // Bắt buộc nhập số điện thoại liên hệ, tối đa 20 ký tự.
            'customer_email' => 'nullable|email|max:100',  // Không bắt buộc, nhưng nếu nhập thì phải đúng dạng email (vd: abc@gmail.com).
            'claim_type'     => 'required|in:warranty,return,exchange', // Phải chọn 1 trong 3: Bảo hành (warranty), Đổi trả (return), Đổi máy (exchange).
            'reason'         => 'required|string|max:1000', // Bắt buộc nhập lý do cụ thể lý do cần bảo hành/đổi trả.
            // Cho phép đính kèm ảnh hoặc video chứng minh lỗi sản phẩm (dung lượng tối đa 20MB).
            'media_file'     => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,mkv,webm,3gp|max:20480',
            // Các trường tài khoản ngân hàng: chỉ bắt buộc khi chọn phương thức nhận tiền hoàn là "Chuyển khoản".
            'refund_method'         => 'required_if:claim_type,return|nullable|in:cash,bank_transfer',
            'bank_name'             => 'required_if:refund_method,bank_transfer|nullable|string|max:100',
            'bank_account_number'   => 'required_if:refund_method,bank_transfer|nullable|string|max:50',
            'bank_account_name'     => 'required_if:refund_method,bank_transfer|nullable|string|max:100',
        ], [
            // Các thông báo lỗi thân thiện hiển thị ra màn hình nếu người dùng nhập sai/thiếu.
            'imei_serial.required' => 'Vui lòng cung cấp mã IMEI/Serial.',
            'imei_serial.exists'   => 'Mã IMEI/Serial này không tồn tại trong hệ thống.',
            'customer_name.required' => 'Vui lòng nhập họ tên.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'claim_type.required' => 'Vui lòng chọn loại yêu cầu.',
            'claim_type.in' => 'Loại yêu cầu không hợp lệ.',
            'reason.required' => 'Vui lòng nhập lý do cụ thể.',
            'media_file.file' => 'Tệp đính kèm không hợp lệ.',
            'media_file.mimes' => 'Hệ thống chỉ hỗ trợ hình ảnh (jpeg, png, jpg, gif, webp) hoặc video (mp4, mov, avi, mkv, webm, 3gp).',
            'media_file.max' => 'Dung lượng tệp tối đa được phép là 20MB.',
            'refund_method.required_if' => 'Vui lòng chọn phương thức hoàn tiền.',
            'refund_method.in' => 'Phương thức hoàn tiền không hợp lệ.',
            'bank_name.required_if' => 'Vui lòng nhập tên ngân hàng để nhận tiền hoàn.',
            'bank_account_number.required_if' => 'Vui lòng nhập số tài khoản ngân hàng.',
            'bank_account_name.required_if' => 'Vui lòng nhập tên chủ tài khoản ngân hàng.',
        ]);

        // Tìm thiết bị trong kho hàng theo mã IMEI/Serial.
        $item = InventoryItem::where('imei_serial', $request->imei_serial)->first();

        // ==========================================
        // 2. KIỂM TRA TRẠNG THÁI THIẾT BỊ
        // Chỉ cho phép thiết bị đã bán ra mới được yêu cầu bảo hành hoặc đổi trả.
        // ==========================================
        if ($item->status !== 'Sold') {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm này chưa được bán ra (Đang trong kho). Không thể gửi yêu cầu dịch vụ.',
            ], 422);
        }

        // ==========================================
        // 3. CHỐNG GỬI YÊU CẦU TRÙNG LẶP (ANTI-SPAM)
        // Nếu thiết bị này đang có một yêu cầu ở trạng thái Chờ duyệt (pending), không cho gửi thêm.
        // ==========================================
        $existingPendingClaim = \App\Models\WarrantyClaim::where('imei_serial', $request->imei_serial)
            ->where('status', 'pending')
            ->first();

        if ($existingPendingClaim) {
            return response()->json([
                'success' => false,
                'message' => 'Thiết bị với mã IMEI này đang có một yêu cầu hỗ trợ chờ xử lý. Vui lòng đợi phản hồi từ ban quản trị.',
            ], 422);
        }

        // ==========================================
        // 4. BẢO MẬT & KIỂM TRA SỞ HỮU (CHỐNG F12)
        // Kiểm tra xem khách hàng gửi yêu cầu có thực sự mua thiết bị này không.
        // ==========================================
        $orderDetail = \App\Models\OrderDetail::where('item_id', $item->item_id)->first();
        if ($orderDetail && $orderDetail->order) {
            $order = $orderDetail->order;
            
            // Hàm phụ để dọn sạch số điện thoại (bỏ khoảng trắng, dấu cộng, số 0 ở đầu) để so sánh chính xác hơn.
            $normalizePhone = function ($phone) {
                $cleaned = preg_replace('/[\s.+\-]/', '', $phone);
                return ltrim($cleaned, '0');
            };

            if (auth()->check() && $order->user_id) {
                // Nếu khách đã đăng nhập: thiết bị phải thuộc đơn hàng của chính tài khoản này.
                if ($order->user_id !== auth()->id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn không sở hữu sản phẩm này. Không thể gửi yêu cầu hỗ trợ.',
                    ], 403);
                }
            } else {
                // Nếu khách mua vãng lai: Số điện thoại nhập vào form phải khớp với số điện thoại đặt đơn hàng này.
                $submittedPhoneNormalized = $normalizePhone($request->customer_phone);
                $orderPhoneNormalized = $normalizePhone($order->customer_phone ?: ($order->user->phone_number ?? ''));

                if ($submittedPhoneNormalized !== $orderPhoneNormalized) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Số điện thoại liên hệ không trùng khớp với thông tin mua hàng của thiết bị này.',
                    ], 403);
                }
            }
        }

        // Tìm thông tin gói bảo hành đã kích hoạt gần nhất của sản phẩm.
        $warranty = Warranty::where('item_id', $item->item_id)
            ->orderBy('end_date', 'desc')
            ->first();

        $today = Carbon::today();

        // ==========================================
        // 5. KIỂM TRA THỜI HẠN HIỆU LỰC CHO TỪNG LOẠI YÊU CẦU
        // ==========================================
        if ($request->claim_type === 'warranty') {
            // A. Loại Yêu cầu: BẢO HÀNH SỬA CHỮA
            if (!$warranty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiết bị này chưa được kích hoạt bảo hành. Vui lòng kích hoạt bảo hành trước.',
                ], 422);
            }

            // Gói bảo hành phải còn hạn sử dụng và đang ở trạng thái kích hoạt (active).
            $isExpired = $today->greaterThan($warranty->end_date);
            if ($warranty->warranty_status !== 'active' || $isExpired) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiết bị đã hết hạn bảo hành hoặc đang không ở trạng thái hoạt động.',
                ], 422);
            }
        } else {
            // B. Loại Yêu cầu: ĐỔI TRẢ HÀNG HOẶC ĐỔI MÁY MỚI
            if (!$warranty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiết bị này chưa được kích hoạt bảo hành. Không thể xác định ngày mua để đổi trả.',
                ], 422);
            }

            // Tính số ngày được phép đổi trả theo chính sách sản phẩm (ví dụ: Điện thoại 30 ngày, Phụ kiện 15 ngày, ...).
            $returnDays = $this->getReturnPeriodDays($item);
            if ($returnDays === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu đổi trả thất bại. Sản phẩm thuộc nhóm phụ kiện dưới 1 triệu không hỗ trợ đổi trả hàng.',
                ], 422);
            }

            // Tính số ngày đã trôi qua kể từ ngày mua máy/ngày bắt đầu bảo hành.
            $daysSinceStart = (int) $warranty->start_date->diffInDays($today);
            if ($daysSinceStart > $returnDays) {
                return response()->json([
                    'success' => false,
                    'message' => "Yêu cầu đổi trả thất bại. Đã quá thời hạn đổi trả {$returnDays} ngày kể từ ngày kích hoạt bảo hành.",
                ], 422);
            }
        }

        // ==========================================
        // 6. XỬ LÝ LƯU TRỮ TỆP TIN ĐÍNH KÈM (HÌNH ẢNH / VIDEO)
        // ==========================================
        $mediaPath = null;
        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $path = $file->store('warranty_claims', 'public'); // Lưu tệp vào thư mục storage/app/public/warranty_claims
            $mediaPath = 'storage/' . $path;
        }

        // ==========================================
        // 7. LƯU THÔNG TIN YÊU CẦU VÀO CƠ SỞ DỮ LIỆU
        // ==========================================
        \App\Models\WarrantyClaim::create([
            'user_id'             => auth()->id(), // ID của tài khoản khách hàng gửi (nếu có).
            'imei_serial'         => $request->imei_serial,
            'customer_name'       => $request->customer_name,
            'customer_phone'      => $request->customer_phone,
            'customer_email'      => $request->customer_email,
            'claim_type'          => $request->claim_type,
            'reason'              => $request->reason,
            'media_path'          => $mediaPath,
            'status'              => 'pending', // Yêu cầu mới tạo mặc định ở trạng thái Chờ Duyệt (pending).
            'refund_method'       => $request->claim_type === 'return' ? $request->refund_method : null,
            'bank_name'           => ($request->claim_type === 'return' && $request->refund_method === 'bank_transfer') ? $request->bank_name : null,
            'bank_account_number' => ($request->claim_type === 'return' && $request->refund_method === 'bank_transfer') ? $request->bank_account_number : null,
            'bank_account_name'   => ($request->claim_type === 'return' && $request->refund_method === 'bank_transfer') ? $request->bank_account_name : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu của bạn đã được gửi thành công. Ban quản trị sẽ sớm liên hệ duyệt yêu cầu!',
        ]);
    }

    /**
     * Lấy số ngày được phép đổi trả theo chính sách sản phẩm/danh mục
     */
    private function getReturnPeriodDays(InventoryItem $item): int
    {
        $variant = $item->variant;
        $product = $variant ? $variant->product : null;
        
        $returnDays = 30;
        if ($variant && $product) {
            $category = $product->category;
            $rootCategoryId = $category ? $category->getRootCategoryId() : null;
            $rootCategory = $rootCategoryId ? \App\Models\Category::find($rootCategoryId) : null;
            $rootCategoryName = $rootCategory ? $rootCategory->name : '';

            if ($rootCategoryName === 'Phụ kiện') {
                if (stripos($product->name, 'Airpod') !== false) {
                    $returnDays = 30;
                } else {
                    $price = $variant->total_price ?? ($product->base_price ?? 0);
                    $returnDays = $price > 1000000 ? 15 : 0;
                }
            } elseif (in_array($rootCategoryName, ['Âm thanh', 'Tivi, Màn hình', 'Gia dụng, Smarthome'])) {
                $returnDays = 15;
            } else {
                $returnDays = 30;
            }
        }
        return $returnDays;
    }
}
