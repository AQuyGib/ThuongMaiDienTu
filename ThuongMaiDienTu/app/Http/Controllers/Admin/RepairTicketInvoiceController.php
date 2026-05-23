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
    public function index(): View
    {
        $repairTickets = RepairTicket::with('serviceInvoice')->latest('ticket_id')->paginate(10);

        return view('admin.repair-tickets.index', compact('repairTickets'));
    }

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
            'imei_serial' => ['required', 'string', 'max:100', 'unique:repair_tickets,imei_serial'],
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
            'imei_serial.unique' => 'Mã IMEI / Serial này đã tồn tại trong hệ thống.',
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

        RepairTicket::create($data);

        return redirect()->route('admin.repair-tickets.index')->with('success', 'Đã tạo phiếu sửa chữa thành công.');
    }

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
            'imei_serial' => ['required', 'string', 'max:100', 'unique:repair_tickets,imei_serial,'.$repairTicket->ticket_id.',ticket_id'],
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
            'imei_serial.unique' => 'Mã IMEI / Serial này đã tồn tại trong hệ thống.',
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

        $repairTicket->update($data);

        return redirect()->route('admin.repair-tickets.index')->with('success', 'Đã cập nhật phiếu sửa chữa thành công.');
    }

    public function destroyTicket(RepairTicket $repairTicket): RedirectResponse
    {
        $repairTicket->delete();

        return redirect()->route('admin.repair-tickets.index')->with('success', 'Đã xóa phiếu sửa chữa thành công.');
    }

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
            'customer_phone' => ['nullable', 'string', 'max:50'],
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

        if ($request->filled('repair_ticket_id')) {
            $repairTicket = RepairTicket::find($request->integer('repair_ticket_id'));
            if ($repairTicket && ! $repairTicket->invoice_no) {
                $repairTicket->update([
                    'invoice_no' => $invoice->invoice_no,
                    'invoiced_at' => now(),
                ]);
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
