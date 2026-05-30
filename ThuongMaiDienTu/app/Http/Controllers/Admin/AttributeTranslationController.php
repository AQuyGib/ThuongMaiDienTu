<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use Illuminate\Http\Request;

class AttributeTranslationController extends Controller
{
    public function edit(Attribute $attribute)
    {
        $translation = AttributeTranslation::query()->firstOrNew([
            'attribute_id' => $attribute->attribute_id,
            'locale' => 'en',
        ]);

        return view('admin.attributes.translation-edit', compact('attribute', 'translation'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        AttributeTranslation::updateOrCreate(
            [
                'attribute_id' => $attribute->attribute_id,
                'locale' => 'en',
            ],
            $data + [
                'attribute_id' => $attribute->attribute_id,
                'locale' => 'en',
            ]
        );

        return back()->with('success', 'Đã lưu bản dịch EN thủ công cho thuộc tính.');
    }
}
