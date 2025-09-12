<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\CourseReview;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Get all courses or by category, with filter (popular, terbaru, terlama)
     */
    public function index(Request $request)
    {
        $categoryId = $request->get('category_id');
        $filter = $request->get('filter', 'all'); // all, popular, terbaru, terlama

        $query = Course::with(['instructor', 'category']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Filter sorting
        switch ($filter) {
            case 'popular':
                $query->orderBy('total_students', 'desc')->orderBy('rating', 'desc');
                break;
            case 'terbaru':
                $query->orderBy('created_at', 'desc');
                break;
            case 'terlama':
                $query->orderBy('created_at', 'asc');
                break;
            case 'all':
            default:
                $query->orderBy('rating', 'desc')->orderBy('total_students', 'desc');
                break;
        }

        $courses = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar kursus berhasil diambil',
            'data' => $courses
        ]);
    }

    /**
     * Search courses by keyword (title, description, instructor, category)
     */
    public function search(Request $request)
    {
        $searchTerm = $request->get('q', '');
        $filter = $request->get('filter', 'all'); // all, popular, terbaru, terlama

        if (empty($searchTerm)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata kunci pencarian diperlukan'
            ], 400);
        }

        $query = Course::with(['instructor', 'category'])
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('short_description', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('instructor', function ($q) use ($searchTerm) {
                          $q->where('name', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('category', function ($q) use ($searchTerm) {
                          $q->where('name', 'LIKE', "%{$searchTerm}%");
                      });
            });

        // Filter sorting
        switch ($filter) {
            case 'popular':
                $query->orderBy('total_students', 'desc')->orderBy('rating', 'desc');
                break;
            case 'terbaru':
                $query->orderBy('created_at', 'desc');
                break;
            case 'terlama':
                $query->orderBy('created_at', 'asc');
                break;
            case 'all':
            default:
                $query->orderBy('rating', 'desc')->orderBy('total_students', 'desc');
                break;
        }

        $courses = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Hasil pencarian kursus berhasil diambil',
            'data' => $courses
        ]);
    }

    /**
     * Get course detail by ID
     */
    public function show($id)
    {
        $user = Auth::user();

        $course = Course::with([
            'instructor',
            'category',
            'lessons',
            'progress' => function ($query) use ($user) {
                if ($user) {
                    $query->where('user_id', $user->id);
                }
            }
        ])->findOrFail($id);

        // Ambil rating rata-rata dan jumlah review
        $rating = CourseReview::where('course_id', $id)
            ->avg('rating');
        $rating_count = CourseReview::where('course_id', $id)
            ->count();

        // Ambil progress user (hanya satu per user per course)
        $progress = $course->progress->first();

        return response()->json([
            'success' => true,
            'message' => 'Detail kursus berhasil diambil',
            'data' => [
                'course' => $course,
                'progress' => $progress,
                'lessons' => $course->lessons,
                'rating' => [
                    'average' => $rating ? round($rating, 2) : null,
                    'count' => $rating_count
                ],
                'instructor' => $course->instructor
            ]
        ]);
    }

    /**
     * Start the first lesson of a course
     */
    public function startLesson(Request $request)
    {
        $request->validate([
            'course_id' => 'required|uuid|exists:courses,id',
        ]);
        $user = Auth::user();

        // Ambil lesson pertama (lesson_order paling kecil)
        $lesson = Lesson::where('course_id', $request->course_id)
            ->orderBy('lesson_order', 'asc')
            ->first();

        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson tidak ditemukan'
            ], 404);
        }

        // Buat progress lesson jika belum ada
        $progress = LessonProgress::firstOrCreate([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id
        ], [
            'completion_percentage' => 0,
            'watch_time_seconds' => 0,
            'is_completed' => '0',
            'last_watched_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mulai lesson pertama',
            'data' => [
                'lesson' => $lesson,
                'progress' => $progress
            ]
        ]);
    }

    /**
     * Complete a lesson and optionally proceed to the quiz
     */
    public function completeLesson(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|uuid|exists:lessons,id',
        ]);
        $user = Auth::user();

        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $request->lesson_id)
            ->first();

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'Progress lesson tidak ditemukan'
            ], 404);
        }

        $progress->update([
            'completion_percentage' => 100,
            'is_completed' => '1',
            'last_watched_at' => now()
        ]);

        // Ambil quiz untuk lesson ini
        $quiz = Quiz::where('lesson_id', $request->lesson_id)
            ->orderBy('quiz_order', 'asc')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Lesson selesai, lanjut ke quiz',
            'data' => [
                'quiz' => $quiz
            ]
        ]);
    }

    /**
     * Complete a quiz and proceed to the next lesson
     */
    public function completeQuiz(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|uuid|exists:quizzes,id',
            'score' => 'required|numeric'
        ]);
        $user = Auth::user();

        // Simpan attempt quiz
        QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $request->quiz_id,
            'score' => $request->score
        ]);

        // Ambil lesson terkait quiz ini
        $lesson = Lesson::where('id', function($q) use ($request) {
                $q->select('lesson_id')->from('quizzes')->where('id', $request->quiz_id);
            })->first();

        // Ambil lesson berikutnya
        $nextLesson = Lesson::where('course_id', $lesson->course_id)
            ->where('lesson_order', '>', $lesson->lesson_order)
            ->orderBy('lesson_order', 'asc')
            ->first();

        // Ambil leaderboard quiz (top 10 skor tertinggi)
        $leaderboard = QuizAttempt::with('user')
            ->where('quiz_id', $request->quiz_id)
            ->orderBy('score', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => $nextLesson ? 'Lanjut ke lesson berikutnya' : 'Course selesai',
            'data' => [
                'next_lesson' => $nextLesson,
                'leaderboard' => $leaderboard
            ]
        ]);
    }

    /**
     * Enroll in a course
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'course_id' => 'required|uuid|exists:courses,id',
        ]);

        $user = Auth::user();

        // Cek apakah user sudah pernah daftar course ini
        $exists = CourseProgress::where('user_id', $user->id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah terdaftar di kursus ini.'
            ], 409);
        }

        $progress = CourseProgress::create([
            'user_id' => $user->id,
            'course_id' => $request->course_id,
            'completion_percentage' => 0,
            'is_completed' => '0'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendaftar kursus.',
            'data' => $progress
        ], 201);
    }
}
