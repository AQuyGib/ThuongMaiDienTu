<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use Illuminate\Http\Request;

class CashbookController extends Controller
{
    /** Danh sách giao dịch (tìm kiếm + lọc loại + phân trang) */
    public function index(Request $request)
    {
        $cashbooks = Cashbook::query()
            ->search($request->input('search'))
            ->when($request->filled('type'), fn ($q) => $q->ofType($request->type))
            ->orderByRaw('reference_id IS NULL, reference_id ASC')
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $totalIncome  = Cashbook::ofType('Income')->sum('amount');
        $totalExpense = Cashbook::ofType('Expense')->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        // Dữ liệu biểu đồ 7 ngày gần nhất
        $chartData = [
            'labels' => [],
            'income' => [],
            'expense' => []
        ];

        for ($i = 6; $i >= 0; $i--) {
            $currentDay = \Carbon\Carbon::now('Asia/Ho_Chi_Minh')->subDays($i);
            $date = $currentDay->format('d/m');
            $fullDate = $currentDay->toDateString();
            
            $chartData['labels'][] = $date;
            $chartData['income'][] = Cashbook::ofType('Income')
                ->whereDate('created_at', $fullDate)
                ->sum('amount');
            $chartData['expense'][] = Cashbook::ofType('Expense')
                ->whereDate('created_at', $fullDate)
                ->sum('amount');
        }

        return view('Cashbook.Cashbook', compact(
            'cashbooks', 'totalIncome', 'totalExpense', 'balance', 'chartData'
        ));
    }

    /** Lưu giao dịch mới */
    public function store(Request $request)
    {
        $request->validate([
            'type'         => 'required|in:Income,Expense',
            'amount'       => 'required|integer|min:1000',
            'description'  => 'required|string|max:500',
            'reference_id' => 'nullable|integer',
            'created_at'   => 'nullable|date',
        ], [
            'type.required'        => 'Vui lòng chọn loại giao dịch.',
            'amount.required'      => 'Vui lòng nhập số tiền.',
            'amount.min'           => 'Số tiền tối thiểu 1,000đ.',
            'description.required' => 'Vui lòng nhập nội dung.',
        ]);

        $data = $request->only('type', 'amount', 'description', 'reference_id');
        if ($request->filled('created_at')) {
            $data['created_at'] = $request->created_at;
        }

        Cashbook::create($data);

        return redirect()->route('admin.cashbooks.index')
            ->with('success', 'Đã thêm giao dịch thành công!');
    }

    /** Form sửa */
    public function edit(Cashbook $cashbook)
    {
        return view('Cashbook.edit', compact('cashbook'));
    }

    /** Cập nhật giao dịch */
    public function update(Request $request, $id)
    {
        $cashbook = Cashbook::findOrFail($id);
        $request->validate([
            'type'         => 'required|in:Income,Expense',
            'amount'       => 'required|integer|min:1000',
            'description'  => 'required|string|max:500',
            'reference_id' => 'nullable|integer',
            'created_at'   => 'nullable|date',
        ]);

        $data = $request->only('type', 'amount', 'description', 'reference_id');
        if ($request->filled('created_at')) {
            $data['created_at'] = $request->created_at;
        }

        $cashbook->update($data);

        return redirect()->route('admin.cashbooks.index')
            ->with('success', 'Đã cập nhật giao dịch!');
    }

    /** Xóa giao dịch */
    public function destroy($id)
    {
        $cashbook = Cashbook::findOrFail($id);
        $cashbook->delete();

        return redirect()->route('admin.cashbooks.index')
            ->with('success', 'Đã xóa giao dịch.');
    }
    /** Xóa nhiều giao dịch cùng lúc */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất một giao dịch để xóa.');
        }

        Cashbook::whereIn('cashbook_id', $ids)->delete();

        return redirect()->route('admin.cashbooks.index')
            ->with('success', 'Đã xóa ' . count($ids) . ' giao dịch được chọn.');
    }
}
