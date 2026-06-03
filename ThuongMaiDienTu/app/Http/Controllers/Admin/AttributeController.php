<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttributeRequest;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::query()
            ->withTranslation()
            ->latest('attribute_id')
            ->paginate(20);

        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(StoreAttributeRequest $request)
    {
        $attribute = Attribute::create($request->validated() + [
            'slug' => $request->filled('slug') ? $request->slug : Str::slug($request->name),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.attributes.edit', $attribute->attribute_id)
            ->with('success', 'Đã tạo thuộc tính và tự động đồng bộ bản dịch.');
    }

    public function edit(Attribute $attribute)
    {
        $attribute->loadMissing('translations');

        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(StoreAttributeRequest $request, Attribute $attribute)
    {
        $attribute->update($request->validated() + [
            'slug' => $request->filled('slug') ? $request->slug : Str::slug($request->name),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Đã cập nhật thuộc tính và tự động đồng bộ bản dịch.');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Đã xóa thuộc tính.');
    }
}
