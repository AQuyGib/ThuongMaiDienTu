@extends('admin.layout')

@section('title', 'Sửa bản dịch danh mục')

@section('content')
<div class="container-fluid py-4">
    @include('admin.translations.partials.two-column-form', [
        'title' => 'Chỉnh sửa bản dịch EN cho danh mục',
        'source' => $category,
        'translation' => $translation,
        'fields' => [
            'name' => 'Tên danh mục',
            'description' => 'Mô tả',
            'seo_description' => 'SEO Description',
        ],
        'action' => route('admin.categories.translation.update', $category->category_id),
        'method' => 'PUT',
    ])
</div>
@endsection
