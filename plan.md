Create a simple personal book archive module inside my existing Laravel project.

Important:

* The system is for one user only.
* Do not create roles or permissions.
* Do not create multi-user management.
* Do not create departments or approval workflows.
* Do not create a new dashboard theme.
* I already have an admin theme installed.
* Integrate all new pages into my existing theme, layout, sidebar, navbar, components, CSS classes, and JavaScript.
* Before creating views, inspect the existing project structure and identify the main layout file, sidebar, navbar, form components, table styles, modal styles, buttons, cards, alerts, pagination, and asset files.
* Reuse the existing theme exactly.
* Do not replace or redesign the theme.
* Do not modify unrelated existing files.

## Technology

Use:

* Existing Laravel project
* Livewire 3
* MySQL
* Existing authentication
* Existing theme
* Existing CSS framework already used by the project

Do not install Tailwind, Bootstrap, Alpine, or another UI library unless it is already used by the existing project.

## Main purpose

I want to scan my personal books or official documents, save them as PDF or images, upload them into the system, and search for them later.

The module should be named:

```text
My Books
```

Arabic menu title:

```text
كتبي
```

## Required database tables

Create only these tables:

```text
book_categories
books
book_files
```

Do not create unnecessary tables.

## Relationships

```text
BookCategory
 └── hasMany Books

Book
 ├── belongsTo BookCategory
 └── hasMany BookFiles

BookFile
 └── belongsTo Book
```

Since the system is for one user only, do not add `user_id` to the books table.

Authentication is still required to access the module.

## Migration: book_categories

Create:

```text
id
name
description nullable
is_active boolean default true
timestamps
softDeletes
```

The category name must be unique.

## Migration: books

Create:

```text
id
book_number nullable string
book_date nullable date
title string
author nullable string
publisher nullable string
edition nullable string
publish_year nullable integer
category_id nullable foreign key
document_type string default book
keywords nullable text
notes nullable longText
status string default archived
created_at
updated_at
deleted_at
```

The `document_type` can contain:

```text
book
official_document
report
personal_document
other
```

The `status` can contain:

```text
active
archived
```

Create indexes for:

```text
book_number
book_date
title
author
publish_year
category_id
document_type
status
```

Do not use database enums. Use normal string columns with Laravel validation.

## Migration: book_files

Create:

```text
id
book_id foreign key
original_name
stored_name
file_path
disk default local
mime_type
file_size unsignedBigInteger
file_type string
is_primary boolean default false
sort_order unsignedInteger default 1
timestamps
```

Allowed file types:

```text
pdf
image
other
```

Use cascade delete for book files when a book is permanently deleted.

## Models

Create:

```text
BookCategory
Book
BookFile
```

Add:

* `$fillable`
* `$casts`
* Eloquent relations
* SoftDeletes for Book and BookCategory
* Query scopes where useful

## Book model relations

```php
public function category(): BelongsTo

public function files(): HasMany

public function primaryFile(): HasOne
```

## BookCategory model relation

```php
public function books(): HasMany
```

## BookFile model relation

```php
public function book(): BelongsTo
```

Add these methods to BookFile:

```php
public function isPdf(): bool

public function isImage(): bool

public function existsInStorage(): bool

public function getFormattedFileSizeAttribute(): string
```

## Book search scope

Create:

```php
scopeSearch(Builder $query, ?string $search): Builder
```

Search inside:

```text
book_number
title
author
publisher
keywords
notes
```

Also create scopes:

```php
scopeByCategory()
scopeByDocumentType()
scopeBetweenDates()
scopeArchived()
```

## Livewire components

Create only these Livewire components:

```text
Books/BookIndex
Books/BookForm
Books/BookShow
Books/BookTrash
Books/CategoryManager
```

Use one reusable `BookForm` component for both create and edit.

## Routes

Add routes inside the existing authenticated middleware:

