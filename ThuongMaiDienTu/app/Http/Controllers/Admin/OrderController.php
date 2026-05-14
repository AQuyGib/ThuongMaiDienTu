<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'details.inventoryItem.variant.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_id', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('phone_number', 'like', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->orderByDesc('order_id')->paginate(15)->appends($request->query());

        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'details.inventoryItem.variant.product'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Shipping,Delivered,Cancelled',
        ]);

        $order = Order::with('details.inventoryItem.variant')->findOrFail($id);
        $previousStatus = $order->status;
        $currentStatus = $request->status;

        DB::transaction(function () use ($order, $currentStatus) {
            $order->status = $currentStatus;
            $order->save();
        });

        return redirect()->route('admin.orders.show', $order->order_id)
            ->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }
}
