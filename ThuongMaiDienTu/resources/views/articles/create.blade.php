@extends('layouts.app')

@section('title', isset($article) && $article->exists ? 'Chỉnh sửa bài viết - 24h Công Nghệ' : 'Đóng góp bài viết - 24h Công Nghệ')

@push('styles')
<style>
    body { background-color: #f4f6f8; }
    .create-container { max-width: 900px; margin: 40px auto; padding: 0 15px; }
    .form-card { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .form-title { font-size: 24px; font-weight: 800; color: #111827; margin-bottom: 5px; }
    .form-subtitle { font-size: 14px; color: #6b7280; margin-bottom: 25px; }
    
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 8px; }
    .form-control { width: 100%; padding: 12px 15px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 15px; transition: 0.2s; }
    .form-control:focus { outline: none; border-color: #d70018; box-shadow: 0 0 0 3px rgba(215, 0, 24, 0.1); }
    
    .btn-submit { background: #d70018; color: #fff; border: none; padding: 15px 30px; border-radius: 8px; font-size: 16px; font-weight: 700; width: 100%; cursor: pointer; transition: 0.3s; margin-top: 10px; }
    .btn-submit:hover { background: #b00014; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(215, 0, 24, 0.2); }
    
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    
    /* Custom style for TinyMCE */
    .tox-tinymce { border-radius: 8px !important; border: 1px solid #e5e7eb !important; }
</style>
@endpush

@section('content')
<div class="create-container">
    <div class="form-card">
        <h1 class="form-title">{{ isset($article) && $article->exists ? 'Chỉnh sửa bài viết' : 'Chia sẻ câu chuyện của bạn' }}</h1>
        <p class="form-subtitle">
            @if(isset($article) && $article->exists)
                Cập nhật lại nội dung bài viết của bạn. Lưu ý: Bài viết sẽ được duyệt lại sau khi bạn cập nhật.
            @else
                Bài viết của bạn sẽ được đội ngũ biên tập kiểm duyệt và xuất bản. Bạn có thể nhận được điểm thưởng khi bài viết được duyệt!
            @endif
        </p>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach($errors->all() as $error)
                        <li><i class="fa-solid fa-circle-exclamation mr-2"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ isset($article) && $article->exists ? route('articles.update', $article->article_id) : route('articles.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($article) && $article->exists)
                @method('PUT')
            @endif
            
            <div class="form-group">
                <label class="form-label">Tiêu đề bài viết <span style="color: #d70018;">*</span></label>
                <input type="text" name="title" value="{{ old('title', $article->title ?? '') }}" class="form-control" placeholder="Nhập tiêu đề hấp dẫn..." required>
            </div>

            <div class="form-group">
                <label class="form-label">Mô tả ngắn (Tóm tắt bài viết)</label>
                <textarea name="summary" rows="2" class="form-control" placeholder="Tóm tắt ngắn gọn nội dung bài viết của bạn...">{{ old('summary', $article->summary ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Ảnh đại diện bài viết</label>
                <div style="border: 2px dashed #e5e7eb; border-radius: 8px; padding: 20px; text-align: center; background: #f9fafb;">
                    <input type="file" name="thumbnail_file" accept="image/*" id="thumbnail_input" style="display: none;">
                    <label for="thumbnail_input" style="cursor: pointer;">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 32px; color: #9ca3af; margin-bottom: 10px; display: block;"></i>
                        <span style="color: #4b5563; font-weight: 500;">Click để tải ảnh lên (Hoặc để trống nếu giữ ảnh cũ)</span>
                    </label>
                    <div id="preview_container" style="margin-top: 15px; {{ (isset($article) && $article->thumbnail) ? '' : 'display: none;' }}">
                        <img id="thumbnail_preview" src="{{ $article->thumbnail ?? '#' }}" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 4px;">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nội dung chi tiết <span style="color: #d70018;">*</span></label>
                <textarea name="content" id="content_editor">{{ old('content', $article->content ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-paper-plane mr-2"></i> Gửi bài viết để kiểm duyệt
            </button>
        </form>
    </div>
</div>

<!-- Tích hợp TinyMCE với API Key từ cấu hình -->
<script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    tinymce.init({
        selector: '#content_editor',
        height: 500,
        plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking save table directionality emoticons template',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | forecolor backcolor | emoticons | code',
        menubar: false,
        placeholder: 'Bắt đầu viết nội dung tại đây...',
        content_style: 'body { font-family: Inter, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6; }'
    });

    // Preview ảnh
    document.getElementById('thumbnail_input').onchange = evt => {
        const [file] = evt.target.files;
        if (file) {
            document.getElementById('preview_container').style.display = 'block';
            document.getElementById('thumbnail_preview').src = URL.createObjectURL(file);
        }
    }
</script>
@endsection