```php
Route::middleware('auth')->group(function () {
    Route::get('/my-books', BookIndex::class)
        ->name('books.index');

    Route::get('/my-books/create', BookForm::class)
        ->name('books.create');

    Route::get('/my-books/trash', BookTrash::class)
        ->name('books.trash');

    Route::get('/my-books/categories', CategoryManager::class)
        ->name('books.categories');

    Route::get('/my-books/{book}', BookShow::class)
        ->name('books.show');

    Route::get('/my-books/{book}/edit', BookForm::class)
        ->name('books.edit');
});
```

Place static routes such as `/create`, `/trash`, and `/categories` before `/{book}`.

## BookIndex requirements

Create a page listing all books.

Include:

* Search input
* Category filter
* Document type filter
* Status filter
* Date from
* Date to
* Reset filters
* Pagination

Display columns:

```text
Book number
Title
Author
Category
Document type
Book date
Files count
Status
Actions
```

Actions:

```text
View
Edit
Download primary file
Delete
```

Use Livewire pagination.

Use query string support for filters when appropriate.

Use eager loading and `withCount('files')`.

## BookForm requirements

Use the same Livewire component for create and update.

Fields:

```text
Book number
Book date
Title
Author
Publisher
Edition
Publish year
Category
Document type
Keywords
Notes
Status
Files
```

Features:

* Multiple file upload
* Accept PDF, JPG, JPEG, and PNG
* Maximum size 50 MB per file
* Preview selected file names before saving
* Remove selected file before submission
* Show existing files during edit
* Delete individual existing files
* Set one file as primary
* The first uploaded file should automatically become primary if no primary file exists
* Display validation errors using the existing theme alert and form styles

Use Livewire temporary uploads.

## BookShow requirements

Display:

* All book information
* Category
* Notes
* Keywords
* Uploaded files

For every file show:

```text
Original name
File size
File type
Upload date
Primary badge
Preview
Download
```

Preview:

* Show PDF inline using the existing browser PDF viewer or PDF.js if already installed
* Show images inline
* Do not expose private storage paths

## File storage

Store files privately inside:

```text
storage/app/private/books/{book_id}
```

Use UUID filenames.

Do not store files in the public folder.

Use Laravel Storage.

Create authorized routes or invokable controllers for:

```text
BookFilePreviewController
BookFileDownloadController
```

Routes:

```php
Route::get('/my-books/files/{file}/preview', BookFilePreviewController::class)
    ->name('books.files.preview');

Route::get('/my-books/files/{file}/download', BookFileDownloadController::class)
    ->name('books.files.download');
```

Preview should return files inline.

Download should force download using the original filename.

## File deletion rules

When deleting a BookFile:

* Delete the physical file
* Delete the database record
* If the deleted file was primary, make the first remaining file primary

When soft deleting a Book:

* Keep its files

When permanently deleting a Book:

* Delete all physical files
* Delete all file records
* Permanently delete the book

## Trash page

Show soft-deleted books.

Actions:

```text
Restore
Delete permanently
```

Use confirmation before permanent deletion.

## CategoryManager

Create a simple category management page.

Fields:

```text
name
description
is_active
```

Actions:

```text
Create
Edit
Delete
Restore
```

Show books count for every category.

Do not permanently delete a category that still contains books.

## Validation

Book validation:

```php
book_number => nullable|string|max:100
book_date => nullable|date
title => required|string|max:255
author => nullable|string|max:255
publisher => nullable|string|max:255
edition => nullable|string|max:100
publish_year => nullable|integer|min:1000|max:2100
category_id => nullable|exists:book_categories,id
document_type => required|in:book,official_document,report,personal_document,other
keywords => nullable|string
notes => nullable|string
status => required|in:active,archived
files => nullable|array
files.* => file|mimes:pdf,jpg,jpeg,png|max:51200
```

Category validation:

```php
name => required|string|max:255|unique:book_categories,name
description => nullable|string
is_active => boolean
```

Handle the unique category name correctly during update.

Use Arabic validation messages.

## Arabic labels

Use these labels:

