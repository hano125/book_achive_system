<?php

namespace App\Models;

use Database\Factories\BookCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'description', 'is_active'])]
class BookCategory extends Model
{
    /** @use HasFactory<BookCategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'is_active' => true,
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
