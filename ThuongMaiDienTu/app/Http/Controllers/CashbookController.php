<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use Illuminate\Http\Request;

/**
 * Controller quản lý Sổ quỹ (Cashbook)
 * 
 * Class này chịu trách nhiệm điều hướng các thao tác nghiệp vụ liên quan đến dòng tiền thu và chi của cửa hàng,
 * đồng thời giải quyết bài toán chống giả mạo dữ liệu gửi từ Client-side (F12 bypass).
 */
class CashbookController extends Controller
{
    /**
     * Hàm hiển thị danh sách giao dịch Sổ quỹ
     * 
     * Hàm này thực hiện:
     * 1. Tìm kiếm và lọc danh sách giao dịch phân trang.
     * 2. Tải trước hàng loạt mã code của các tài liệu liên kết để hiển thị tên thân thiện (Tránh lỗi truy vấn N+1).
     * 3. Tính toán tổng thu, tổng chi và số dư hiện tại.
     * 4. Thống kê dữ liệu doanh thu & chi phí 7 ngày gần nhất để vẽ biểu đồ.
     */
    public function index(Request $request)
    {
        // Bắt đầu câu truy vấn lấy danh sách giao dịch sổ quỹ
        $cashbooks = Cashbook::query()
            // Áp dụng bộ lọc tìm kiếm theo từ khóa (mô tả,...) được định nghĩa trong Model
            ->search($request->input('search'))
            // Nếu người dùng chọn lọc theo phân loại (Income/Expense), tiến hành lọc
            ->when($request->filled('type'), fn ($q) => $q->ofType($request->type))
            // Ưu tiên đẩy các bản ghi có liên kết tài liệu lên trước, sau đó sắp xếp theo ID liên kết
            ->orderByRaw('reference_id IS NULL, reference_id ASC')
            // Sắp xếp các giao dịch mới nhất lên trên đầu danh sách
            ->orderBy('created_at', 'desc')
            // Phân trang danh sách giao dịch, mỗi trang hiển thị tối đa 12 bản ghi
            ->paginate(12)
            // Giữ lại các tham số tìm kiếm/lọc trên thanh URL khi chuyển trang
            ->withQueryString();

        // Khởi tạo các mảng chứa ID tài liệu liên kết để chuẩn bị truy vấn hàng loạt
        $orderIds = [];          // Danh sách ID đơn hàng liên kết
        $serviceInvoiceIds = [];  // Danh sách ID hóa đơn dịch vụ liên kết
        $purchaseOrderIds = [];   // Danh sách ID phiếu nhập kho liên kết
        $installmentIds = [];     // Danh sách ID hợp đồng trả góp liên kết

        // Vòng lặp duyệt qua các giao dịch Sổ quỹ hiện tại của trang
        foreach ($cashbooks as $cb) {
            $refId = $cb->reference_id;      // Lấy ID tài liệu liên kết của bản ghi này
            $refType = $cb->reference_type;  // Lấy loại tài liệu liên kết của bản ghi này

            // Cơ chế dự phòng (fallback): Nếu bản ghi có ID liên kết nhưng chưa được lưu loại liên kết trong DB
            if (!$refType && $refId) {
                // Nếu mô tả chứa chữ 'đơn hàng', suy luận loại liên kết là đơn hàng
                if (str_contains(strtolower($cb->description), 'đơn hàng')) {
                    $refType = 'order';
                // Nếu mô tả chứa chữ 'dịch vụ', suy luận loại liên kết là hóa đơn dịch vụ
                } elseif (str_contains(strtolower($cb->description), 'dịch vụ')) {
                    $refType = 'service_invoice';
                // Nếu mô tả chứa chữ 'nhập hàng', suy luận loại liên kết là phiếu nhập kho
                } elseif (str_contains(strtolower($cb->description), 'nhập hàng')) {
                    $refType = 'purchase_order';
                // Nếu mô tả chứa chữ 'trả góp', suy luận loại liên kết là hợp đồng trả góp
                } elseif (str_contains(strtolower($cb->description), 'trả góp')) {
                    $refType = 'installment';
                }
            }

            // Nếu có đầy đủ cả ID liên kết và loại liên kết hợp lệ
            if ($refId && $refType) {
                // Đưa ID vào mảng tương ứng để tí nữa truy vấn hàng loạt
                if ($refType === 'order') $orderIds[] = $refId;
                elseif ($refType === 'service_invoice') $serviceInvoiceIds[] = $refId;
                elseif ($refType === 'purchase_order') $purchaseOrderIds[] = $refId;
                elseif ($refType === 'installment') $installmentIds[] = $refId;
            }
        }

        // Truy vấn lấy thông tin mã đơn hàng thực tế (order_code) hàng loạt để hiển thị
        $existingOrders = [];
        if (!empty($orderIds)) {
            // Lấy danh sách đơn hàng có ID nằm trong mảng và chỉ lấy cột order_id, order_code
            $orders = \App\Models\Order::whereIn('order_id', array_unique($orderIds))->get(['order_id', 'order_code']);
            // Ánh xạ ID đơn hàng với mã code hiển thị
            foreach ($orders as $order) {
                $existingOrders[$order->order_id] = $order->order_code ?: '#' . $order->order_id;
            }
        }

        // Truy vấn lấy thông tin mã hóa đơn dịch vụ (invoice_no) hàng loạt để hiển thị
        $existingServiceInvoices = [];
        if (!empty($serviceInvoiceIds)) {
            // Lấy danh sách hóa đơn dịch vụ có ID nằm trong mảng và chỉ lấy cột id, invoice_no
            $invoices = \App\Models\ServiceInvoice::whereIn('id', array_unique($serviceInvoiceIds))->get(['id', 'invoice_no']);
            // Ánh xạ ID hóa đơn dịch vụ với mã số hóa đơn
            foreach ($invoices as $inv) {
                $existingServiceInvoices[$inv->id] = $inv->invoice_no ?: '#' . $inv->id;
            }
        }

        // Truy vấn lấy danh sách phiếu nhập kho hàng loạt để định dạng hiển thị thành dạng PO-XXXXX
        $existingPurchaseOrders = [];
        if (!empty($purchaseOrderIds)) {
            // Lấy các po_id của phiếu nhập kho tồn tại trong mảng
            $pos = \App\Models\PurchaseOrder::whereIn('po_id', array_unique($purchaseOrderIds))->pluck('po_id')->toArray();
            // Định dạng mã phiếu nhập kho có dạng PO-00001
            foreach ($pos as $poId) {
                $existingPurchaseOrders[$poId] = 'PO-' . str_pad($poId, 5, '0', STR_PAD_LEFT);
            }
        }

        // Truy vấn lấy danh sách hợp đồng trả góp hàng loạt để lấy mã installment_code
        $existingInstallments = [];
        if (!empty($installmentIds)) {
            // Lấy danh sách trả góp có ID trong mảng và chỉ lấy cột id, installment_code
            $insts = \App\Models\Installment::whereIn('id', array_unique($installmentIds))->get(['id', 'installment_code']);
            // Ánh xạ ID trả góp với mã hợp đồng trả góp
            foreach ($insts as $inst) {
                $existingInstallments[$inst->id] = $inst->installment_code ?: '#' . $inst->id;
            }
        }

        // Gộp tất cả các bản đồ ánh xạ tài liệu tham chiếu để truyền xuống view
        $existingRefs = [
            'order' => $existingOrders,
            'service_invoice' => $existingServiceInvoices,
            'purchase_order' => $existingPurchaseOrders,
            'installment' => $existingInstallments,
        ];

        // Tính tổng số tiền thu (Income) của toàn bộ hệ thống sổ quỹ
        $totalIncome  = Cashbook::ofType('Income')->sum('amount');
        // Tính tổng số tiền chi (Expense) của toàn bộ hệ thống sổ quỹ
        $totalExpense = Cashbook::ofType('Expense')->sum('amount');
        // Tính toán số dư quỹ hiện tại (Thu - Chi)
        $balance      = $totalIncome - $totalExpense;

        // Khởi tạo mảng cấu trúc dữ liệu cho biểu đồ 7 ngày gần nhất
        $chartData = [
            'labels' => [],  // Lưu danh sách ngày dạng (ngày/tháng) làm trục hoành
            'income' => [],  // Doanh thu từng ngày
            'expense' => []  // Chi phí từng ngày
        ];

        // Vòng lặp chạy ngược từ 6 ngày trước cho đến ngày hôm nay
        for ($i = 6; $i >= 0; $i--) {
            // Lấy đối tượng ngày tương ứng
            $currentDay = \Carbon\Carbon::now('Asia/Ho_Chi_Minh')->subDays($i);
            // Định dạng hiển thị dạng ngày/tháng (ví dụ 03/06)
            $date = $currentDay->format('d/m');
            // Định dạng ngày đầy đủ YYYY-MM-DD để truy vấn DB
            $fullDate = $currentDay->toDateString();
            
            // Đưa ngày/tháng vào nhãn biểu đồ
            $chartData['labels'][] = $date;
            // Truy vấn cộng tổng các khoản thu của ngày này
            $chartData['income'][] = Cashbook::ofType('Income')
                ->whereDate('created_at', $fullDate)
                ->sum('amount');
            // Truy vấn cộng tổng các khoản chi của ngày này
            $chartData['expense'][] = Cashbook::ofType('Expense')
                ->whereDate('created_at', $fullDate)
                ->sum('amount');
        }

        // Trả về view giao diện sổ quỹ cùng toàn bộ các biến thống kê/tìm kiếm đã chuẩn bị
        return view('Cashbook.Cashbook', compact(
            'cashbooks', 'totalIncome', 'totalExpense', 'balance', 'chartData', 'existingRefs'
        ));
    }

