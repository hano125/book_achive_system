<?php

namespace App\Livewire\Books;

use App\Models\Book;
use App\Models\BookFile;
use App\Services\BookFileService;
use App\Services\BookService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('layouts.Frontend.master')]
#[Title('حفظ كتاب')]
class BookForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $bookId = null;

    public ?string $bookNumber = null;

    public ?string $bookDate = null;

    public string $subject = '';

    public ?string $notes = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $files = [];

    public ?TemporaryUploadedFile $scannedFile = null;

    public function mount(?Book $book = null): void
    {
        if (! $book?->exists) {
            return;
        }

        $this->bookId = $book->id;
        $this->bookNumber = $book->book_number;
        $this->bookDate = $book->book_date?->format('Y-m-d');
        $this->subject = $book->subject;
        $this->notes = $book->notes;
    }

    public function removeSelectedFile(int $index): void
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files);
    }

    public function deleteExistingFile(int $fileId, BookFileService $bookFileService): void
    {
        $file = BookFile::query()->where('book_id', $this->bookId)->findOrFail($fileId);
        $bookFileService->delete($file);
        session()->flash('success', 'تم حذف الملف.');
    }

    public function setPrimaryFile(int $fileId, BookFileService $bookFileService): void
    {
        $file = BookFile::query()->where('book_id', $this->bookId)->findOrFail($fileId);
        $bookFileService->setPrimary($file);
        session()->flash('success', 'تم تعيين الملف الأساسي.');
    }

    public function save(BookService $bookService): mixed
    {
        $validated = $this->validate();
        $data = [
            'book_number' => $validated['bookNumber'],
            'book_date' => $validated['bookDate'],
            'subject' => $validated['subject'],
            'notes' => $validated['notes'],
        ];
        $uploadedFiles = $validated['files'];

        if ($validated['scannedFile']) {
            array_unshift($uploadedFiles, $validated['scannedFile']);
        }

        $book = $this->bookId
            ? $bookService->update(Book::query()->findOrFail($this->bookId), $data, $uploadedFiles)
            : $bookService->create($data, $uploadedFiles);

        session()->flash('success', $this->bookId ? 'تم تعديل الكتاب بنجاح.' : 'تمت إضافة الكتاب بنجاح.');

        return $this->redirectRoute('books.show', ['book' => $book->getRouteKey()]);
    }

    public function render(): View
    {
        return view('livewire.books.book-form', [
            'existingFiles' => $this->bookId
                ? BookFile::query()->where('book_id', $this->bookId)->orderBy('sort_order')->get()
                : collect(),
        ]);
    }

    protected function rules(): array
    {
        return [
            'bookNumber' => ['required', 'string', 'max:100'],
            'bookDate' => ['required', 'date'],
            'subject' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'files' => ['array'],
            'files.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:51200'],
            'scannedFile' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'mimetypes:image/jpeg,image/png', 'max:51200'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'string' => 'يجب أن يكون :attribute نصاً.',
            'date' => 'يجب أن يكون :attribute تاريخاً صحيحاً.',
            'integer' => 'يجب أن يكون :attribute رقماً صحيحاً.',
            'min' => 'قيمة :attribute أصغر من الحد المسموح.',
            'max' => 'قيمة أو حجم :attribute أكبر من الحد المسموح.',
            'exists' => ':attribute المحدد غير موجود.',
            'in' => 'قيمة :attribute غير صالحة.',
            'mimes' => 'يجب أن يكون :attribute من نوع PDF أو JPG أو JPEG أو PNG.',
            'mimetypes' => 'محتوى :attribute غير مدعوم.',
            'file' => 'يجب أن يكون :attribute ملفاً صالحاً.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'bookNumber' => 'رقم الكتاب',
            'bookDate' => 'تاريخ الكتاب',
            'subject' => 'الموضوع',
            'notes' => 'الملاحظة',
            'files.*' => 'الملف',
            'scannedFile' => 'الصورة الممسوحة',
        ];
    }
}
