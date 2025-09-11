<?php

namespace Database\Seeders;

use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instructors = [
            [
                'id' => (string) Str::uuid(),
                'name' => 'Budi Santoso',
                'bio' => 'Instruktur Laravel dan PHP berpengalaman.',
                'photo_url' => 'https://dummyimage.com/200x200/000/fff&text=Budi'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Siti Aminah',
                'bio' => 'Ahli Web Development dan UI/UX.',
                'photo_url' => 'https://dummyimage.com/200x200/000/fff&text=Siti'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Rizky Pratama',
                'bio' => 'Instruktur Pemrograman Dasar dan Mobile.',
                'photo_url' => 'https://dummyimage.com/200x200/000/fff&text=Rizky'
            ]
        ];

        foreach ($instructors as $instructor) {
            Instructor::create($instructor);
        }
    }
}