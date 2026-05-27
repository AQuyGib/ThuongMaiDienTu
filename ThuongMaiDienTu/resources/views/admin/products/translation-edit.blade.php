@extends('admin.layout')

@section('title', 'Sửa bản dịch sản phẩm')

@section('content')
<div class="container-fluid py-4">
    @include('admin.translations.partials.two-column-form', [
        'title' => 'Chỉnh sửa bản dịch EN cho sản phẩm',
        'source' => $product,
        'translation' => $translation,
        'fields' => [
            'name' => 'Tên sản phẩm',
            'description' => 'Mô tả',
            'seo_description' => 'SEO Description',
        ],
        'action' => route('admin.products.translation.update', $product->product_id),
        'method' => 'PUT',
    ])
</div>
@endsection
