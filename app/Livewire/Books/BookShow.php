<?php

namespace App\Livewire\Books;

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.Frontend.master')]
#[Title('تفاصيل الكتاب')]
class BookShow extends Component
{
    #[Locked]
    public int $bookId;

    #[Locked]
    public ?int $previewFileId = null;

    public function mount(Book $book): void
    {
        $this->bookId = $book->id;
    }

    public function openFilePreview(int $fileId): void
    {
        $file = BookFile::query()
            ->where('book_id', $this->bookId)
            ->findOrFail($fileId);

        abort_unless($file->isPdf() || $file->isImage(), 415);

        $this->previewFileId = $file->id;
    }

    public function closeFilePreview(): void
    {
        $this->previewFileId = null;
    }

    public function render(): View
    {
        return view('livewire.books.book-show', [
            'book' => Book::query()->with('files')->findOrFail($this->bookId),
            'previewFile' => $this->previewFileId
                ? BookFile::query()
                    ->where('book_id', $this->bookId)
                    ->findOrFail($this->previewFileId)
                : null,
        ]);
    }
}
