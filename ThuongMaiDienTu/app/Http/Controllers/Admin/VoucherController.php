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
            'code'             => ['required', 'string', 'min:6', 'max:20', 'regex:/^[A-Za-z0-9]+$/', 'unique:coupons_flash_sales,code'],
            'discount_type'    => ['required', Rule::in(['fixed', 'percent'])],
            'discount_fixed'   => ['nullable', 'integer', 'min:100000', 'max:99999999'],
            'discount_percent' => ['nullable', 'integer', 'min:10', 'max:100'],
            'start_time'       => ['nullable', 'date'],
            'end_time'         => ['nullable', 'date', 'after_or_equal:start_time'],
            'usage_limit'      => ['nullable', 'integer', 'min:1', 'max:100'],
        ], [
            'code.regex'            => 'Mã voucher chỉ được chứa chữ cái (a-z, A-Z) và số (0-9), không dùng ký tự đặc biệt.',
            'code.min'              => 'Mã voucher phải có ít nhất 6 ký tự.',
            'code.max'              => 'Mã voucher tối đa 20 ký tự.',
            'discount_fixed.min'    => 'Giá giảm theo tiền phải từ 100.000đ (6 chữ số) trở lên.',
            'discount_fixed.max'    => 'Giá giảm theo tiền tối đa 99.999.999đ (8 chữ số).',
            'discount_percent.min'  => 'Phần trăm giảm phải từ 10% trở lên.',
            'discount_percent.max'  => 'Phần trăm giảm tối đa 100%.',
            'usage_limit.min'       => 'Giới hạn lượt dùng phải từ 1 trở lên.',
            'usage_limit.max'       => 'Giới hạn lượt dùng tối đa là 100 lần.',
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
            'promo_type'    => 'Coupon',
            'code'          => strtoupper($validated['code']),
            'discount_type' => $validated['discount_type'],
            'discount_val'  => $discountVal,
            'start_time'    => $validated['start_time'] ?? null,
            'end_time'      => $validated['end_time'] ?? null,
            'usage_limit'   => $validated['usage_limit'] ?? null,
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
                'required', 'string', 'min:6', 'max:20', 'regex:/^[A-Za-z0-9]+$/',
                Rule::unique('coupons_flash_sales', 'code')->ignore($voucher->promo_id, 'promo_id'),
            ],
            'discount_type'    => ['required', Rule::in(['fixed', 'percent'])],
            'discount_fixed'   => ['nullable', 'integer', 'min:100000', 'max:99999999'],
            'discount_percent' => ['nullable', 'integer', 'min:10', 'max:100'],
            'start_time'       => ['nullable', 'date'],
            'end_time'         => ['nullable', 'date', 'after_or_equal:start_time'],
            'usage_limit'      => ['nullable', 'integer', 'min:1', 'max:100'],
        ], [
            'code.regex'            => 'Mã voucher chỉ được chứa chữ cái (a-z, A-Z) và số (0-9), không dùng ký tự đặc biệt.',
            'code.min'              => 'Mã voucher phải có ít nhất 6 ký tự.',
            'code.max'              => 'Mã voucher tối đa 20 ký tự.',
            'discount_fixed.min'    => 'Giá giảm theo tiền phải từ 100.000đ (6 chữ số) trở lên.',
            'discount_fixed.max'    => 'Giá giảm theo tiền tối đa 99.999.999đ (8 chữ số).',
            'discount_percent.min'  => 'Phần trăm giảm phải từ 10% trở lên.',
            'discount_percent.max'  => 'Phần trăm giảm tối đa 100%.',
            'usage_limit.min'       => 'Giới hạn lượt dùng phải từ 1 trở lên.',
            'usage_limit.max'       => 'Giới hạn lượt dùng tối đa là 100 lần.',
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
            'code'          => strtoupper($validated['code']),
            'discount_type' => $validated['discount_type'],
            'discount_val'  => $discountVal,
            'start_time'    => $validated['start_time'] ?? null,
            'end_time'      => $validated['end_time'] ?? null,
            'usage_limit'   => $validated['usage_limit'] ?? null,
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
