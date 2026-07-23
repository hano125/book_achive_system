<?php

namespace App\Livewire\Books;

use App\Models\Book;
use App\Services\BookService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.Frontend.master')]
#[Title('سلة المحذوفات')]
class BookTrash extends Component
{
    use WithPagination;

    public function restoreBook(int $bookId, BookService $bookService): void
    {
        $bookService->restore($bookId);
        session()->flash('success', 'تم استرجاع الكتاب.');
    }

    public function forceDeleteBook(int $bookId, BookService $bookService): void
    {
        $bookService->forceDelete(Book::onlyTrashed()->findOrFail($bookId));
        session()->flash('success', 'تم حذف الكتاب نهائياً.');
    }

    public function render(): View
    {
        return view('livewire.books.book-trash', [
            'books' => Book::onlyTrashed()
                ->select(['id', 'book_number', 'book_date', 'subject', 'deleted_at'])
                ->withCount('files')
                ->latest('deleted_at')
                ->paginate(15),
        ]);
    }
}
