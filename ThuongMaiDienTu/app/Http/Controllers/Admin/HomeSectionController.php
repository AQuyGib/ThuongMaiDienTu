<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeSectionController extends Controller
{
    public function index()
    {
        $sections = HomeSection::orderBy('order', 'asc')->get();
        return view('admin.home-sections.index', compact('sections'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return view('admin.home-sections.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:latest,manual,category',
            'limit' => 'required|integer|min:1|max:20',
            'sidebar_banner_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['title', 'type', 'category_id', 'limit', 'sidebar_link', 'order', 'status']);
        $data['status'] = $request->has('status');

        if ($request->hasFile('sidebar_banner_file')) {
            $path = $request->file('sidebar_banner_file')->store('banners', 'public');
            $data['sidebar_banner'] = Storage::url($path);
        } elseif ($request->sidebar_banner_url) {
            $data['sidebar_banner'] = $request->sidebar_banner_url;
        }

        $section = HomeSection::create($data);

        if ($request->type === 'manual' && $request->product_ids) {
            $productIds = explode(',', $request->product_ids);
            foreach ($productIds as $index => $productId) {
                $section->products()->attach($productId, ['order' => $index]);
            }
        }

        return redirect()->route('admin.home-sections.index')->with('success', 'Tạo khung sản phẩm thành công!');
    }

    public function edit($id)
    {
        $section = HomeSection::with('products')->findOrFail($id);
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return view('admin.home-sections.edit', compact('section', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:latest,manual,category',
            'limit' => 'required|integer|min:1|max:20',
        ]);

        $section = HomeSection::findOrFail($id);
        $data = $request->only(['title', 'type', 'category_id', 'limit', 'sidebar_link', 'order', 'status']);
        $data['status'] = $request->has('status');

        if ($request->hasFile('sidebar_banner_file')) {
            $path = $request->file('sidebar_banner_file')->store('banners', 'public');
            $data['sidebar_banner'] = Storage::url($path);
        } elseif ($request->sidebar_banner_url) {
            $data['sidebar_banner'] = $request->sidebar_banner_url;
        }

        $section->update($data);

        if ($request->type === 'manual') {
            $section->products()->detach();
            if ($request->product_ids) {
                $productIds = explode(',', $request->product_ids);
                foreach ($productIds as $index => $productId) {
                    $section->products()->attach($productId, ['order' => $index]);
                }
            }
        }

        return redirect()->route('admin.home-sections.index')->with('success', 'Cập nhật khung sản phẩm thành công!');
    }

    public function destroy($id)
    {
        $section = HomeSection::findOrFail($id);
        $section->delete();
        return redirect()->route('admin.home-sections.index')->with('success', 'Xóa khung sản phẩm thành công!');
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('q');
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->select('product_id', 'name', 'thumbnail', 'base_price')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function reorder(Request $request)
    {
        $orders = $request->orders; // Array of [id => order]
        foreach ($orders as $id => $order) {
            HomeSection::where('id', $id)->update(['order' => $order]);
        }
        return response()->json(['status' => 'success']);
    }
}
