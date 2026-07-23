<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
        <div><h4 class="fw-bold mb-1">سلة المحذوفات</h4><p class="text-muted mb-0">استرجاع الكتب أو حذفها نهائياً</p></div>
        <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">جميع الكتب</a>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>رقم الكتاب</th><th>تاريخ الكتاب</th><th>الموضوع</th><th>الملفات</th><th>تاريخ الحذف</th><th>الإجراءات</th></tr></thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr wire:key="trashed-book-{{ $book->id }}">
                            <td>{{ $book->book_number }}</td>
                            <td>{{ $book->book_date->format('Y-m-d') }}</td>
                            <td class="fw-semibold text-wrap">{{ $book->subject }}</td>
                            <td><span class="badge bg-label-info">{{ $book->files_count }}</span></td>
                            <td>{{ $book->deleted_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-success" wire:click="restoreBook({{ $book->id }})">استرجاع</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="forceDeleteBook({{ $book->id }})" wire:confirm="سيتم حذف الكتاب وجميع ملفاته نهائياً. هل أنت متأكد؟">حذف نهائي</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">سلة المحذوفات فارغة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($books->hasPages()) <div class="card-footer">{{ $books->links() }}</div> @endif
    </div>
</div>
