@extends('admin.layouts.master')

@section('title', 'Import sản phẩm')

@push('styles')
<style>
    .import-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(15,23,42,.05);
    }
    .import-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15,23,42,.05);
    }
    .upload-zone {
        border: 2px dashed #c7d2fe;
        background: linear-gradient(180deg, #eef2ff 0%, #ffffff 100%);
        border-radius: 20px;
        padding: 28px;
        text-align: center;
        transition: all .2s ease;
    }
    .upload-zone:hover { border-color: #818cf8; box-shadow: 0 10px 30px rgba(99,102,241,.08); }
    .upload-icon {
        width: 72px; height: 72px; border-radius: 22px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #6366f1; color: #fff; font-size: 1.8rem;
        box-shadow: 0 14px 26px rgba(99,102,241,.2);
    }
    .file-input {
        background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 14px;
        padding: 12px 14px;
    }
    .step-badge {
        width: 34px; height: 34px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: #eef2ff; color: #4338ca; font-weight: 800;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="import-hero p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <div class="d-inline-flex align-items-center gap-2 rounded-pill bg-indigo-50 px-3 py-2 text-sm fw-semibold text-indigo-700 mb-3">
                    <i class="bi bi-file-earmark-arrow-up"></i>
                    Nhập dữ liệu sản phẩm
                </div>
                <h1 class="h3 fw-bold text-slate-900 mb-2">Import sản phẩm từ Excel</h1>
                <p class="text-slate-500 mb-0">Tải file mẫu trước khi import để đảm bảo đúng định dạng và tránh lỗi dữ liệu.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Vui lòng kiểm tra lại file</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="import-card p-4 p-lg-5 h-100">
                <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="upload-zone mb-4">
                        <div class="upload-icon mb-3">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                        </div>
                        <h2 class="h5 fw-bold text-slate-900 mb-2">Chọn file Excel để import</h2>
                        <p class="text-slate-500 mb-4">Hỗ trợ định dạng <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong>.</p>
                        <input type="file" name="file" class="form-control file-input mx-auto" accept=".xlsx,.xls,.csv" required style="max-width: 520px;">
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i> Tải lên và Import
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="import-card p-4 p-lg-5 h-100">
                <h3 class="h5 fw-bold text-slate-900 mb-4">Hướng dẫn nhanh</h3>
                <div class="d-flex gap-3 mb-3">
                    <div class="step-badge">1</div>
                    <div>
                        <div class="fw-semibold text-slate-900">Tải file mẫu</div>
                        <div class="text-sm text-slate-500">Dùng đúng cấu trúc cột sẵn có.</div>
                    </div>
                </div>
                <div class="d-flex gap-3 mb-3">
                    <div class="step-badge">2</div>
                    <div>
                        <div class="fw-semibold text-slate-900">Điền dữ liệu</div>
                        <div class="text-sm text-slate-500">Kiểm tra tên, danh mục và giá bán.</div>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <div class="step-badge">3</div>
                    <div>
                        <div class="fw-semibold text-slate-900">Tải lên</div>
                        <div class="text-sm text-slate-500">Hệ thống sẽ nhập dữ liệu vào danh sách sản phẩm.</div>
                    </div>
                </div>
                <hr class="my-4">
                <div class="small text-slate-500">
                    Gợi ý: chuẩn bị file theo đúng cấu trúc cột mà hệ thống yêu cầu trước khi import.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