    /**
     * Hàm xử lý thêm mới một Giao dịch Sổ quỹ
     * 
     * Hàm này thực hiện:
     * 1. Xác thực các trường cơ bản từ biểu mẫu frontend.
     * 2. Giải mã input mã tài liệu dạng chữ (Ví dụ: PO-00001, HD0001) về ID số nguyên khóa chính tương ứng.
     * 3. Kiểm tra chéo tính hợp lệ và sự tồn tại thực tế của tài liệu để chống giả mạo F12.
     * 4. Tạo giao dịch và lưu vào database.
     */
    public function store(Request $request)
    {
        // Thiết lập bộ quy tắc xác thực cơ bản cho biểu mẫu
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'type'           => 'required|in:Income,Expense', // Loại giao dịch bắt buộc là Income hoặc Expense
            'amount'         => 'required|integer|min:1000',  // Số tiền bắt buộc là số nguyên từ 1,000đ trở lên
            'description'    => 'required|string|max:500',   // Mô tả bắt buộc nhập chuỗi chữ, tối đa 500 ký tự
            'reference_id'   => 'nullable|max:100',          // Mã tài liệu liên kết có thể trống, tối đa 100 ký tự (chuỗi hoặc số)
            'reference_type' => 'nullable|string|in:order,service_invoice,purchase_order,installment', // Loại tài liệu phải nằm trong tập xác định
            'created_at'     => 'nullable|date',             // Ngày ghi nhận có thể trống, nếu nhập phải đúng dạng ngày giờ
        ], [
            // Các câu thông báo lỗi tùy chỉnh hiển thị khi xác thực thất bại
            'type.required'        => 'Vui lòng chọn loại giao dịch.',
            'amount.required'      => 'Vui lòng nhập số tiền.',
            'amount.min'           => 'Số tiền tối thiểu 1,000đ.',
            'description.required' => 'Vui lòng nhập nội dung.',
        ]);

        // Biến lưu trữ ID khóa chính sau khi đã xác định và giải mã thành công
        $resolvedId = null;

        // Định nghĩa logic kiểm tra nâng cao sau khi các quy tắc cơ bản ở trên đã vượt qua
        $validator->after(function ($validator) use ($request, &$resolvedId) {
            $refInput = $request->input('reference_id');      // Nhận mã tài liệu liên kết do người dùng nhập
            $refType = $request->input('reference_type');      // Nhận loại liên kết do người dùng chọn

            // Kiểm tra tính logic: có nhập mã liên kết thì bắt buộc phải chọn phân loại liên kết
            if ($refInput !== null && $refInput !== '' && !$refType) {
                $validator->errors()->add('reference_type', 'Vui lòng chọn loại liên kết khi đã nhập mã liên kết.');
            }
            // Ngược lại, nếu chọn phân loại liên kết thì bắt buộc phải nhập mã liên kết
            if ($refType && ($refInput === null || $refInput === '')) {
                $validator->errors()->add('reference_id', 'Vui lòng nhập mã liên kết khi đã chọn loại liên kết.');
            }

            // Tiến hành phân tích và kiểm tra tài liệu liên kết nếu cả hai thông tin đều được điền đầy đủ
            if ($refInput !== null && $refInput !== '' && $refType) {
                $exists = false; // Biến đánh dấu sự tồn tại của tài liệu
                
                // Trường hợp 1: Nếu người dùng nhập trực tiếp ID số nguyên thô
                if (is_numeric($refInput)) {
                    $refId = (int)$refInput; // Ép kiểu về số nguyên
                    // Kiểm tra sự tồn tại trong bảng tương ứng
                    switch ($refType) {
                        case 'order':
                            $exists = \App\Models\Order::where('order_id', $refId)->exists();
                            break;
                        case 'service_invoice':
                            $exists = \App\Models\ServiceInvoice::where('id', $refId)->exists();
                            break;
                        case 'purchase_order':
                            $exists = \App\Models\PurchaseOrder::where('po_id', $refId)->exists();
                            break;
                        case 'installment':
                            $exists = \App\Models\Installment::where('id', $refId)->exists();
                            break;
                    }
                    // Nếu tài liệu tồn tại thực tế, ghi nhận resolvedId là ID số nguyên đó
                    if ($exists) {
                        $resolvedId = $refId;
                    }
                }

                // Trường hợp 2: Nếu người dùng nhập mã hiển thị dạng chữ (Code)
                if (!$resolvedId) {
                    $cleanInput = trim($refInput); // Loại bỏ khoảng trắng thừa
                    switch ($refType) {
                        case 'order':
                            // Tìm kiếm ID đơn hàng khớp với cột order_code đơn hàng
                            $resolvedId = \App\Models\Order::where('order_code', $cleanInput)->value('order_id');
                            break;
                        case 'service_invoice':
                            // Tìm kiếm ID hóa đơn khớp với cột invoice_no hóa đơn dịch vụ
                            $resolvedId = \App\Models\ServiceInvoice::where('invoice_no', $cleanInput)->value('id');
                            break;
                        case 'purchase_order':
                            // Kiểm tra nếu mã khớp với biểu thức định dạng PO-XXXXX (Ví dụ: PO-00012)
                            if (preg_match('/^PO-(\d+)$/i', $cleanInput, $matches)) {
                                $poId = (int)$matches[1]; // Tách lấy phần số của phiếu nhập kho
                                // Kiểm tra xem phiếu nhập kho này có tồn tại hay không
                                if (\App\Models\PurchaseOrder::where('po_id', $poId)->exists()) {
                                    $resolvedId = $poId; // Gán resolvedId là phần số tách được
                                }
                            } else {
                                // Nếu nhập số thô không có chữ PO- vẫn cho phép
                                if (is_numeric($cleanInput)) {
                                    $poId = (int)$cleanInput;
                                    if (\App\Models\PurchaseOrder::where('po_id', $poId)->exists()) {
                                        $resolvedId = $poId;
                                    }
                                }
                            }
                            break;
                        case 'installment':
                            // Tìm kiếm ID hợp đồng trả góp khớp với cột installment_code
                            $resolvedId = \App\Models\Installment::where('installment_code', $cleanInput)->value('id');
                            break;
                    }
                }

                // Nếu sau cả hai bước tìm kiếm mà resolvedId vẫn trống (không tìm thấy tài liệu thực tế nào khớp)
                if (!$resolvedId) {
                    // Trả về lỗi ngăn chặn việc thêm dữ liệu sai lệch hoặc giả mạo
                    $validator->errors()->add('reference_id', 'Mã tài liệu liên kết không tồn tại trong hệ thống với loại liên kết đã chọn.');
                }
            }
        });

        // Kích hoạt việc kiểm tra và lấy dữ liệu đã xác thực (sẽ tự động chuyển hướng kèm lỗi nếu thất bại)
        $data = $validator->validate();

        // Thu thập các dữ liệu hợp lệ để chuẩn bị chèn vào Database
        $insertData = $request->only('type', 'amount', 'description', 'reference_type');
        // Gán reference_id là ID khóa chính số nguyên đã được giải mã và kiểm chứng thành công
        $insertData['reference_id'] = $resolvedId;
        // Nếu người dùng chọn thời gian ghi nhận khác thời điểm hiện tại
        if ($request->filled('created_at')) {
            $insertData['created_at'] = $request->created_at;
        }

        // Tạo bản ghi giao dịch sổ quỹ mới trong cơ sở dữ liệu
        Cashbook::create($insertData);

        // Chuyển hướng người dùng về trang danh sách và hiển thị thông báo thành công
        return redirect()->route('admin.cashbooks.index')
            ->with('success', 'Đã thêm giao dịch thành công!');
    }

    /**
     * Hàm hiển thị form chỉnh sửa giao dịch (Phương thức phụ)
     */
    public function edit(Cashbook $cashbook)
    {
        return view('Cashbook.edit', compact('cashbook'));
    }

    /**
     * Hàm xử lý Cập Nhật giao dịch Sổ quỹ
     * 
     * Khóa bảo mật (Security Lock): 
     * Hàm này KHÔNG cho phép người dùng thay đổi reference_id và reference_type trong cơ sở dữ liệu khi cập nhật.
     * Điều này bảo vệ tính toàn vẹn của sổ sách tài chính khi liên kết đã được thiết lập từ lúc tạo.
     */
    public function update(Request $request, $id)
    {
        // Tìm bản ghi Sổ quỹ cần chỉnh sửa, ném lỗi 404 nếu không tồn tại
        $cashbook = Cashbook::findOrFail($id);
        
        // Thiết lập bộ xác thực các thông tin cơ bản cho phép chỉnh sửa
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'type'        => 'required|in:Income,Expense', // Bắt buộc chọn loại thu hoặc chi
            'amount'      => 'required|integer|min:1000',  // Số tiền tối thiểu 1000đ
            'description' => 'required|string|max:500',   // Mô tả tối đa 500 ký tự
            'created_at'  => 'nullable|date',             // Ngày ghi nhận
        ]);

        // Thực hiện xác thực biểu mẫu
        $data = $validator->validate();

        // Chỉ lấy các trường cơ bản cho phép sửa (Loại bỏ hoàn toàn reference_id và reference_type)
        $updateData = $request->only('type', 'amount', 'description');
        // Nếu thay đổi ngày ghi nhận
        if ($request->filled('created_at')) {
            $updateData['created_at'] = $request->created_at;
        }

        // Thực hiện lưu các thay đổi mới vào database
        $cashbook->update($updateData);

        // Chuyển hướng về trang danh sách sổ quỹ kèm thông báo cập nhật thành công
        return redirect()->route('admin.cashbooks.index')
            ->with('success', 'Đã cập nhật giao dịch!');
    }

    /**
     * Hàm xóa một giao dịch Sổ quỹ
     */
    public function destroy($id)
    {
        // Tìm kiếm giao dịch cần xóa, ném lỗi 404 nếu không tìm thấy
        $cashbook = Cashbook::findOrFail($id);
        // Tiến hành xóa bản ghi này khỏi cơ sở dữ liệu
        $cashbook->delete();

        // Điều hướng về trang sổ quỹ cùng thông báo xóa thành công
        return redirect()->route('admin.cashbooks.index')
            ->with('success', 'Đã xóa giao dịch.');
    }

    /**
     * Hàm xóa hàng loạt các giao dịch được chọn cùng một lúc
     */
    public function bulkDestroy(Request $request)
    {
        // Xác thực mảng danh sách ID gửi lên
        $request->validate([
            'ids'   => 'required|array|min:1',                        // Bắt buộc phải là mảng chứa ít nhất một phần tử
            'ids.*' => 'required|integer|exists:cashbooks,cashbook_id', // Mỗi phần tử trong mảng phải là ID số nguyên tồn tại trong DB
        ], [
            'ids.required' => 'Vui lòng chọn ít nhất một giao dịch để xóa.',
            'ids.*.exists' => 'Một trong những giao dịch được chọn không tồn tại.',
        ]);

        // Nhận mảng các ID giao dịch được tích chọn từ client
        $ids = $request->input('ids', []);
        // Thực hiện câu lệnh xóa hàng loạt các dòng có ID nằm trong danh sách
        Cashbook::whereIn('cashbook_id', $ids)->delete();

        // Chuyển hướng người dùng về trang chính kèm thông báo số lượng bản ghi đã xóa
        return redirect()->route('admin.cashbooks.index')
            ->with('success', 'Đã xóa ' . count($ids) . ' giao dịch được chọn.');
    }
}
