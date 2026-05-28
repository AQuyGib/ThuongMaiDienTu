@extends('admin.layout')

@section('title', 'Sửa bản dịch thuộc tính')

@section('content')
<div class="container-fluid py-4">
    @include('admin.translations.partials.two-column-form', [
        'title' => 'Chỉnh sửa bản dịch EN cho thuộc tính',
        'source' => $attribute,
        'translation' => $translation,
        'fields' => [
            'name' => 'Tên thuộc tính',
            'description' => 'Mô tả',
        ],
        'action' => route('admin.attributes.translation.update', $attribute->attribute_id),
        'method' => 'PUT',
    ])
</div>
@endsection
