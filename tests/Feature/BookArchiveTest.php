<?php

use App\Livewire\Books\BookForm;
use App\Livewire\Books\BookIndex;
use App\Livewire\Books\BookShow;
use App\Models\Book;
use App\Models\BookFile;
use App\Models\User;
use App\Services\BookFileService;
use App\Services\BookService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

test('guest cannot access my books pages', function (string $route) {
    $this->get($route)->assertRedirect(route('login'));
})->with([
    '/my-books',
    '/my-books/create',
    '/my-books/trash',
    '/my-books/categories',
]);

test('the single user can log in and log out', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertRedirect(route('books.index'));

    $this->assertAuthenticatedAs($user);
    $this->get(route('books.index'))
        ->assertSuccessful()
        ->assertSee('كتبي');

    $this->post(route('logout'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('authenticated user can create a book with multiple files and first file becomes primary', function () {
    Storage::fake('local');
    $this->actingAs(User::factory()->create());

    $component = Livewire::test(BookForm::class)
        ->set('bookNumber', '100')
        ->set('bookDate', '2026-07-23')
        ->set('subject', 'موضوع الكتاب الرسمي')
        ->set('notes', 'ملاحظة اختيارية')
        ->set('scannedFile', UploadedFile::fake()->image('scan.jpg'))
        ->set('files', [
            UploadedFile::fake()->create('first.pdf', 100, 'application/pdf'),
        ])
        ->call('save')
        ->assertHasNoErrors();

    $book = Book::query()->with('files')->firstOrFail();

    $component->assertRedirect(route('books.show', $book));

    expect($book->files)->toHaveCount(2)
        ->and($book->subject)->toBe('موضوع الكتاب الرسمي')
        ->and($book->notes)->toBe('ملاحظة اختيارية')
        ->and($book->files->first()->original_name)->toBe('scan.jpg')
        ->and($book->files->first()->is_primary)->toBeTrue()
        ->and($book->files->last()->is_primary)->toBeFalse();

    $book->files->each(fn (BookFile $file) => Storage::disk('local')->assertExists($file->file_path));
});

test('user can create a book from the index modal and preview its uploaded file', function () {
    Storage::fake('local');
    $this->actingAs(User::factory()->create());

    $this->get('/my_books')->assertRedirect('/my-books');

    $this->get(route('books.index', ['create' => 1]))
        ->assertSuccessful()
        ->assertSee('إضافة كتاب جديد');

    $component = Livewire::test(BookIndex::class)
        ->call('openCreateModal')
        ->assertSet('showCreateModal', true)
        ->assertSee('إضافة كتاب جديد')
        ->set('newBookNumber', '2026')
        ->set('newBookDate', '2026-07-23')
        ->set('newSubject', 'كتاب أضيف من النافذة')
        ->set('newFiles', [
            UploadedFile::fake()->create('book.pdf', 100, 'application/pdf'),
        ])
        ->call('saveNewBook')
        ->assertHasNoErrors()
        ->assertSet('showCreateModal', false)
        ->assertSee('تمت إضافة الكتاب بنجاح.');

    $book = Book::query()->with('primaryFile')->firstOrFail();

    expect($book->book_number)->toBe('2026')
        ->and($book->primaryFile)->not->toBeNull();

    $component
        ->call('openFilePreview', $book->primaryFile->id)
        ->assertSee('<iframe', false)
        ->assertSee(route('books.files.preview', $book->primaryFile), false);
    Storage::disk('local')->assertExists($book->primaryFile->file_path);
});

test('user can change the primary file', function () {
    $book = Book::factory()->create();
    $first = BookFile::factory()->for($book)->create(['is_primary' => true, 'sort_order' => 1]);
    $second = BookFile::factory()->for($book)->create(['is_primary' => false, 'sort_order' => 2]);

    app(BookFileService::class)->setPrimary($second);

    expect($first->refresh()->is_primary)->toBeFalse()
        ->and($second->refresh()->is_primary)->toBeTrue();
});

test('file metadata is captured before the temporary file is stored', function () {
    Storage::fake('local');
    $book = Book::factory()->create();
    $temporaryPath = tempnam(sys_get_temp_dir(), 'book-upload-');
    file_put_contents($temporaryPath, '%PDF test document');

    $file = new class($temporaryPath, 'temporary.pdf', 'application/pdf', null, true) extends UploadedFile
    {
        private bool $stored = false;

        public function storeAs($path, $name = null, $options = [])
        {
            $storedPath = parent::storeAs($path, $name, $options);
            $this->stored = true;

            return $storedPath;
        }

        public function getSize(): int|false
        {
            if ($this->stored) {
                throw new RuntimeException('Metadata was requested after storage.');
            }

            return parent::getSize();
        }

        public function getMimeType(): ?string
        {
            if ($this->stored) {
                throw new RuntimeException('Metadata was requested after storage.');
            }

            return 'application/pdf';
        }
    };

    $storedFile = app(BookFileService::class)->store($book, $file);

    expect($storedFile->file_size)->toBe(strlen('%PDF test document'))
        ->and($storedFile->mime_type)->toBe('application/pdf')
        ->and($storedFile->file_type)->toBe('pdf');
    Storage::disk('local')->assertExists($storedFile->file_path);
});

test('user can search books', function () {
    $this->actingAs(User::factory()->create());
    Book::factory()->create(['subject' => 'الكتاب المطلوب']);
    Book::factory()->create(['subject' => 'عنوان مختلف']);

    Livewire::test(BookIndex::class)
        ->set('search', 'المطلوب')
        ->assertSee('الكتاب المطلوب')
        ->assertDontSee('عنوان مختلف');
});

test('user can update a book', function () {
    $this->actingAs(User::factory()->create());
    $book = Book::factory()->create(['subject' => 'الموضوع القديم']);

    Livewire::test(BookForm::class, ['book' => $book])
        ->set('subject', 'الموضوع الجديد')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect($book->refresh()->subject)->toBe('الموضوع الجديد');
});

test('authenticated user sees the full book details page', function () {
    $this->actingAs(User::factory()->create());
    $book = Book::factory()->create([
        'book_number' => '2026',
        'subject' => 'موضوع الكتاب المحفوظ',
    ]);

    $this->get(route('books.show', $book))
        ->assertSuccessful()
        ->assertSee('<!DOCTYPE html>', false)
        ->assertSee('تفاصيل الكتاب')
        ->assertSee('موضوع الكتاب المحفوظ');
});

test('book can be soft deleted and restored', function () {
    $book = Book::factory()->create();
    $service = app(BookService::class);

    $service->softDelete($book);
    expect(Book::onlyTrashed()->find($book->id))->not->toBeNull();

    $restored = $service->restore($book->id);
    expect($restored->trashed())->toBeFalse();
});

test('permanent deletion removes physical files', function () {
    Storage::fake('local');
    $book = Book::factory()->create();
    $path = 'books/'.$book->id.'/document.pdf';
    Storage::disk('local')->put($path, 'pdf');
    BookFile::factory()->for($book)->create(['file_path' => $path]);

    $book->delete();
    app(BookService::class)->forceDelete($book);

    Storage::disk('local')->assertMissing($path);
    expect(Book::withTrashed()->find($book->id))->toBeNull();
});

test('private files are available only through authenticated routes', function () {
    Storage::fake('local');
    $book = Book::factory()->create();
    $path = 'books/'.$book->id.'/private.pdf';
    Storage::disk('local')->put($path, '%PDF');
    $file = BookFile::factory()->for($book)->create([
        'file_path' => $path,
        'original_name' => 'private.pdf',
        'mime_type' => 'application/pdf',
        'file_type' => 'pdf',
    ]);

    $this->get(route('books.files.preview', $file))->assertRedirect(route('login'));
    $this->get(route('books.files.download', $file))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('books.files.preview', $file))
        ->assertSuccessful()
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    $this->get(route('books.files.download', $file))
        ->assertSuccessful()
        ->assertDownload('private.pdf');
});

test('uploaded books are previewed in an iframe without a download link', function () {
    Storage::fake('local');
    $this->actingAs(User::factory()->create());

    $book = Book::factory()->create();
    $path = 'books/'.$book->id.'/preview.pdf';
    Storage::disk('local')->put($path, '%PDF');
    $file = BookFile::factory()->for($book)->create([
        'file_path' => $path,
        'original_name' => 'preview.pdf',
        'mime_type' => 'application/pdf',
        'file_type' => 'pdf',
        'is_primary' => true,
    ]);

    Livewire::test(BookIndex::class)
        ->call('openFilePreview', $file->id)
        ->assertSet('previewFileId', $file->id)
        ->assertSee('<iframe', false)
        ->assertSee(route('books.files.preview', $file), false)
        ->assertDontSee(route('books.files.download', $file), false)
        ->call('closeFilePreview')
        ->assertSet('previewFileId', null)
        ->assertDontSee('<iframe', false);

    Livewire::test(BookShow::class, ['book' => $book])
        ->call('openFilePreview', $file->id)
        ->assertSee('<iframe', false)
        ->assertSee('المعاينة فقط')
        ->assertDontSee('تحميل');
});
