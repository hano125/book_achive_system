<?php

namespace App\Livewire\Books;

use App\Models\Book;
use App\Services\BookService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.Frontend.master')]
#[Title('كتبي')]
class BookIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url(as: 'create', except: false)]
    public bool $showCreateModal = false;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    public ?string $newBookNumber = null;

    public ?string $newBookDate = null;

    public string $newSubject = '';

    public ?string $newNotes = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $newFiles = [];

    public ?TemporaryUploadedFile $newScannedFile = null;

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->resetCreateForm();
    }

    public function removeNewFile(int $index): void
    {
        unset($this->newFiles[$index]);
        $this->newFiles = array_values($this->newFiles);
    }

    public function saveNewBook(BookService $bookService): void
    {
        $validated = $this->validate($this->createBookRules(), $this->createBookMessages(), $this->createBookAttributes());
        $uploadedFiles = $validated['newFiles'];

        if ($validated['newScannedFile']) {
            array_unshift($uploadedFiles, $validated['newScannedFile']);
        }

        $bookService->create([
            'book_number' => $validated['newBookNumber'],
            'book_date' => $validated['newBookDate'],
            'subject' => $validated['newSubject'],
            'notes' => $validated['newNotes'],
        ], $uploadedFiles);

        $this->resetCreateForm();
        $this->resetPage();
        session()->flash('success', 'تمت إضافة الكتاب بنجاح.');
    }

    public function deleteBook(Book $book, BookService $bookService): void
    {
        $bookService->softDelete($book);
        session()->flash('success', 'تم نقل الكتاب إلى سلة المحذوفات.');
    }

    public function render(): View
    {
        $books = Book::query()
            ->select([
                'id', 'book_number', 'book_date', 'subject', 'notes', 'created_at',
            ])
            ->with('primaryFile')
            ->withCount('files')
            ->search($this->search)
            ->betweenDates($this->dateFrom ?: null, $this->dateTo ?: null)
            ->latest()
            ->paginate(15);

        return view('livewire.books.book-index', [
            'books' => $books,
        ]);
    }

    private function resetCreateForm(): void
    {
        $this->reset([
            'showCreateModal',
            'newBookNumber',
            'newBookDate',
            'newSubject',
            'newNotes',
            'newFiles',
            'newScannedFile',
        ]);
        $this->resetValidation();
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function createBookRules(): array
    {
        return [
            'newBookNumber' => ['required', 'string', 'max:100'],
            'newBookDate' => ['required', 'date'],
            'newSubject' => ['required', 'string'],
            'newNotes' => ['nullable', 'string'],
            'newFiles' => ['array'],
            'newFiles.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:51200'],
            'newScannedFile' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'mimetypes:image/jpeg,image/png', 'max:51200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function createBookMessages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'string' => 'يجب أن يكون :attribute نصاً.',
            'date' => 'يجب أن يكون :attribute تاريخاً صحيحاً.',
            'max' => 'قيمة أو حجم :attribute أكبر من الحد المسموح.',
            'mimes' => 'يجب أن يكون :attribute من نوع PDF أو JPG أو JPEG أو PNG.',
            'mimetypes' => 'محتوى :attribute غير مدعوم.',
            'file' => 'يجب أن يكون :attribute ملفاً صالحاً.',
            'image' => 'يجب أن يكون :attribute صورة صالحة.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function createBookAttributes(): array
    {
        return [
            'newBookNumber' => 'رقم الكتاب',
            'newBookDate' => 'تاريخ الكتاب',
            'newSubject' => 'الموضوع',
            'newNotes' => 'الملاحظة',
            'newFiles.*' => 'الملف',
            'newScannedFile' => 'الصورة الممسوحة',
        ];
    }
}
