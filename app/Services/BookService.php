<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BookService
{
    public function __construct(private BookFileService $bookFileService) {}

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function create(array $data, array $files = []): Book
    {
        $storedFiles = [];

        try {
            return DB::transaction(function () use ($data, $files, &$storedFiles): Book {
                $book = Book::query()->create($data);

                foreach ($files as $file) {
                    $storedFiles[] = $this->bookFileService->store($book, $file);
                }

                return $book->load(['category', 'files']);
            });
        } catch (Throwable $exception) {
            $this->cleanupStoredFiles($storedFiles);

            throw $exception;
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function update(Book $book, array $data, array $files = []): Book
    {
        $storedFiles = [];

        try {
            return DB::transaction(function () use ($book, $data, $files, &$storedFiles): Book {
                $book->update($data);

                foreach ($files as $file) {
                    $storedFiles[] = $this->bookFileService->store($book, $file);
                }

                return $book->refresh()->load(['category', 'files']);
            });
        } catch (Throwable $exception) {
            $this->cleanupStoredFiles($storedFiles);

            throw $exception;
        }
    }

    public function softDelete(Book $book): void
    {
        $book->delete();
    }

    public function restore(int $id): Book
    {
        return DB::transaction(function () use ($id): Book {
            $book = Book::onlyTrashed()->findOrFail($id);
            $book->restore();

            return $book->refresh();
        });
    }

    public function forceDelete(Book $book): void
    {
        DB::transaction(function () use ($book): void {
            $book = Book::withTrashed()->with('files')->lockForUpdate()->findOrFail($book->getKey());
            $storedFiles = $book->files->map->only(['disk', 'file_path']);

            $book->forceDelete();

            DB::afterCommit(function () use ($storedFiles): void {
                $storedFiles->each(
                    fn (array $file) => Storage::disk($file['disk'])->delete($file['file_path'])
                );
            });
        });
    }

    /**
     * @param  array<int, BookFile>  $storedFiles
     */
    private function cleanupStoredFiles(array $storedFiles): void
    {
        foreach ($storedFiles as $storedFile) {
            Storage::disk($storedFile->disk)->delete($storedFile->file_path);
        }
    }
}
