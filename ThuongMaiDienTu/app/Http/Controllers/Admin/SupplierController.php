<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Hiển thị danh sách nhà cung cấp (Trang Quản lý NCC)
     */
    public function index()
    {
        // Lấy danh sách NCC có phân trang, đếm số phiếu nhập
        $suppliers = Supplier::withCount('purchaseOrders')
            ->orderBy('supplier_id', 'desc')
            ->paginate(10);

        // Thống kê
        $totalSuppliers = Supplier::count();

        return view('admin.suppliers.Supplier', compact(
            'suppliers',
            'totalSuppliers'
        ));
    }

    /**
     * Thêm nhà cung cấp mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên nhà cung cấp.',
            'name.max'      => 'Tên nhà cung cấp không được vượt quá 100 ký tự.',
            'email.email'   => 'Email không đúng định dạng.',
        ]);

        Supplier::create([
            'name'    => $request->name,
            'phone'   => $request->phone ?: null,
            'email'   => $request->email ?: null,
            'address' => $request->address ?: null,
        ]);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Thêm nhà cung cấp "' . $request->name . '" thành công!');
    }

    /**
     * Cập nhật nhà cung cấp
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'version' => 'required|integer',
        ], [
            'name.required' => 'Vui lòng nhập tên nhà cung cấp.',
            'name.max'      => 'Tên nhà cung cấp không được vượt quá 100 ký tự.',
            'email.email'   => 'Email không đúng định dạng.',
            'version.required' => 'Thiếu thông tin phiên bản nhà cung cấp.',
            'version.integer' => 'Phiên bản nhà cung cấp không hợp lệ.',
        ]);

        $supplier = Supplier::findOrFail($id);

        if ((int)$supplier->version !== (int)$request->version) {
            return redirect()->route('admin.suppliers.index')
                ->with('error', 'Nhà cung cấp "' . $supplier->name . '" đã bị cập nhật bởi một người quản trị khác. Vui lòng tải lại trang.');
        }

        $supplier->update([
            'name'    => $request->name,
            'phone'   => $request->phone ?: null,
            'email'   => $request->email ?: null,
            'address' => $request->address ?: null,
            'version' => $supplier->version + 1,
        ]);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Cập nhật nhà cung cấp "' . $request->name . '" thành công!');
    }

    /**
     * Xóa nhà cung cấp
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $name = $supplier->name;

        // Kiểm tra xem NCC có phiếu nhập không
        if ($supplier->purchaseOrders()->count() > 0) {
            return redirect()->route('admin.suppliers.index')
                ->with('error', 'Không thể xóa nhà cung cấp "' . $name . '" vì đang có phiếu nhập kho liên kết!');
        }

        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Xóa nhà cung cấp "' . $name . '" thành công!');
    }
}
