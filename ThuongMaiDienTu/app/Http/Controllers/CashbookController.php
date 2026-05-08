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
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $totalIncome  = Cashbook::ofType('Income')->sum('amount');
        $totalExpense = Cashbook::ofType('Expense')->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        return view('Cashbook.Cashbook', compact(
            'cashbooks', 'totalIncome', 'totalExpense', 'balance'
        ));
    }

    /** Lưu giao dịch mới */
    public function store(Request $request)
    {
        $request->validate([
            'type'        => 'required|in:Income,Expense',
            'amount'      => 'required|integer|min:1000',
            'description' => 'required|string|max:500',
        ], [
            'type.required'        => 'Vui lòng chọn loại giao dịch.',
            'amount.required'      => 'Vui lòng nhập số tiền.',
            'amount.min'           => 'Số tiền tối thiểu 1,000đ.',
            'description.required' => 'Vui lòng nhập nội dung.',
        ]);

        Cashbook::create($request->only('type', 'amount', 'description'));

        return redirect()->route('cashbooks.index')
            ->with('success', 'Đã thêm giao dịch thành công!');
    }

    /** Form sửa */
    public function edit(Cashbook $cashbook)
    {
        return view('Cashbook.edit', compact('cashbook'));
    }

    /** Cập nhật giao dịch */
    public function update(Request $request, Cashbook $cashbook)
    {
        $request->validate([
            'type'        => 'required|in:Income,Expense',
            'amount'      => 'required|integer|min:1000',
            'description' => 'required|string|max:500',
        ]);

        $cashbook->update($request->only('type', 'amount', 'description'));

        return redirect()->route('cashbooks.index')
            ->with('success', 'Đã cập nhật giao dịch!');
    }

    /** Xóa giao dịch */
    public function destroy(Cashbook $cashbook)
    {
        $cashbook->delete();

        return redirect()->route('cashbooks.index')
            ->with('success', 'Đã xóa giao dịch.');
    }
}
