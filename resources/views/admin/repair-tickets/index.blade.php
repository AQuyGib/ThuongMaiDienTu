<div>
    <h1>Danh sách phiếu sửa chữa</h1>

    <table>
        <thead>
            <tr>
                <th>Khách hàng</th>
                <th>Dịch vụ</th>
                <th>Phí</th>
                <th>Hóa đơn</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($repairTickets as $repairTicket)
                <tr>
                    <td>{{ $repairTicket->customer_name ?? '-' }}</td>
                    <td>{{ $repairTicket->service_name ?? '-' }}</td>
                    <td>{{ number_format($repairTicket->service_fee ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $repairTicket->invoice_no ?? 'Chưa xuất' }}</td>
                    <td>
                        <a href="{{ route('admin.repair-tickets.invoice.create', $repairTicket) }}">Xuất hóa đơn</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
