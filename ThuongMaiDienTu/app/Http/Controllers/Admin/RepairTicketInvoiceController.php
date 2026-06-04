<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RepairTicket;
use App\Models\ServiceInvoice;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RepairTicketInvoiceController extends Controller
{
    /**
     * Hiển thị danh sách các phiếu sửa chữa có trong hệ thống.
     * Hỗ trợ nạp kèm thông tin hóa đơn dịch vụ liên kết (serviceInvoice),
     * sắp xếp theo thứ tự phiếu mới nhất và phân trang 10 dòng/trang.
     */
    public function index(): View
    {
        $repairTickets = RepairTicket::with('serviceInvoice')->latest('ticket_id')->paginate(10);

        return view('admin.repair-tickets.index', compact('repairTickets'));
    }

    /**
     * Hiển thị giao diện tạo mới phiếu sửa chữa.
     * Truy vấn danh sách khách hàng (role_id = 3) và kỹ thuật viên (role_id thuộc [1, 2, 4])
     * để hiển thị trên các thẻ chọn (select box) trong form.
     */
    public function createTicket(): View
    {
        $customers = User::where('role_id', 3)->orderBy('full_name')->get();
        $technicians = User::whereIn('role_id', [1, 2, 4])->orderBy('full_name')->get();

        return view('admin.repair-tickets.create', compact('customers', 'technicians'));
    }

    public function storeTicket(Request $request): RedirectResponse
    {
        // Khởi tạo bộ xác thực (Validator) dữ liệu đầu vào cho phiếu sửa chữa mới
        $validator = Validator::make($request->all(), [
            'user_id' => ['nullable', 'exists:users,user_id'],
            'technician_id' => ['required', 'exists:users,user_id'],
            'imei_serial' => ['required', 'string', 'max:100'],
            'issue_desc' => ['required', 'string'],
            'schedule_date' => ['nullable', 'date', 'after_or_equal:today'],
            'estimated_cost' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:Received,Checking,Under_Repair,Waiting_Parts,Done'], // Cho phép trạng thái Checking (Kiểm tra & Báo giá) mới
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_source' => ['nullable', 'string', 'max:100'],
            'service_name' => ['nullable', 'string', 'max:255'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
        ], [
            'technician_id.required' => 'Vui lòng chọn kỹ thuật viên phụ trách.',
            'schedule_date.after_or_equal' => 'Ngày hẹn trả phải từ hôm nay trở đi.',
            'imei_serial.required' => 'Vui lòng nhập mã IMEI / Serial.',
            'issue_desc.required' => 'Vui lòng nhập mô tả lỗi.',
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'customer_phone.regex' => 'Số điện thoại phải đúng 10 chữ số.',
            'estimated_cost.required' => 'Vui lòng nhập chi phí dự kiến.',
            'customer_email.email' => 'Email không đúng định dạng.',
        ]);

        // Hàm callback xử lý các ràng buộc logic nghiệp vụ đặc thù sau khi kiểm tra xong dữ liệu cơ bản
        $validator->after(function ($validator) use ($request) {
            $status = $request->input('status');
            $serviceName = $request->input('service_name');
            $estimatedCost = (int) $request->input('estimated_cost', 0);

            // RÀNG BUỘC 1: Bắt buộc nhập Tên dịch vụ khi chuyển trạng thái sang Checking, Under_Repair, Waiting_Parts hoặc Done.
            if (in_array($status, ['Checking', 'Under_Repair', 'Waiting_Parts', 'Done']) && empty($serviceName)) {
                $validator->errors()->add('service_name', 'Vui lòng nhập tên dịch vụ khi chuyển sang trạng thái này.');
            }

            // RÀNG BUỘC 2: Khi đã có chi phí dự kiến lớn hơn 0, trạng thái sửa chữa không được giữ ở 'Received' (Đã tiếp nhận) mà phải là 'Checking' (Kiểm tra & Báo giá) trở lên.
            if ($estimatedCost > 0 && $status === 'Received') {
                $validator->errors()->add('status', 'Khi có chi phí dự kiến, vui lòng chuyển trạng thái sửa chữa sang "Kiểm tra & Báo giá" trở lên.');
            }

            // RÀNG BUỘC 3: Khi ở trạng thái 'Checking' (Kiểm tra & Báo giá), chi phí dự kiến không được để trống hoặc bằng 0.
            if ($status === 'Checking' && $estimatedCost <= 0) {
                $validator->errors()->add('estimated_cost', 'Vui lòng nhập chi phí dự kiến lớn hơn 0 khi ở trạng thái "Kiểm tra & Báo giá".');
            }

            // RÀNG BUỘC 4: Khi ở trạng thái 'Done' (Hoàn thành), phí dịch vụ thực tế không được để trống hoặc bằng 0.
            $serviceFee = (float) $request->input('service_fee', 0);
            if ($status === 'Done' && $serviceFee <= 0) {
                $validator->errors()->add('service_fee', 'Vui lòng nhập phí dịch vụ thực tế lớn hơn 0 khi ở trạng thái "Hoàn thành".');
            }
        });

        // Thực hiện xác thực và ném lỗi ValidationException nếu không thỏa mãn điều kiện
        $data = $validator->validate();

        $matchedUser = User::where('phone_number', $data['customer_phone'])->first();
        $data['user_id'] = $matchedUser ? $matchedUser->user_id : null;

        $data['technician_id'] = $data['technician_id'] ?? null;
        $data['schedule_date'] = $data['schedule_date'] ?? null;
        $data['customer_address'] = $data['customer_address'] ?? null;
        $data['customer_email'] = $data['customer_email'] ?? null;
        $data['customer_source'] = $data['customer_source'] ?? null;
        $data['service_name'] = $data['service_name'] ?? null;
        $data['service_fee'] = isset($data['service_fee']) && $data['service_fee'] !== '' ? $data['service_fee'] : 0;

        $ticket = RepairTicket::create($data);

        // Gửi thông báo cho khách hàng
        $user = null;
        if ($ticket->user_id) {
            $user = User::find($ticket->user_id);
        }
        
        if (!$user && $ticket->customer_phone) {
            $user = User::where('phone_number', $ticket->customer_phone)->first();
        }

        if (!$user && $ticket->customer_email) {
            $user = User::where('email', $ticket->customer_email)->first();
        }

        $statusMap = [
            'Received' => 'Đã tiếp nhận',
            'Checking' => 'Kiểm tra & Báo giá',
            'Under_Repair' => 'Đang sửa chữa',
            'Waiting_Parts' => 'Chờ linh kiện',
            'Done' => 'Đã hoàn thành',
        ];
        $statusText = $statusMap[$ticket->status] ?? $ticket->status;

        if ($user) {
            try {
                app(\App\Services\NotificationService::class)->createForUser($user, [
                    'type' => 'repair_ticket.created',
                    'title' => 'Đã tiếp nhận thiết bị sửa chữa',
                    'content' => "Yêu cầu sửa chữa thiết bị IMEI/Serial: {$ticket->imei_serial} đã được tiếp nhận. Trạng thái: {$statusText}. Mã phiếu: #RT-{$ticket->ticket_id}.",
                    'action_url' => url('/profile'),
                    'data' => [
                        'ticket_id' => $ticket->ticket_id,
                        'imei_serial' => $ticket->imei_serial,
                        'status' => $ticket->status,
                    ]
                ]);
            } catch (\Throwable $ne) {}
        } else {
            // Nếu khách hàng dùng số điện thoại/email khác và không có tài khoản
            if ($ticket->customer_email) {
                try {
                    \Illuminate\Support\Facades\Mail::raw(
                        "Kính gửi quý khách {$ticket->customer_name},\n\nYêu cầu sửa chữa thiết bị IMEI/Serial: {$ticket->imei_serial} của quý khách đã được tiếp nhận thành công.\nMã phiếu sửa chữa: #RT-{$ticket->ticket_id}.\nTrạng thái hiện tại: {$statusText}.\n\nCảm ơn quý khách đã sử dụng dịch vụ của chúng tôi!",
                        function ($message) use ($ticket) {
                            $message->to($ticket->customer_email)
                                    ->subject("[Hệ thống] Tiếp nhận sửa chữa thiết bị #RT-{$ticket->ticket_id}");
                        }
                    );
                } catch (\Throwable $me) {
                    \Illuminate\Support\Facades\Log::error("Lỗi gửi mail vãng lai: " . $me->getMessage());
                }
            }
        }

        return redirect()->route('admin.repair-tickets.index')->with('success', 'Đã tạo phiếu sửa chữa thành công.');
    }

    /**
     * Hiển thị giao diện chỉnh sửa một phiếu sửa chữa cụ thể.
     * Nạp thông tin phiếu sửa chữa hiện tại kèm danh sách khách hàng và kỹ thuật viên
     * để hiển thị thông tin cũ và cho phép cập nhật.
     */
    public function editTicket(RepairTicket $repairTicket): View
    {
        $customers = User::where('role_id', 3)->orderBy('full_name')->get();
        $technicians = User::whereIn('role_id', [1, 2, 4])->orderBy('full_name')->get();

        return view('admin.repair-tickets.edit', compact('repairTicket', 'customers', 'technicians'));
    }

    public function updateTicket(Request $request, RepairTicket $repairTicket): RedirectResponse
    {
        // Khởi tạo bộ xác thực (Validator) dữ liệu cho việc cập nhật phiếu sửa chữa hiện có
        $validator = Validator::make($request->all(), [
            'user_id' => ['nullable', 'exists:users,user_id'],
            'technician_id' => ['required', 'exists:users,user_id'],
            'imei_serial' => ['required', 'string', 'max:100'],
            'issue_desc' => ['required', 'string'],
            'schedule_date' => ['nullable', 'date', 'after_or_equal:today'],
            'estimated_cost' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:Received,Checking,Under_Repair,Waiting_Parts,Done'], // Cho phép trạng thái Checking (Kiểm tra & Báo giá) mới
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_source' => ['nullable', 'string', 'max:100'],
            'service_name' => ['nullable', 'string', 'max:255'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
        ], [
            'technician_id.required' => 'Vui lòng chọn kỹ thuật viên phụ trách.',
            'schedule_date.after_or_equal' => 'Ngày hẹn trả phải từ hôm nay trở đi.',
            'imei_serial.required' => 'Vui lòng nhập mã IMEI / Serial.',
            'issue_desc.required' => 'Vui lòng nhập mô tả lỗi.',
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'customer_phone.regex' => 'Số điện thoại phải đúng 10 chữ số.',
            'estimated_cost.required' => 'Vui lòng nhập chi phí dự kiến.',
            'customer_email.email' => 'Email không đúng định dạng.',
        ]);

        // Hàm callback xử lý các ràng buộc logic nghiệp vụ đặc thù sau khi kiểm tra xong dữ liệu cơ bản
        $validator->after(function ($validator) use ($request) {
            $status = $request->input('status');
            $serviceName = $request->input('service_name');
            $estimatedCost = (int) $request->input('estimated_cost', 0);

            // RÀNG BUỘC 1: Bắt buộc nhập Tên dịch vụ khi chuyển trạng thái sang Checking, Under_Repair, Waiting_Parts hoặc Done.
            if (in_array($status, ['Checking', 'Under_Repair', 'Waiting_Parts', 'Done']) && empty($serviceName)) {
                $validator->errors()->add('service_name', 'Vui lòng nhập tên dịch vụ khi chuyển sang trạng thái này.');
            }

            // RÀNG BUỘC 2: Khi đã có chi phí dự kiến lớn hơn 0, trạng thái sửa chữa không được giữ ở 'Received' (Đã tiếp nhận) mà phải là 'Checking' (Kiểm tra & Báo giá) trở lên.
            if ($estimatedCost > 0 && $status === 'Received') {
                $validator->errors()->add('status', 'Khi có chi phí dự kiến, vui lòng chuyển trạng thái sửa chữa sang "Kiểm tra & Báo giá" trở lên.');
            }

            // RÀNG BUỘC 3: Khi ở trạng thái 'Checking' (Kiểm tra & Báo giá), chi phí dự kiến không được để trống hoặc bằng 0.
            if ($status === 'Checking' && $estimatedCost <= 0) {
                $validator->errors()->add('estimated_cost', 'Vui lòng nhập chi phí dự kiến lớn hơn 0 khi ở trạng thái "Kiểm tra & Báo giá".');
            }

            // RÀNG BUỘC 4: Khi ở trạng thái 'Done' (Hoàn thành), phí dịch vụ thực tế không được để trống hoặc bằng 0.
            $serviceFee = (float) $request->input('service_fee', 0);
            if ($status === 'Done' && $serviceFee <= 0) {
                $validator->errors()->add('service_fee', 'Vui lòng nhập phí dịch vụ thực tế lớn hơn 0 khi ở trạng thái "Hoàn thành".');
            }
        });

        // Thực hiện xác thực và ném lỗi ValidationException nếu không thỏa mãn điều kiện
        $data = $validator->validate();

        $matchedUser = User::where('phone_number', $data['customer_phone'])->first();
        $data['user_id'] = $matchedUser ? $matchedUser->user_id : ($repairTicket->user_id ?? null);

        $data['technician_id'] = $data['technician_id'] ?? null;
        $data['schedule_date'] = $data['schedule_date'] ?? null;
        $data['customer_address'] = $data['customer_address'] ?? null;
        $data['customer_email'] = $data['customer_email'] ?? null;
        $data['customer_source'] = $data['customer_source'] ?? null;
        $data['service_name'] = $data['service_name'] ?? null;
        $data['service_fee'] = isset($data['service_fee']) && $data['service_fee'] !== '' ? $data['service_fee'] : 0;

        $oldStatus = $repairTicket->status;
        $repairTicket->update($data);

        // Gửi thông báo cho khách hàng nếu thay đổi trạng thái
        $user = null;
        if ($repairTicket->user_id) {
            $user = User::find($repairTicket->user_id);
        }
        
        if (!$user && $repairTicket->customer_phone) {
            $user = User::where('phone_number', $repairTicket->customer_phone)->first();
        }

        if (!$user && $repairTicket->customer_email) {
            $user = User::where('email', $repairTicket->customer_email)->first();
        }

        if ($oldStatus !== $repairTicket->status) {
            $statusMap = [
                'Received' => 'Đã tiếp nhận',
                'Checking' => 'Kiểm tra & Báo giá',
                'Under_Repair' => 'Đang sửa chữa',
                'Waiting_Parts' => 'Chờ linh kiện',
                'Done' => 'Đã hoàn thành',
            ];
            $statusText = $statusMap[$repairTicket->status] ?? $repairTicket->status;

            if ($user) {
                try {
                    app(\App\Services\NotificationService::class)->createForUser($user, [
                        'type' => 'repair_ticket.status_updated',
                        'title' => 'Trạng thái sửa chữa cập nhật',
                        'content' => "Phiếu sửa chữa #RT-{$repairTicket->ticket_id} cho thiết bị IMEI: {$repairTicket->imei_serial} đã chuyển sang trạng thái: {$statusText}.",
                        'action_url' => url('/profile'),
                        'data' => [
                            'ticket_id' => $repairTicket->ticket_id,
                            'imei_serial' => $repairTicket->imei_serial,
                            'old_status' => $oldStatus,
                            'new_status' => $repairTicket->status,
                        ]
                    ]);
                } catch (\Throwable $ne) {}
            } else {
                // Khách hàng không có tài khoản nhưng có điền email
                if ($repairTicket->customer_email) {
                    try {
                        \Illuminate\Support\Facades\Mail::raw(
                            "Kính gửi quý khách {$repairTicket->customer_name},\n\nTiến độ sửa chữa thiết bị IMEI/Serial: {$repairTicket->imei_serial} (Mã phiếu #RT-{$repairTicket->ticket_id}) đã có cập nhật mới.\nTrạng thái mới: {$statusText}.\n\nCảm ơn quý khách!",
                            function ($message) use ($repairTicket) {
                                $message->to($repairTicket->customer_email)
                                        ->subject("[Hệ thống] Cập nhật trạng thái sửa chữa #RT-{$repairTicket->ticket_id}");
                            }
                        );
                    } catch (\Throwable $me) {
                        \Illuminate\Support\Facades\Log::error("Lỗi gửi mail cập nhật tiến độ vãng lai: " . $me->getMessage());
                    }
                }
            }
        }

        return redirect()->route('admin.repair-tickets.index')->with('success', 'Đã cập nhật phiếu sửa chữa thành công.');
    }

    /**
     * Xóa một phiếu sửa chữa ra khỏi hệ thống.
     */
    public function destroyTicket(RepairTicket $repairTicket): RedirectResponse
    {
        $repairTicket->delete();

        return redirect()->route('admin.repair-tickets.index')->with('success', 'Đã xóa phiếu sửa chữa thành công.');
    }

    /**
     * Phương thức create(): Chuẩn bị dữ liệu và hiển thị giao diện xuất hóa đơn dịch vụ từ phiếu sửa chữa.
     * RÀNG BUỘC BẢO MẬT:
     *   - Chỉ cho phép xuất hóa đơn nếu phiếu sửa chữa đã ở trạng thái hoàn thành ('Done').
     *   - Tự động điền trước các thông tin khách hàng, số điện thoại, email, IMEI, tên dịch vụ và chi phí dịch vụ thực tế từ phiếu sửa chữa sang hóa đơn.
     */
    public function create(RepairTicket $repairTicket): View|RedirectResponse
    {
        if ($repairTicket->status !== 'Done') {
            return redirect()->route('admin.repair-tickets.index')
                ->with('error', 'Phiếu sửa chữa chưa hoàn thành. Chỉ xuất hóa đơn khi trạng thái là Đã hoàn thành (Done).');
        }

        return view('admin.service-invoices.create', [
            'repairTicket' => $repairTicket,
            'prefill' => [
                'invoice_no' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'customer_name' => $repairTicket->customer_name,
                'customer_phone' => $repairTicket->customer_phone,
                'customer_email' => $repairTicket->customer_email,
                'imei_serial' => $repairTicket->imei_serial,
                'service_name' => $repairTicket->service_name,
                'subtotal' => (float) $repairTicket->service_fee,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
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

        $validator->after(function ($validator) use ($request) {
            $status = $request->input('status');
            $subtotal = (float) $request->input('subtotal', 0);

            if (in_array($status, ['issued', 'paid']) && $subtotal <= 0) {
                $validator->errors()->add('subtotal', 'Số tiền tạm tính phải lớn hơn 0 khi phát hành hoặc thanh toán hóa đơn.');
            }
        });

        $data = $validator->validate();

        $subtotal = (int) $data['subtotal'];
        $vatRate = (int) ($data['vat_rate'] ?? 0);
        $taxAmount = (int) round(($subtotal * $vatRate) / 100);
        $discountAmount = (int) ($data['discount_amount'] ?? 0);
        $totalAmount = (int) max(0, $subtotal + $taxAmount - $discountAmount);

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
            'issued_date' => $data['status'] === 'draft' ? null : now()->toDateString(),
            'created_by' => $request->user()?->id,
        ]);

        // Gửi thông báo cho khách hàng
        $user = null;
        if ($request->filled('repair_ticket_id')) {
            $repairTicket = RepairTicket::find($request->integer('repair_ticket_id'));
            if ($repairTicket) {
                if ($repairTicket->user_id) {
                    $user = User::find($repairTicket->user_id);
                } elseif ($repairTicket->customer_phone) {
                    $user = User::where('phone_number', $repairTicket->customer_phone)->first();
                }
                
                if (! $repairTicket->invoice_no) {
                    $repairTicket->update([
                        'invoice_no' => $invoice->invoice_no,
                        'invoiced_at' => now(),
                    ]);
                }
            }
        }

        if (!$user && $invoice->customer_phone) {
            $user = User::where('phone_number', $invoice->customer_phone)->first();
        }

        if (!$user && $invoice->customer_email) {
            $user = User::where('email', $invoice->customer_email)->first();
        }

        $statusMap = [
            'draft' => 'Bản nháp',
            'issued' => 'Đã phát hành',
            'paid' => 'Đã thanh toán',
            'cancelled' => 'Đã hủy',
        ];
        $statusText = $statusMap[$invoice->status] ?? $invoice->status;
        $formattedAmount = number_format($invoice->total_amount);

        if ($user) {
            try {
                app(\App\Services\NotificationService::class)->createForUser($user, [
                    'type' => 'service_invoice.created',
                    'title' => 'Hóa đơn dịch vụ mới được xuất',
                    'content' => "Hóa đơn dịch vụ #{$invoice->invoice_no} trị giá {$formattedAmount}đ cho dịch vụ '{$invoice->service_name}' đã được tạo ở trạng thái: {$statusText}.",
                    'action_url' => url('/profile'),
                    'data' => [
                        'invoice_id' => $invoice->id,
                        'invoice_no' => $invoice->invoice_no,
                        'total_amount' => $invoice->total_amount,
                        'status' => $invoice->status,
                    ]
                ]);
            } catch (\Throwable $ne) {}
        } else {
            // Gửi email hóa đơn trực tiếp cho khách hàng vãng lai
            if ($invoice->customer_email) {
                try {
                    \Illuminate\Support\Facades\Mail::raw(
                        "Kính gửi quý khách {$invoice->customer_name},\n\nHóa đơn dịch vụ #{$invoice->invoice_no} trị giá {$formattedAmount}đ cho dịch vụ '{$invoice->service_name}' đã được tạo thành công.\nTrạng thái hiện tại: {$statusText}.\n\nCảm ơn quý khách đã tin tưởng và sử dụng dịch vụ của chúng tôi!",
                        function ($message) use ($invoice) {
                            $message->to($invoice->customer_email)
                                    ->subject("[Hệ thống] Hóa đơn dịch vụ mới #{$invoice->invoice_no}");
                        }
                    );
                } catch (\Throwable $me) {
                    \Illuminate\Support\Facades\Log::error("Lỗi gửi email hóa đơn vãng lai: " . $me->getMessage());
                }
            }
        }

        return redirect()->route('admin.service-invoices.show', $invoice)->with('success', 'Đã xuất hóa đơn dịch vụ thành công.');
    }

    public function searchByPhone(Request $request): JsonResponse
    {
        $phone = $request->query('phone');
        if (! $phone) {
            return response()->json(null);
        }

        $ticket = RepairTicket::where('customer_phone', $phone)
            ->whereNotNull('customer_name')
            ->latest('ticket_id')
            ->first();

        if ($ticket) {
            return response()->json([
                'customer_name' => $ticket->customer_name,
                'customer_address' => $ticket->customer_address,
                'customer_email' => $ticket->customer_email,
                'customer_source' => $ticket->customer_source,
            ]);
        }

        $user = User::where('phone', $phone)->first();
        if ($user) {
            return response()->json([
                'customer_name' => $user->full_name,
                'customer_address' => $user->address,
                'customer_email' => $user->email,
                'customer_source' => null,
            ]);
        }

        return response()->json(null);
    }
}
