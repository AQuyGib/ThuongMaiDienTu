@extends('admin.layout')

@section('title', 'Sửa bản dịch trang')

@section('content')
<div class="container-fluid py-4">
    @include('admin.translations.partials.two-column-form', [
        'title' => 'Chỉnh sửa bản dịch EN cho trang',
        'source' => $page,
        'translation' => $translation,
        'fields' => [
            'title' => 'Tiêu đề',
            'excerpt' => 'Tóm tắt',
            'content' => 'Nội dung',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
        ],
        'action' => route('admin.pages.translation.update', $page->page_id),
        'method' => 'PUT',
    ])
</div>
@endsection
