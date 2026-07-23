<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">تفاصيل الكتاب</h4>
            <p class="text-muted mb-0">{{ $book->subject }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('books.edit', $book) }}" class="btn btn-primary"><i class="bx bx-edit me-1"></i> تعديل</a>
            <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">رجوع</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">معلومات الكتاب</h5></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 mb-3">رقم الكتاب</dt><dd class="col-sm-7 mb-3">{{ $book->book_number }}</dd>
                        <dt class="col-sm-5 mb-3">تاريخ الكتاب</dt><dd class="col-sm-7 mb-3">{{ $book->book_date->format('Y-m-d') }}</dd>
                        <dt class="col-12 mb-2">الموضوع</dt><dd class="col-12 mb-3 text-wrap">{{ $book->subject }}</dd>
                        <dt class="col-12 mb-2">الملاحظة</dt><dd class="col-12 mb-0 text-wrap">{{ $book->notes ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">الملفات المرفوعة</h5>
                    <span class="badge bg-label-info">{{ $book->files->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse ($book->files as $file)
                        <div class="border rounded p-3 mb-3" wire:key="show-file-{{ $file->id }}">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                <div>
                                    <h6 class="mb-1">
                                        {{ $file->original_name }}
                                        @if ($file->is_primary)<span class="badge bg-label-primary ms-1">أساسي</span>@endif
                                    </h6>
                                    <small class="text-muted">{{ $file->formatted_file_size }} · {{ $file->file_type }} · {{ $file->created_at->format('Y-m-d') }}</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openFilePreview({{ $file->id }})">
                                        <i class="bx bx-show me-1"></i> معاينة
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5"><i class="bx bx-file d-block fs-1 mb-2"></i>لا توجد ملفات مرفوعة.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-ui.file-preview-modal :file="$previewFile" />
</div>
