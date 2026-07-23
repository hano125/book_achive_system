<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
        <div><h4 class="fw-bold mb-1">التصنيفات</h4><p class="text-muted mb-0">إدارة تصنيفات الكتب والمستندات</p></div>
        <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">جميع الكتب</a>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @error('categoryDelete') <div class="alert alert-danger">{{ $message }}</div> @enderror

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ $editingId ? 'تعديل التصنيف' : 'إضافة تصنيف' }}</h5></div>
                <div class="card-body">
                    <form wire:submit="save">
                        <div class="mb-3">
                            <label class="form-label" for="category-name">الاسم</label>
                            <input id="category-name" type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="category-description">الوصف</label>
                            <textarea id="category-description" class="form-control @error('description') is-invalid @enderror" rows="4" wire:model="description"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input id="category-active" type="checkbox" class="form-check-input" wire:model="isActive">
                            <label for="category-active" class="form-check-label">فعال</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $editingId ? 'حفظ التعديل' : 'إضافة' }}</button>
                            @if ($editingId)<button type="button" class="btn btn-outline-secondary" wire:click="resetForm">إلغاء</button>@endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>الاسم</th><th>الوصف</th><th>عدد الكتب</th><th>الحالة</th><th>الإجراءات</th></tr></thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr wire:key="category-{{ $category->id }}" class="{{ $category->trashed() ? 'table-light' : '' }}">
                                    <td class="fw-semibold">{{ $category->name }}</td>
                                    <td class="text-wrap">{{ $category->description ?: '—' }}</td>
                                    <td><span class="badge bg-label-info">{{ $category->books_count }}</span></td>
                                    <td>
                                        @if ($category->trashed())
                                            <span class="badge bg-label-danger">محذوف</span>
                                        @elseif ($category->is_active)
                                            <span class="badge bg-label-success">فعال</span>
                                        @else
                                            <span class="badge bg-label-secondary">غير فعال</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if ($category->trashed())
                                                <button type="button" class="btn btn-sm btn-outline-success" wire:click="restore({{ $category->id }})">استرجاع</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="forceDelete({{ $category->id }})" wire:confirm="هل تريد حذف التصنيف نهائياً؟">حذف نهائي</button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="edit({{ $category->id }})">تعديل</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="delete({{ $category->id }})" wire:confirm="هل تريد نقل التصنيف إلى المحذوفات؟">حذف</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-5">لا توجد تصنيفات.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
