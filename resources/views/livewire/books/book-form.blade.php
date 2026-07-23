<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
        <h4 class="fw-bold mb-0">{{ $bookId ? 'تعديل الكتاب' : 'إضافة كتاب' }}</h4>
        <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> رجوع
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>يرجى تصحيح الأخطاء التالية:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">بيانات الكتاب</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="book-number">رقم الكتاب <span class="text-danger">*</span></label>
                        <input id="book-number" type="text" class="form-control @error('bookNumber') is-invalid @enderror" wire:model="bookNumber">
                        @error('bookNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="book-date">تاريخ الكتاب <span class="text-danger">*</span></label>
                        <input id="book-date" type="date" class="form-control @error('bookDate') is-invalid @enderror" wire:model="bookDate">
                        @error('bookDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="subject">الموضوع <span class="text-danger">*</span></label>
                        <textarea id="subject" class="form-control @error('subject') is-invalid @enderror" rows="3" wire:model="subject"></textarea>
                        @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="notes">الملاحظة <span class="text-muted">(اختياري)</span></label>
                        <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" wire:model="notes"></textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">مسح أو رفع الكتاب</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <label class="form-label fw-semibold" for="scanner-file">
                                <i class="bx bx-scan me-1"></i> فتح الماسح بالكاميرا
                            </label>
                            <input id="scanner-file" type="file" class="form-control @error('scannedFile') is-invalid @enderror" wire:model="scannedFile" accept="image/*" capture="environment">
                            @error('scannedFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">يفتح الكاميرا الخلفية مباشرة على الهواتف والأجهزة المدعومة.</div>
                            <div wire:loading wire:target="scannedFile" class="text-primary mt-2">
                                <span class="spinner-border spinner-border-sm me-1"></span> جارٍ تجهيز الصورة...
                            </div>
                            @if ($scannedFile)
                                <div class="alert alert-success py-2 mt-3 mb-0">
                                    <i class="bx bx-check-circle me-1"></i>{{ $scannedFile->getClientOriginalName() }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <label class="form-label fw-semibold" for="book-files">
                                <i class="bx bx-upload me-1"></i> رفع الكتاب
                            </label>
                            <input id="book-files" type="file" class="form-control @error('files.*') is-invalid @enderror" wire:model="files" accept=".pdf,.jpg,.jpeg,.png" multiple>
                            <div class="form-text">PDF أو JPG أو JPEG أو PNG، بحد أقصى 50 ميجابايت لكل ملف.</div>
                            <div wire:loading wire:target="files" class="text-primary mt-2">
                                <span class="spinner-border spinner-border-sm me-1"></span> جارٍ رفع الملفات...
                            </div>
                        </div>
                    </div>
                </div>

                @if ($files)
                    <div class="list-group mt-3">
                        @foreach ($files as $index => $file)
                            <div class="list-group-item d-flex align-items-center justify-content-between gap-2" wire:key="new-file-{{ $index }}">
                                <span class="text-truncate"><i class="bx bx-file me-1"></i>{{ $file->getClientOriginalName() }}</span>
                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeSelectedFile({{ $index }})">إزالة</button>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($existingFiles->isNotEmpty())
                    <h6 class="mt-4">الملفات الحالية</h6>
                    <div class="list-group">
                        @foreach ($existingFiles as $file)
                            <div class="list-group-item d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2" wire:key="existing-file-{{ $file->id }}">
                                <div>
                                    <i class="bx {{ $file->isPdf() ? 'bxs-file-pdf' : 'bx-image' }} me-1"></i>
                                    {{ $file->original_name }}
                                    @if ($file->is_primary)
                                        <span class="badge bg-label-primary ms-1">أساسي</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    @unless ($file->is_primary)
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="setPrimaryFile({{ $file->id }})">تعيين كأساسي</button>
                                    @endunless
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteExistingFile({{ $file->id }})" wire:confirm="هل تريد حذف هذا الملف نهائياً؟">حذف</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save,files,scannedFile">
            <span wire:loading.remove wire:target="save"><i class="bx bx-save me-1"></i> حفظ</span>
            <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm me-1"></span> جارٍ الحفظ...</span>
        </button>
    </form>
</div>
