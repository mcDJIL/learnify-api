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
        // Ambil kategori berdasarkan nama
        $frontendCategory = Category::where('name', 'Frontend Development')->first();
        $mobileCategory = Category::where('name', 'Mobile Development')->first();
        $mlCategory = Category::where('name', 'Machine Learning')->first();
        $uiuxCategory = Category::where('name', 'UI/UX Design')->first();
        $backendCategory = Category::where('name', 'Backend Development')->first();
        
        // Ambil instructor pertama
        $instructor = Instructor::first();

        // Jika belum ada kategori atau instructor, skip seeding
        if (!$instructor || !$frontendCategory || !$mobileCategory || !$mlCategory || !$uiuxCategory || !$backendCategory) {
            return;
        }

        $courses = [
            [
                'title' => 'Belajar React JS Dasar',
                'description' => 'Kursus lengkap untuk pemula React JS.',
                'short_description' => 'React JS untuk pemula',
                'thumbnail_url' => 'http://167.172.69.78/hology/images/react-js-thumbnail.png',
                'banner_url' => 'http://167.172.69.78/hology/images/react-js-thumbnail.png',
                'instructor_id' => $instructor->id,
                'category_id' => $frontendCategory->id, // Frontend Development
                'duration_hours' => 10,
                'rating' => 0,
                'total_students' => 0,
            ],
            [
                'title' => 'Belajar Mobile Development dengan Flutter',
                'description' => 'Pelajari pembuatan aplikasi mobile menggunakan Flutter.',
                'short_description' => 'Flutter untuk Pemula',
                'thumbnail_url' => 'http://167.172.69.78/hology/images/flutter-thumbnail.png',
                'banner_url' => 'http://167.172.69.78/hology/images/flutter-thumbnail.png',
                'instructor_id' => $instructor->id,
                'category_id' => $mobileCategory->id, // Mobile Development
                'duration_hours' => 0,
                'rating' => 0,
                'total_students' => 0,
            ],
            [
                'title' => 'Mengembangkan Kecerdasan Buatan dengan Python',
                'description' => 'Kursus untuk kamu yang tertarik akan pengembangan kecerdasan buatan menggunakan Python.',
                'short_description' => 'Kursus AI dengan Python',
                'thumbnail_url' => 'http://167.172.69.78/hology/images/ai-thumbnail.png',
                'banner_url' => 'http://167.172.69.78/hology/images/ai-thumbnail.png',
                'instructor_id' => $instructor->id,
                'category_id' => $mlCategory->id, // Machine Learning
                'duration_hours' => 0,
                'rating' => 0,
                'total_students' => 0,
            ],
            [
                'title' => 'Belajar Backend Development dengan Node.js',
                'description' => 'Kursus untuk kamu yang tertarik akan pengembangan backend menggunakan Node.js.',
                'short_description' => 'Kursus Backend dengan Node.js',
                'thumbnail_url' => 'http://167.172.69.78/hology/images/node-js-thumbnail.png',
                'banner_url' => 'http://167.172.69.78/hology/images/node-js-thumbnail.png',
                'instructor_id' => $instructor->id,
                'category_id' => $backendCategory->id, // Backend Development
                'duration_hours' => 0,
                'rating' => 0,
                'total_students' => 0,
            ],
            [
                'title' => 'Meningkatakan Kemampuan Desain UI/UX',
                'description' => 'Kursus untuk kamu yang tertarik akan pengembangan desain UI/UX.',
                'short_description' => 'Kursus Desain UI/UX',
                'thumbnail_url' => 'http://167.172.69.78/hology/images/ui-ux-thumbnail.png',
                'banner_url' => 'http://167.172.69.78/hology/images/ui-ux-thumbnail.png',
                'instructor_id' => $instructor->id,
                'category_id' => $uiuxCategory->id, // UI/UX Design
                'duration_hours' => 0,
                'rating' => 0,
                'total_students' => 0,
            ],
        ];

        foreach ($courses as $course) {
            Course::create(array_merge(['id' => (string) Str::uuid()], $course));
        }
    }
}