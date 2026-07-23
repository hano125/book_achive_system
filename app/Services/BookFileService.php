<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class BookFileService
{
    public function store(Book $book, UploadedFile $file): BookFile
    {
        $disk = 'local';
        $extension = Str::lower($file->getClientOriginalExtension());
        $storedName = Str::uuid().($extension ? '.'.$extension : '');
        $directory = 'books/'.$book->getKey();
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $fileSize = (int) $file->getSize();
        $fileType = $this->detectFileType($mimeType);
        $path = $file->storeAs($directory, $storedName, $disk);

        if ($path === false) {
            throw new RuntimeException('تعذر حفظ الملف.');
        }

        try {
            return DB::transaction(function () use ($book, $disk, $storedName, $path, $originalName, $mimeType, $fileSize, $fileType): BookFile {
                $book = Book::query()->lockForUpdate()->findOrFail($book->getKey());
                $hasPrimary = $book->files()->where('is_primary', true)->exists();
                $sortOrder = ((int) $book->files()->max('sort_order')) + 1;

                return $book->files()->create([
                    'original_name' => $originalName,
                    'stored_name' => $storedName,
                    'file_path' => $path,
                    'disk' => $disk,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                    'file_type' => $fileType,
                    'is_primary' => ! $hasPrimary,
                    'sort_order' => $sortOrder,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }
    }

    public function delete(BookFile $file): void
    {
        DB::transaction(function () use ($file): void {
            $file = BookFile::query()->lockForUpdate()->findOrFail($file->getKey());
            $bookId = $file->book_id;
            $wasPrimary = $file->is_primary;
            $disk = $file->disk;
            $path = $file->file_path;

            $file->delete();

            if ($wasPrimary) {
                BookFile::query()
                    ->where('book_id', $bookId)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->first()
                    ?->update(['is_primary' => true]);
            }

            DB::afterCommit(fn () => Storage::disk($disk)->delete($path));
        });
    }

    public function setPrimary(BookFile $file): void
    {
        DB::transaction(function () use ($file): void {
            $file = BookFile::query()->lockForUpdate()->findOrFail($file->getKey());

            BookFile::query()
                ->where('book_id', $file->book_id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);

            $file->update(['is_primary' => true]);
        });
    }

    private function detectFileType(string $mimeType): string
    {
        if ($mimeType === 'application/pdf') {
            return 'pdf';
        }

        if (Str::startsWith($mimeType, 'image/')) {
            return 'image';
        }

        return 'other';
    }
}
