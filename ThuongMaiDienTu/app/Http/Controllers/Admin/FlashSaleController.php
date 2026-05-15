<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FlashSaleController extends Controller
{
    public function index(Request $request)
    {
        $flashSales = FlashSale::withCount('products')
            ->orderByDesc('flash_sale_id')
            ->paginate(10);

        $products = Product::orderBy('name')->get();
        $editingFlashSale = $request->filled('edit')
            ? FlashSale::with('products.product')->find($request->integer('edit'))
            : null;

        return view('admin.flash-sales.index', compact('flashSales', 'products', 'editingFlashSale'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'is_active' => ['nullable', Rule::in([0, 1, '0', '1'])],
            'description' => 'nullable|string|max:1000',
        ]);

        FlashSale::create([
            'name' => $validated['name'],
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'],
            'is_active' => $request->boolean('is_active'),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.flash-sales.index')->with('success', 'Tạo Flash Sale thành công!');
    }

    public function update(Request $request, FlashSale $flash_sale)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'is_active' => ['nullable', Rule::in([0, 1, '0', '1'])],
            'description' => 'nullable|string|max:1000',
        ]);

        $flash_sale->update([
            'name' => $validated['name'],
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'],
            'is_active' => $request->boolean('is_active'),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.flash-sales.index')->with('success', 'Cập nhật Flash Sale thành công!');
    }

    public function destroy(FlashSale $flash_sale)
    {
        $flash_sale->delete();

        return redirect()->route('admin.flash-sales.index')->with('success', 'Xóa Flash Sale thành công!');
    }
}
