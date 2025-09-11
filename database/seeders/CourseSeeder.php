<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Category;
use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil satu kategori dan instruktur secara acak
        $category = Category::first();
        $instructor = Instructor::first();

        // Jika belum ada, skip seeding
        if (!$category || !$instructor) {
            return;
        }

        $courses = [
            [
                'title' => 'Belajar Laravel Dasar',
                'description' => 'Kursus lengkap untuk pemula Laravel.',
                'short_description' => 'Laravel untuk pemula',
                'thumbnail_url' => 'https://dummyimage.com/600x400/000/fff&text=Laravel',
                'banner_url' => 'https://dummyimage.com/1200x400/000/fff&text=Laravel+Banner',
                'instructor_id' => $instructor->id,
                'category_id' => $category->id,
                'duration_hours' => 10,
                'rating' => 4.8,
                'total_students' => 120,
            ],
            [
                'title' => 'Web Development dengan PHP',
                'description' => 'Pelajari pembuatan website dinamis menggunakan PHP.',
                'short_description' => 'PHP Web Development',
                'thumbnail_url' => 'https://dummyimage.com/600x400/000/fff&text=PHP',
                'banner_url' => 'https://dummyimage.com/1200x400/000/fff&text=PHP+Banner',
                'instructor_id' => $instructor->id,
                'category_id' => $category->id,
                'duration_hours' => 8,
                'rating' => 4.5,
                'total_students' => 90,
            ],
            [
                'title' => 'Dasar-dasar Pemrograman',
                'description' => 'Kursus pengantar pemrograman untuk semua kalangan.',
                'short_description' => 'Pemrograman Dasar',
                'thumbnail_url' => 'https://dummyimage.com/600x400/000/fff&text=Programming',
                'banner_url' => 'https://dummyimage.com/1200x400/000/fff&text=Programming+Banner',
                'instructor_id' => $instructor->id,
                'category_id' => $category->id,
                'duration_hours' => 12,
                'rating' => 4.7,
                'total_students' => 150,
            ]
        ];

        foreach ($courses as $course) {
            Course::create(array_merge(['id' => (string) Str::uuid()], $course));
        }
    }
}