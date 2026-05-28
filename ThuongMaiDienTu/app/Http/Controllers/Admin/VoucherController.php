<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponFlashSale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $vouchers = CouponFlashSale::query()
            ->where('promo_type', 'Coupon')
            ->orderByDesc('promo_id')
            ->paginate(10);

        $editingVoucher = $request->filled('edit')
            ? CouponFlashSale::query()
                ->where('promo_type', 'Coupon')
                ->find($request->integer('edit'))
            : null;

        return view('frontend.cart.voucher', compact('vouchers', 'editingVoucher'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', 'unique:coupons_flash_sales,code'],
            'discount_type' => ['required', Rule::in(['fixed', 'percent'])],
            'discount_fixed' => ['nullable', 'integer', 'min:1'],
            'discount_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
            'start_time' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
        ], [
            'code.regex' => 'Mã voucher chỉ được chứa chữ in hoa, số, dấu gạch dưới hoặc gạch ngang.',
        ]);

        $discountVal = $validated['discount_type'] === 'percent'
            ? (int) ($validated['discount_percent'] ?? 0)
            : (int) ($validated['discount_fixed'] ?? 0);

        if ($discountVal < 1) {
            return back()->withInput()->withErrors([
                $validated['discount_type'] === 'percent' ? 'discount_percent' : 'discount_fixed'
                    => 'Vui lòng nhập giá trị giảm hợp lệ.',
            ]);
        }

        CouponFlashSale::create([
            'promo_type' => 'Coupon',
            'code' => strtoupper($validated['code']),
            'discount_type' => $validated['discount_type'],
            'discount_val' => $discountVal,
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
        ]);

        return redirect()->route('admin.vouchers.index')->with('success', 'Tạo voucher thành công.');
    }

    public function update(Request $request, CouponFlashSale $voucher)
    {
        if ($voucher->promo_type !== 'Coupon') {
            abort(404);
        }

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('coupons_flash_sales', 'code')->ignore($voucher->promo_id, 'promo_id'),
            ],
            'discount_type' => ['required', Rule::in(['fixed', 'percent'])],
            'discount_fixed' => ['nullable', 'integer', 'min:1'],
            'discount_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
            'start_time' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
        ], [
            'code.regex' => 'Mã voucher chỉ được chứa chữ in hoa, số, dấu gạch dưới hoặc gạch ngang.',
        ]);

        $discountVal = $validated['discount_type'] === 'percent'
            ? (int) ($validated['discount_percent'] ?? 0)
            : (int) ($validated['discount_fixed'] ?? 0);

        if ($discountVal < 1) {
            return back()->withInput()->withErrors([
                $validated['discount_type'] === 'percent' ? 'discount_percent' : 'discount_fixed'
                    => 'Vui lòng nhập giá trị giảm hợp lệ.',
            ]);
        }

        $voucher->update([
            'code' => strtoupper($validated['code']),
            'discount_type' => $validated['discount_type'],
            'discount_val' => $discountVal,
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
        ]);

        return redirect()->route('admin.vouchers.index')->with('success', 'Cập nhật voucher thành công.');
    }

    public function destroy(CouponFlashSale $voucher)
    {
        if ($voucher->promo_type !== 'Coupon') {
            abort(404);
        }

        $voucher->delete();

        return redirect()->route('admin.vouchers.index')->with('success', 'Xóa voucher thành công.');
    }
}
