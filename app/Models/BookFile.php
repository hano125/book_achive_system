<?php

namespace App\Models;

use Database\Factories\BookFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

#[Fillable([
    'book_id', 'original_name', 'stored_name', 'file_path', 'disk', 'mime_type',
    'file_size', 'file_type', 'is_primary', 'sort_order',
])]
class BookFile extends Model
{
    /** @use HasFactory<BookFileFactory> */
    use HasFactory;

    protected $attributes = [
        'disk' => 'local',
        'is_primary' => false,
        'sort_order' => 1,
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function isPdf(): bool
    {
        return $this->file_type === 'pdf';
    }

    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    public function existsInStorage(): bool
    {
        return Storage::disk($this->disk)->exists($this->file_path);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        return Number::fileSize($this->file_size);
    }

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
