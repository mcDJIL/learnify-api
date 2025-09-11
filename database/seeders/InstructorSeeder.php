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
                'job' => 'Senior Developer',
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Siti Aminah',
                'job' => 'Web Developer',
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Rizky Pratama',
                'job' => 'Junior Developer',
            ]
        ];

        foreach ($instructors as $instructor) {
            Instructor::create($instructor);
        }
    }
}