<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceInvoiceController extends Controller
{
    /**
     * Phương thức index(): Hiển thị danh sách toàn bộ hóa đơn dịch vụ trong admin.
     * Hỗ trợ tìm kiếm và lọc dữ liệu động:
     *   - Tìm theo mã hóa đơn (invoice_no).
     *   - Lọc theo trạng thái hóa đơn (status: draft, issued, paid, cancelled).
     *   - Lọc theo khoảng ngày phát hành hóa đơn (from_date, to_date).
     * Sắp xếp danh sách theo thời gian tạo mới nhất và phân trang 10 dòng/trang.
     */
    public function index(Request $request): View
    {
        $query = ServiceInvoice::query();

        // Lọc theo mã hóa đơn nếu người dùng có nhập vào ô tìm kiếm
        if ($request->filled('invoice_no')) {
            $query->where('invoice_no', 'like', '%' . $request->string('invoice_no') . '%');
        }

        // Lọc theo trạng thái hóa đơn được chọn
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        // Lọc theo ngày bắt đầu xuất hóa đơn
        if ($request->filled('from_date')) {
            $query->whereDate('issued_date', '>=', $request->date('from_date'));
        }

        // Lọc theo ngày kết thúc xuất hóa đơn
        if ($request->filled('to_date')) {
            $query->whereDate('issued_date', '<=', $request->date('to_date'));
        }

        // Truy vấn dữ liệu từ DB, sắp xếp mới nhất lên đầu và phân trang
        $invoices = $query->latest()->paginate(10)->withQueryString();

        return view('admin.service-invoices.index', compact('invoices'));
    }

    /**
     * Phương thức create(): Hiển thị giao diện tạo hóa đơn dịch vụ mới (thủ công).
     * Tự động sinh mã hóa đơn nháp mẫu có dạng "INV-[NămThángNgày]-[6 ký tự ngẫu nhiên]" để gợi ý cho Admin.
     */
    public function create(): View
    {
        return view('admin.service-invoices.create', [
            'prefill' => [
                'invoice_no' => 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
            ]
        ]);
    }

    /**
     * Phương thức store(): Thực hiện lưu trữ hóa đơn dịch vụ mới vào cơ sở dữ liệu.
     * Hàm này thực thi:
     *   - Xác thực (Validation) dữ liệu đầu vào (tên khách hàng, tên dịch vụ, IMEI, số tiền tạm tính, VAT, giảm giá, trạng thái).
     *   - Kiểm tra logic sau xác thực (after callback): Nếu hóa đơn phát hành hoặc đã trả tiền thì tiền tạm tính phải > 0.
     *   - Tính toán tiền thuế VAT = (tạm tính * %VAT) / 100 và tiền thực nhận sau giảm giá tại Server-side.
     *   - Tạo hóa đơn và tự động cập nhật liên kết sang phiếu sửa chữa gốc nếu hóa đơn được xuất từ một phiếu sửa chữa.
     */
    public function store(Request $request)
    {
        // 1. Xác thực dữ liệu cơ bản gửi từ form
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'invoice_no' => ['required', 'string', 'max:50', 'unique:service_invoices,invoice_no'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'imei_serial' => ['required', 'string', 'max:255'],
            'service_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subtotal' => ['required', 'integer', 'min:0'],
            'vat_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,issued,paid,cancelled'],
        ], [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'service_name.required' => 'Vui lòng nhập tên dịch vụ.',
            'subtotal.required' => 'Vui lòng nhập số tiền tạm tính.',
            'subtotal.integer' => 'Số tiền tạm tính phải là số nguyên.',
            'imei_serial.required' => 'Vui lòng nhập mã IMEI / Serial.',
            'customer_phone.regex' => 'Số điện thoại phải đúng 10 chữ số.',
        ]);

        // 2. Kiểm tra logic bổ sung: Không được để phí dịch vụ bằng 0 nếu đã phát hành hoặc thanh toán hóa đơn
        $validator->after(function ($validator) use ($request) {
            $status = $request->input('status');
            $subtotal = (float) $request->input('subtotal', 0);

            if (in_array($status, ['issued', 'paid']) && $subtotal <= 0) {
                $validator->errors()->add('subtotal', 'Số tiền tạm tính phải lớn hơn 0 khi phát hành hoặc thanh toán hóa đơn.');
            }
        });

        // Kích hoạt ném lỗi validation nếu có sai sót dữ liệu
        $data = $validator->validate();

        // 3. Tính toán tiền thuế và tổng tiền thực tế trên server
        $subtotal = (int) $data['subtotal'];
        $vatRate = (int) ($data['vat_rate'] ?? 0);
        $taxAmount = (int) round(($subtotal * $vatRate) / 100);
        $discountAmount = (int) ($data['discount_amount'] ?? 0);
        $totalAmount = (int) max(0, $subtotal + $taxAmount - $discountAmount);

        // 4. Tạo bản ghi hóa đơn mới trong DB
        $invoice = ServiceInvoice::create([
            'invoice_no' => $data['invoice_no'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'imei_serial' => $data['imei_serial'] ?? null,
            'service_name' => $data['service_name'],
            'description' => $data['description'] ?? null,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'status' => $data['status'],
            // Gán ngày phát hành nếu hóa đơn không phải là nháp
            'issued_date' => $data['status'] === 'draft' ? null : now()->toDateString(),
            'created_by' => auth()->id(),
        ]);

        // 5. Nếu hóa đơn được xuất từ một phiếu sửa chữa, tự động lưu ngược thông tin hóa đơn vào phiếu sửa chữa đó
        if ($request->filled('repair_ticket_id')) {
            $repairTicket = \App\Models\RepairTicket::find($request->integer('repair_ticket_id'));
            if ($repairTicket && ! $repairTicket->invoice_no) {
                $repairTicket->update([
                    'invoice_no' => $invoice->invoice_no,
                    'invoiced_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.service-invoices.index')->with('success', 'Tạo hóa đơn dịch vụ thành công.');
    }

    /**
     * Phương thức show(): Xem chi tiết hóa đơn dịch vụ.
     */
    public function show(ServiceInvoice $serviceInvoice): View
    {
        return view('admin.service-invoices.show', compact('serviceInvoice'));
    }

    /**
     * Phương thức print(): Trả về giao diện html chuyên dụng cho việc in ấn hóa đơn.
     */
    public function print(ServiceInvoice $serviceInvoice): View
    {
        return view('admin.service-invoices.print', compact('serviceInvoice'));
    }

    /**
     * Phương thức pdf(): Xuất file PDF của hóa đơn dịch vụ và bắt đầu tải xuống trình duyệt ngay lập tức.
     */
    public function pdf(ServiceInvoice $serviceInvoice)
    {
        // Load view in hóa đơn dịch vụ vào trình tạo PDF
        $pdf = Pdf::loadView('admin.service-invoices.print', compact('serviceInvoice'));

        return $pdf->download($serviceInvoice->invoice_no . '.pdf');
    }

    /**
     * Phương thức savePdf(): Lưu file PDF hóa đơn vào thư mục lưu trữ public/invoices nếu chưa tồn tại và tải xuống.
     */
    public function savePdf(ServiceInvoice $serviceInvoice)
    {
        $pdf = Pdf::loadView('admin.service-invoices.print', compact('serviceInvoice'));
        $filename = $serviceInvoice->invoice_no . '.pdf';
        $path = 'invoices/' . $filename;

        // Nếu file PDF chưa tồn tại trên ổ đĩa public, tiến hành lưu trữ file vật lý
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $pdf->output());
        }

        return response()->download(storage_path('app/public/' . $path), $filename);
    }

    /**
     * Phương thức openPdf(): Xuất hóa đơn ra dạng PDF và mở hiển thị trực tiếp trên tab mới của trình duyệt.
     */
    public function openPdf(ServiceInvoice $serviceInvoice)
    {
        $path = 'invoices/' . $serviceInvoice->invoice_no . '.pdf';

        // Tạo và ghi đè file PDF nếu chưa được lưu trữ trước đó
        if (! Storage::disk('public')->exists($path)) {
            $pdf = Pdf::loadView('admin.service-invoices.print', compact('serviceInvoice'));
            Storage::disk('public')->put($path, $pdf->output());
        }

        return response()->file(storage_path('app/public/' . $path));
    }

    /**
     * Phương thức downloadSavedPdf(): Tìm và tải về file PDF đã được lưu trữ sẵn trong hệ thống files.
     */
    public function downloadSavedPdf(ServiceInvoice $serviceInvoice)
    {
        $path = 'invoices/' . $serviceInvoice->invoice_no . '.pdf';

        if (! Storage::disk('public')->exists($path)) {
            $pdf = Pdf::loadView('admin.service-invoices.print', compact('serviceInvoice'));
            Storage::disk('public')->put($path, $pdf->output());
        }

        return response()->download(storage_path('app/public/' . $path), $serviceInvoice->invoice_no . '.pdf');
    }

    /**
     * Phương thức edit(): Hiển thị form chỉnh sửa hóa đơn dịch vụ hiện có.
     */
    public function edit(ServiceInvoice $serviceInvoice): View
    {
        return view('admin.service-invoices.edit', compact('serviceInvoice'));
    }

    /**
     * Phương thức update(): Cập nhật thông tin chi tiết của một hóa đơn dịch vụ.
     * Thực thi các bước:
     *   - Xác thực dữ liệu form gửi lên.
     *   - Kiểm tra ràng buộc logic giá tiền khi phát hành/thanh toán hóa đơn.
     *   - Tính toán lại VAT, tiền thuế và tổng tiền thực tế.
     *   - Cập nhật ngày phát hành (issued_date) nếu hóa đơn được chuyển từ nháp sang trạng thái chính thức.
     */
    public function update(Request $request, ServiceInvoice $serviceInvoice): \Illuminate\Http\RedirectResponse
    {
        // 1. Xác thực dữ liệu
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'imei_serial' => ['required', 'string', 'max:255'],
            'service_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subtotal' => ['required', 'integer', 'min:0'],
            'vat_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,issued,paid,cancelled'],
        ], [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'service_name.required' => 'Vui lòng nhập tên dịch vụ.',
            'subtotal.required' => 'Vui lòng nhập số tiền tạm tính.',
            'subtotal.integer' => 'Số tiền tạm tính phải là số nguyên.',
            'imei_serial.required' => 'Vui lòng nhập mã IMEI / Serial.',
            'customer_phone.regex' => 'Số điện thoại phải đúng 10 chữ số.',
        ]);

        // 2. Chặn lỗi logic số tiền bằng 0 khi thay đổi trạng thái
        $validator->after(function ($validator) use ($request) {
            $status = $request->input('status');
            $subtotal = (float) $request->input('subtotal', 0);

            if (in_array($status, ['issued', 'paid']) && $subtotal <= 0) {
                $validator->errors()->add('subtotal', 'Số tiền tạm tính phải lớn hơn 0 khi phát hành hoặc thanh toán hóa đơn.');
            }
        });

        $data = $validator->validate();

        // 3. Tính toán lại thuế và tổng số tiền trên server
        $subtotal = (int) $data['subtotal'];
        $vatRate = (int) ($data['vat_rate'] ?? 0);
        $taxAmount = (int) round(($subtotal * $vatRate) / 100);
        $discountAmount = (int) ($data['discount_amount'] ?? 0);
        $totalAmount = (int) max(0, $subtotal + $taxAmount - $discountAmount);

        $updateData = [
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'imei_serial' => $data['imei_serial'] ?? null,
            'service_name' => $data['service_name'],
            'description' => $data['description'] ?? null,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'status' => $data['status'],
        ];

        // Nếu chuyển từ trạng thái nháp (draft) sang trạng thái khác và chưa có ngày phát hành, gán ngày hôm nay
        if ($data['status'] !== 'draft' && !$serviceInvoice->issued_date) {
            $updateData['issued_date'] = now()->toDateString();
        }

        $serviceInvoice->update($updateData);

        return redirect()->route('admin.service-invoices.index')->with('success', 'Cập nhật hóa đơn dịch vụ thành công.');
    }

    /**
     * Phương thức destroy(): Xóa hóa đơn dịch vụ khỏi hệ thống.
     * RÀNG BUỘC DỮ LIỆU: 
     *   - Trước khi xóa, tìm kiếm các phiếu sửa chữa đang liên kết với hóa đơn này thông qua mã hóa đơn (invoice_no).
     *   - Cập nhật các phiếu đó về trạng thái chưa xuất hóa đơn (gán invoice_no và invoiced_at về null) để tránh đứt gãy mối liên kết.
     */
    public function destroy(ServiceInvoice $serviceInvoice)
    {
        // Giải phóng liên kết ở các phiếu sửa chữa liên quan (nếu có)
        \App\Models\RepairTicket::where('invoice_no', $serviceInvoice->invoice_no)
            ->update([
                'invoice_no' => null,
                'invoiced_at' => null,
            ]);

        $serviceInvoice->delete();

        return redirect()->route('admin.service-invoices.index')->with('success', 'Xóa hóa đơn dịch vụ thành công.');
    }
}
