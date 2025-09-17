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
        // Ambil course React JS
        $reactCourse = Course::where('title', 'Belajar React JS Dasar')->first();
        if (!$reactCourse) return;

        // Seeder Lesson untuk React JS
        $lessons = [
            [
                'id' => (string) Str::uuid(),
                'course_id' => $reactCourse->id,
                'title' => 'Pengenalan React JS',
                'video_url' => 'https://youtu.be/AYb7l6XDlPo?si=YA_BCzHw3AbyqB65',
                'duration_minutes' => 20,
                'lesson_order' => 1,
                'is_completed' => '0'
            ],
            [
                'id' => (string) Str::uuid(),
                'course_id' => $reactCourse->id,
                'title' => 'Component dan JSX',
                'video_url' => 'https://youtu.be/TK451YJLaZg?si=KJvASi1t1be6JFzc',
                'duration_minutes' => 25,
                'lesson_order' => 2,
                'is_completed' => '0'
            ],
            [
                'id' => (string) Str::uuid(),
                'course_id' => $reactCourse->id,
                'title' => 'State dan Props',
                'video_url' => 'https://youtu.be/BgapZ6Cqy3s?si=skSJviETHf8Ciyov',
                'duration_minutes' => 30,
                'lesson_order' => 3,
                'is_completed' => '0'
            ],
            [
                'id' => (string) Str::uuid(),
                'course_id' => $reactCourse->id,
                'title' => 'Event Handling',
                'video_url' => 'https://youtu.be/jgcyqcF6n9U?si=BjgX6RpSWVa9rxNA',
                'duration_minutes' => 22,
                'lesson_order' => 4,
                'is_completed' => '0'
            ],
            [
                'id' => (string) Str::uuid(),
                'course_id' => $reactCourse->id,
                'title' => 'React Hooks',
                'video_url' => 'https://youtu.be/kRT1RiDklqc?si=wIEWzuH6GP6Z5bpL',
                'duration_minutes' => 35,
                'lesson_order' => 5,
                'is_completed' => '0'
            ]
        ];

        foreach ($lessons as $lessonData) {
            $lesson = Lesson::create($lessonData);

            // Seeder Quiz untuk setiap lesson React JS
            $quizzes = [];
            
            if ($lesson->lesson_order == 1) {
                // Quiz untuk Lesson 1: Pengenalan React JS
                $quizzes = [
                    [
                        'id' => (string) Str::uuid(),
                        'lesson_id' => $lesson->id,
                        'question' => 'Apa itu React JS?',
                        'choices' => json_encode(['JavaScript Library', 'Database', 'CSS Framework', 'Web Server']),
                        'correct_answer' => 'JavaScript Library',
                        'quiz_order' => 1
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'lesson_id' => $lesson->id,
                        'question' => 'React dikembangkan oleh?',
                        'choices' => json_encode(['Google', 'Facebook', 'Microsoft', 'Apple']),
                        'correct_answer' => 'Facebook',
                        'quiz_order' => 2
                    ]
                ];
            } elseif ($lesson->lesson_order == 2) {
                // Quiz untuk Lesson 2: Component dan JSX
                $quizzes = [
                    [
                        'id' => (string) Str::uuid(),
                        'lesson_id' => $lesson->id,
                        'question' => 'Apa itu JSX?',
                        'choices' => json_encode(['JavaScript XML', 'Java Syntax', 'JSON Extended', 'JavaScript Extension']),
                        'correct_answer' => 'JavaScript XML',
                        'quiz_order' => 1
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'lesson_id' => $lesson->id,
                        'question' => 'Bagaimana cara membuat component di React?',
                        'choices' => json_encode(['function atau class', 'hanya function', 'hanya class', 'dengan HTML']),
                        'correct_answer' => 'function atau class',
                        'quiz_order' => 2
                    ]
                ];
            } elseif ($lesson->lesson_order == 3) {
                // Quiz untuk Lesson 3: State dan Props
                $quizzes = [
                    [
                        'id' => (string) Str::uuid(),
                        'lesson_id' => $lesson->id,
                        'question' => 'Apa itu State di React?',
                        'choices' => json_encode(['Data internal component', 'Data dari parent', 'CSS styling', 'HTML element']),
                        'correct_answer' => 'Data internal component',
                        'quiz_order' => 1
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'lesson_id' => $lesson->id,
                        'question' => 'Apa itu Props di React?',
                        'choices' => json_encode(['Data dari parent component', 'Data internal', 'CSS properties', 'HTML attributes']),
                        'correct_answer' => 'Data dari parent component',
                        'quiz_order' => 2
                    ]
                ];
            } elseif ($lesson->lesson_order == 4) {
                // Quiz untuk Lesson 4: Event Handling
                $quizzes = [
                    [
                        'id' => (string) Str::uuid(),
                        'lesson_id' => $lesson->id,
                        'question' => 'Bagaimana cara handle click event di React?',
                        'choices' => json_encode(['onClick={handleClick}', 'onclick="handleClick"', 'onPress={handleClick}', 'click={handleClick}']),
                        'correct_answer' => 'onClick={handleClick}',
                        'quiz_order' => 1
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'lesson_id' => $lesson->id,
                        'question' => 'Event object di React adalah?',
                        'choices' => json_encode(['SyntheticEvent', 'NativeEvent', 'DOMEvent', 'ReactEvent']),
                        'correct_answer' => 'SyntheticEvent',
                        'quiz_order' => 2
                    ]
                ];
            } elseif ($lesson->lesson_order == 5) {
                // Quiz untuk Lesson 5: React Hooks
                $quizzes = [
                    [
                        'id' => (string) Str::uuid(),
                        'lesson_id' => $lesson->id,
                        'question' => 'Hook untuk manage state adalah?',
                        'choices' => json_encode(['useState', 'useEffect', 'useContext', 'useReducer']),
                        'correct_answer' => 'useState',
                        'quiz_order' => 1
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'lesson_id' => $lesson->id,
                        'question' => 'Hook untuk side effect adalah?',
                        'choices' => json_encode(['useEffect', 'useState', 'useContext', 'useMemo']),
                        'correct_answer' => 'useEffect',
                        'quiz_order' => 2
                    ]
                ];
            }

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
                'quest_type' => 'daily'
            ],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Selesaikan 1 Quiz',
                'target_value' => 1,
                'xp_reward' => 50,
                'quest_type' => 'daily'
            ],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Selesaikan 1 Lesson',
                'target_value' => 1,
                'xp_reward' => 50,
                'quest_type' => 'daily'
            ],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Tonton 3 Video',
                'target_value' => 3,
                'xp_reward' => 150,
                'quest_type' => 'weekly'
            ],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Selesaikan 2 Kursus',
                'target_value' => 2,
                'xp_reward' => 200,
                'quest_type' => 'weekly'
            ],
        ];

        foreach ($quests as $questData) {
            Quests::create($questData);
        }
    }
}
