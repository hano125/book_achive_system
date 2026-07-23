<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'book_number', 'book_date', 'subject', 'notes',
])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'document_type' => 'book',
        'status' => 'archived',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class)->withTrashed();
    }

    public function files(): HasMany
    {
        return $this->hasMany(BookFile::class)->orderBy('sort_order');
    }

    public function primaryFile(): HasOne
    {
        return $this->hasOne(BookFile::class)->where('is_primary', true);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $term = '%'.trim($search).'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('book_number', 'like', $term)
                ->orWhere('subject', 'like', $term)
                ->orWhere('notes', 'like', $term);
        });
    }

    public function scopeByCategory(Builder $query, int|string|null $categoryId): Builder
    {
        return filled($categoryId) ? $query->where('category_id', $categoryId) : $query;
    }

    public function scopeByDocumentType(Builder $query, ?string $documentType): Builder
    {
        return filled($documentType) ? $query->where('document_type', $documentType) : $query;
    }

    public function scopeBetweenDates(Builder $query, ?string $dateFrom, ?string $dateTo): Builder
    {
        return $query
            ->when($dateFrom, fn (Builder $query, string $date): Builder => $query->whereDate('book_date', '>=', $date))
            ->when($dateTo, fn (Builder $query, string $date): Builder => $query->whereDate('book_date', '<=', $date));
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    protected function casts(): array
    {
        return [
            'book_date' => 'date',
            'quantity' => 'integer',
            'publish_year' => 'integer',
        ];
    }
}
