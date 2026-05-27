@php
    /** @var \Illuminate\Database\Eloquent\Model $source */
    /** @var \Illuminate\Database\Eloquent\Model $translation */
    $fields = $fields ?? [];
    $title = $title ?? 'Translation Editor';
    $action = $action ?? '#';
    $method = $method ?? 'PUT';
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom-0 pb-0">
        <h5 class="mb-0">{{ $title }}</h5>
        <p class="text-muted mb-0">Bên trái là tiếng Việt gốc, bên phải là bản dịch EN có thể chỉnh sửa.</p>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if(strtoupper($method) !== 'POST')
                @method($method)
            @endif

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100 bg-light">
                        <h6 class="fw-semibold mb-3">Tiếng Việt gốc</h6>
                        @foreach($fields as $field => $label)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ $label }}</label>
                                @if(in_array($field, ['content']))
                                    <textarea class="form-control" rows="10" readonly>{{ old($field, data_get($source, $field)) }}</textarea>
                                @else
                                    <input type="text" class="form-control" value="{{ old($field, data_get($source, $field)) }}" readonly>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <h6 class="fw-semibold mb-3">Bản dịch EN</h6>
                        @foreach($fields as $field => $label)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ $label }}</label>
                                @if(in_array($field, ['content']))
                                    <textarea name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" rows="10">{{ old($field, data_get($translation, $field)) }}</textarea>
                                @else
                                    <input type="text" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, data_get($translation, $field)) }}">
                                @endif
                                @error($field)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">Lưu bản dịch EN</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
