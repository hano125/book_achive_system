<?php

namespace App\Livewire\Books;

use App\Models\Book;
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

    public function mount(Book $book): void
    {
        $this->bookId = $book->id;
    }

    public function render(): View
    {
        return view('livewire.books.book-show', [
            'book' => Book::query()->with('files')->findOrFail($this->bookId),
        ]);
    }
}
