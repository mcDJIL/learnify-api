<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Quests;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LessonQuizQuestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil satu course untuk relasi lesson
        $course = Course::first();
        if (!$course) return;

        // Seeder Lesson
        $lessons = [
            [
                'id' => (string) Str::uuid(),
                'course_id' => $course->id,
                'title' => 'Pengenalan Laravel',
                'video_url' => 'https://dummyvideo.com/laravel1.mp4',
                'duration_minutes' => 15,
                'lesson_order' => 1,
                'is_completed' => false
            ],
            [
                'id' => (string) Str::uuid(),
                'course_id' => $course->id,
                'title' => 'Routing dan Controller',
                'video_url' => 'https://dummyvideo.com/laravel2.mp4',
                'duration_minutes' => 20,
                'lesson_order' => 2,
                'is_completed' => false
            ]
        ];

        foreach ($lessons as $lessonData) {
            $lesson = Lesson::create($lessonData);

            // Seeder Quiz untuk setiap lesson
            $quizzes = [
                [
                    'id' => (string) Str::uuid(),
                    'lesson_id' => $lesson->id,
                    'question' => 'Apa itu Laravel?',
                    'choices' => json_encode(['Framework PHP', 'Database', 'Library JavaScript', 'Web Server']),
                    'correct_answer' => 'Framework PHP',
                    'quiz_order' => 1
                ],
                [
                    'id' => (string) Str::uuid(),
                    'lesson_id' => $lesson->id,
                    'question' => 'Fungsi routing di Laravel?',
                    'choices' => json_encode(['Mengatur tampilan', 'Mengatur alur request', 'Menyimpan data', 'Menghubungkan ke database']),
                    'correct_answer' => 'Mengatur alur request',
                    'quiz_order' => 2
                ]
            ];

            foreach ($quizzes as $quizData) {
                Quiz::create($quizData);
            }
        }

        // Seeder Quests
        $quests = [
            [
                'id' => (string) Str::uuid(),
                'title' => 'Selesaikan 1 Kursus',
                'target_value' => 1,
                'xp_reward' => 100,
                'quest_type' => 'course_complete'
            ],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Selesaikan 5 Quiz',
                'target_value' => 5,
                'xp_reward' => 50,
                'quest_type' => 'quiz_complete'
            ],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Tonton 3 Video',
                'target_value' => 3,
                'xp_reward' => 30,
                'quest_type' => 'lesson_watch'
            ]
        ];

        foreach ($quests as $questData) {
            Quests::create($questData);
        }
    }
}
