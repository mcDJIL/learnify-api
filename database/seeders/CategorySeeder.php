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
                'name' => 'Programming'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Web Development'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Mobile Development'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Data Science'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'UI/UX Design'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Digital Marketing'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Business'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Photography'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Language Learning'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Music'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}