@props(['file'])

@if ($file)
    <div
        class="modal fade show d-block"
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="file-preview-title"
        wire:click.self="closeFilePreview"
    >
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-truncate" id="file-preview-title">
                        <i class="bx bx-file-find me-1"></i> {{ $file->original_name }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeFilePreview" aria-label="إغلاق"></button>
                </div>

                <div class="modal-body p-0 bg-dark">
                    <iframe
                        src="{{ route('books.files.preview', $file) }}#toolbar=0&navpanes=0"
                        title="معاينة {{ $file->original_name }}"
                        class="d-block w-100 border-0"
                        style="height: min(78vh, 900px)"
                    ></iframe>
                </div>

                <div class="modal-footer">
                    <small class="text-muted me-auto">المعاينة فقط</small>
                    <button type="button" class="btn btn-secondary" wire:click="closeFilePreview">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
@endif
