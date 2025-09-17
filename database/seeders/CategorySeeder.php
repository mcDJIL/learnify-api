<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'id' => (string) Str::uuid(),
                'name' => 'Backend Development'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Frontend Development'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Mobile Development'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'UI/UX Design'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Machine Learning'
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}