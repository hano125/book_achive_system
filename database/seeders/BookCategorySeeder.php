<?php

namespace Database\Seeders;

use App\Models\BookCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect(['كتب', 'كتب رسمية', 'تقارير', 'وثائق شخصية', 'أخرى'])
            ->each(fn (string $name) => BookCategory::withTrashed()->firstOrCreate(
                ['name' => $name],
                ['is_active' => true],
            ));
    }
}
