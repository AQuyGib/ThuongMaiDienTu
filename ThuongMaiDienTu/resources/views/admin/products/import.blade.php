@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="table-card animate-in">
        <div class="table-card-header">
            <h5><i class="bi bi-upload me-2" style="color:var(--accent);"></i>Import Sản Phẩm</h5>
            <a href="{{ route('admin.products.template') }}" class="btn btn-cancel">
                <i class="bi bi-file-earmark-spreadsheet"></i> Tải Mẫu Excel
            </a>
        </div>

        <div class="p-4">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Chọn file Excel</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>

                <div class="alert alert-info">
                    File nên có các cột: <strong>sku, name, category, base_price, stock, status</strong>
                </div>

                <button type="submit" class="btn btn-accent">
                    <i class="bi bi-upload"></i> Tải lên và Import
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
