<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">كتبي</h4>
            <p class="text-muted mb-0">أرشفة الكتب الرسمية والبحث عنها</p>
        </div>
        <button type="button" class="btn btn-primary" wire:click="openCreateModal">
            <i class="bx bx-plus me-1"></i> إضافة كتاب
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">البحث والتصفية</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-lg-5">
                    <label class="form-label" for="book-search">بحث</label>
                    <input id="book-search" type="search" class="form-control" placeholder="رقم الكتاب، الموضوع أو الملاحظة..." wire:model.live.debounce.400ms="search">
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <label class="form-label" for="date-from">من تاريخ</label>
                    <input id="date-from" type="date" class="form-control" wire:model.live="dateFrom">
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <label class="form-label" for="date-to">إلى تاريخ</label>
                    <input id="date-to" type="date" class="form-control" wire:model.live="dateTo">
                </div>
                <div class="col-12 col-lg-3 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" wire:click="resetFilters">
                        <i class="bx bx-reset me-1"></i> إعادة تعيين
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>رقم الكتاب</th>
                        <th>تاريخ الكتاب</th>
                        <th>الموضوع</th>
                        <th>الملاحظة</th>
                        <th>الملفات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($books as $book)
                        <tr wire:key="book-{{ $book->id }}">
                            <td>{{ $book->book_number }}</td>
                            <td>{{ $book->book_date->format('Y-m-d') }}</td>
                            <td class="fw-semibold text-wrap">{{ \Illuminate\Support\Str::limit($book->subject, 80) }}</td>
                            <td class="text-wrap">{{ $book->notes ? \Illuminate\Support\Str::limit($book->notes, 60) : '—' }}</td>
                            <td><span class="badge bg-label-info">{{ $book->files_count }}</span></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('books.show', $book) }}" class="btn btn-sm btn-outline-primary" title="عرض التفاصيل"><i class="bx bx-show"></i></a>
                                    <a href="{{ route('books.edit', $book) }}" class="btn btn-sm btn-outline-secondary" title="تعديل"><i class="bx bx-edit"></i></a>
                                    @if ($book->primaryFile)
                                        <a href="{{ route('books.files.preview', $book->primaryFile) }}" target="_blank" class="btn btn-sm btn-outline-success" title="معاينة الملف الأساسي"><i class="bx bx-file-find"></i></a>
                                        <a href="{{ route('books.files.download', $book->primaryFile) }}" class="btn btn-sm btn-outline-info" title="تحميل الملف الأساسي"><i class="bx bx-download"></i></a>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteBook({{ $book->id }})" wire:confirm="هل تريد نقل هذا الكتاب إلى سلة المحذوفات؟" title="حذف"><i class="bx bx-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bx bx-book-open d-block fs-1 mb-2"></i>لا توجد كتب مطابقة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($books->hasPages()) <div class="card-footer">{{ $books->links() }}</div> @endif
    </div>

    @if ($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="create-book-title" wire:click.self="closeCreateModal">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form wire:submit="saveNewBook">
                        <div class="modal-header">
                            <h5 class="modal-title" id="create-book-title">
                                <i class="bx bx-book-add me-1"></i> إضافة كتاب جديد
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeCreateModal" aria-label="إغلاق"></button>
                        </div>

                        <div class="modal-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <strong>يرجى تصحيح الأخطاء التالية:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="new-book-number">رقم الكتاب <span class="text-danger">*</span></label>
                                    <input id="new-book-number" type="text" class="form-control @error('newBookNumber') is-invalid @enderror" wire:model="newBookNumber" autofocus>
                                    @error('newBookNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="new-book-date">تاريخ الكتاب <span class="text-danger">*</span></label>
                                    <input id="new-book-date" type="date" class="form-control @error('newBookDate') is-invalid @enderror" wire:model="newBookDate">
                                    @error('newBookDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="new-subject">الموضوع <span class="text-danger">*</span></label>
                                    <textarea id="new-subject" rows="3" class="form-control @error('newSubject') is-invalid @enderror" wire:model="newSubject"></textarea>
                                    @error('newSubject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="new-notes">الملاحظة <span class="text-muted">(اختياري)</span></label>
                                    <textarea id="new-notes" rows="2" class="form-control @error('newNotes') is-invalid @enderror" wire:model="newNotes"></textarea>
                                    @error('newNotes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-12 col-lg-6">
                                    <div class="border rounded p-3 h-100">
                                        <label class="form-label fw-semibold" for="new-scanner-file">
                                            <i class="bx bx-scan me-1"></i> فتح الماسح بالكاميرا
                                        </label>
                                        <input id="new-scanner-file" type="file" class="form-control @error('newScannedFile') is-invalid @enderror" wire:model="newScannedFile" accept="image/*" capture="environment">
                                        @error('newScannedFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <div wire:loading wire:target="newScannedFile" class="text-primary mt-2">
                                            <span class="spinner-border spinner-border-sm me-1"></span> جارٍ تجهيز الصورة...
                                        </div>
                                        @if ($newScannedFile)
                                            <div class="mt-3">
                                                <div class="small fw-semibold mb-2">{{ $newScannedFile->getClientOriginalName() }}</div>
                                                <img src="{{ $newScannedFile->temporaryUrl() }}" class="img-fluid rounded border" style="max-height: 260px" alt="معاينة الصورة الممسوحة">
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="border rounded p-3 h-100">
                                        <label class="form-label fw-semibold" for="new-book-files">
                                            <i class="bx bx-upload me-1"></i> رفع الكتاب
                                        </label>
                                        <input id="new-book-files" type="file" class="form-control @error('newFiles.*') is-invalid @enderror" wire:model="newFiles" accept=".pdf,.jpg,.jpeg,.png" multiple>
                                        @error('newFiles.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <div class="form-text">PDF أو JPG أو JPEG أو PNG، بحد أقصى 50 ميجابايت لكل ملف.</div>
                                        <div wire:loading wire:target="newFiles" class="text-primary mt-2">
                                            <span class="spinner-border spinner-border-sm me-1"></span> جارٍ رفع الملفات...
                                        </div>

                                        @if ($newFiles)
                                            <div class="list-group mt-3">
                                                @foreach ($newFiles as $index => $file)
                                                    <div class="list-group-item d-flex align-items-center justify-content-between gap-2" wire:key="modal-file-{{ $index }}">
                                                        <span class="text-truncate"><i class="bx bx-file me-1"></i>{{ $file->getClientOriginalName() }}</span>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeNewFile({{ $index }})">إزالة</button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="closeCreateModal">إلغاء</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveNewBook,newFiles,newScannedFile">
                                <span wire:loading.remove wire:target="saveNewBook"><i class="bx bx-save me-1"></i> حفظ الكتاب</span>
                                <span wire:loading wire:target="saveNewBook"><span class="spinner-border spinner-border-sm me-1"></span> جارٍ الحفظ...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