```text
كتبي
إضافة كتاب
تعديل الكتاب
تفاصيل الكتاب
التصنيفات
سلة المحذوفات
رقم الكتاب
تاريخ الكتاب
عنوان الكتاب
المؤلف
الناشر
الطبعة
سنة النشر
التصنيف
نوع المستند
الكلمات المفتاحية
الملاحظات
الحالة
الملفات
إضافة ملفات
حفظ
تعديل
حذف
استرجاع
حذف نهائي
عرض
تحميل
بحث
إعادة تعيين
```

Document type labels:

```text
book = كتاب
official_document = كتاب رسمي
report = تقرير
personal_document = وثيقة شخصية
other = أخرى
```

Status labels:

```text
active = فعال
archived = مؤرشف
```

## Existing theme integration

This requirement is very important.

Before writing Blade views:

1. Inspect the current layout files.
2. Identify the layout used by existing Livewire pages.
3. Identify existing reusable UI components.
4. Identify sidebar structure.
5. Identify navigation active-state logic.
6. Identify existing table, form, card, badge, modal, button, alert, breadcrumb, pagination, and confirmation styles.
7. Follow the same folder conventions used by the project.

Integrate the module into the existing theme.

Add a sidebar menu item:

```text
كتبي
```

Submenu items:

```text
جميع الكتب
إضافة كتاب
التصنيفات
سلة المحذوفات
```

Use suitable existing theme icons.

Do not create a separate layout.

Do not copy the entire theme into the module.

Do not add duplicate CSS or JavaScript libraries.

Do not overwrite theme files unnecessarily.

Make minimal changes to the sidebar and routes.

Use the existing layout through the same method already used in the project, for example:

```php
#[Layout('components.layouts.app')]
```

or:

```php
return view(...)->layout('layouts.app');
```

Choose the method based on the existing project.

## Services

Create:

```text
app/Services/BookService.php
app/Services/BookFileService.php
```

BookService methods:

```php
public function create(array $data, array $files = []): Book

public function update(Book $book, array $data, array $files = []): Book

public function softDelete(Book $book): void

public function restore(int $id): Book

public function forceDelete(Book $book): void
```

BookFileService methods:

```php
public function store(Book $book, UploadedFile $file): BookFile

public function delete(BookFile $file): void

public function setPrimary(BookFile $file): void
```

Use database transactions.

Keep business logic outside Livewire render methods.

## Seeders

Create a simple `BookCategorySeeder` with:

```text
كتب
كتب رسمية
تقارير
وثائق شخصية
أخرى
```

Do not create a new user seeder because the project already has authentication.

## Tests

Create focused tests only for:

```text
Guest cannot access my books pages
Authenticated user can create a book
Book can contain multiple files
First file becomes primary
User can change primary file
User can search books
User can update a book
User can soft delete and restore a book
Permanent deletion removes physical files
Private files can only be previewed through authenticated routes
```

Use:

```php
Storage::fake('local');
```

## Code generation rules

* First inspect the existing project before modifying it.
* Reuse the current architecture and naming style.
* Generate complete working code.
* Do not generate pseudo-code.
* Do not create unnecessary classes.
* Do not add roles or permissions.
* Do not add `user_id`.
* Do not create a new theme.
* Do not redesign existing pages.
* Do not change unrelated features.
* Use Laravel conventions.
* Use typed relationship methods.
* Use eager loading.
* Avoid N+1 queries.
* Use transactions for database and file operations.
* Ensure file cleanup on failure.
* Make the UI responsive using the current theme.
* Keep the implementation simple and maintainable.

## Execution order

Perform the work in this order:

1. Inspect the current Laravel project and theme.
2. Report the layout and theme files that will be reused.
3. Create migrations.
4. Create models and relationships.
5. Create services.
6. Create controllers for preview and download.
7. Create Livewire components.
8. Create Blade views integrated with the existing theme.
9. Add routes.
10. Add sidebar links.
11. Create seeder.
12. Create tests.
13. Run migrations.
14. Run the tests.
15. Fix any errors found.
16. Provide a final summary of created and modified files.
17. make all system in arabic 

Do not ask me to choose a design. Use the existing theme already present in the project.
