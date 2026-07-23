<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BookFileDownloadController;
use App\Http\Controllers\BookFilePreviewController;
use App\Livewire\Books\BookForm;
use App\Livewire\Books\BookIndex;
use App\Livewire\Books\BookShow;
use App\Livewire\Books\BookTrash;
use App\Livewire\Books\CategoryManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('books.index')
        : view('auth.login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::redirect('/my_books', '/my-books');
    Route::livewire('/my-books', BookIndex::class)->name('books.index');
    Route::livewire('/my-books/create', BookForm::class)->name('books.create');
    Route::livewire('/my-books/trash', BookTrash::class)->name('books.trash');
    Route::livewire('/my-books/categories', CategoryManager::class)->name('books.categories');
    Route::get('/my-books/files/{file}/preview', BookFilePreviewController::class)->name('books.files.preview');
    Route::get('/my-books/files/{file}/download', BookFileDownloadController::class)->name('books.files.download');
    Route::livewire('/my-books/{book}', BookShow::class)->name('books.show');
    Route::livewire('/my-books/{book}/edit', BookForm::class)->name('books.edit');
});
