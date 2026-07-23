<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookFile>
 */
class BookFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $storedName = fake()->uuid().'.pdf';

        return [
            'book_id' => Book::factory(),
            'original_name' => fake()->word().'.pdf',
            'stored_name' => $storedName,
            'file_path' => 'books/1/'.$storedName,
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1024, 1024 * 1024),
            'file_type' => 'pdf',
            'is_primary' => false,
            'sort_order' => 1,
        ];
    }
}
