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
use App\Models\UserQuizAnswer;
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
        $rating = CourseReview::where('course_id', $id)->avg('rating');
        $rating_count = CourseReview::where('course_id', $id)->count();

        // Ambil progress user (hanya satu per user per course)
        $progress = $course->progress->first();

        // Cek apakah user sudah daftar course
        $isEnrolled = !is_null($progress);

        // Ambil active lesson: lesson order paling kecil dan belum completed
        $activeLesson = $course->lessons
            ->orderBy('lesson_order', 'asc')
            ->first(function ($lesson) use ($user) {
                $progress = $lesson->progress()->where('user_id', $user->id)->first();
                return !$progress || $progress->completion_percentage < 100;
            });

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
                'instructor' => $course->instructor,
                'is_enrolled' => $isEnrolled,
                'active_lesson' => $activeLesson
            ]
        ]);
    }

    /**
     * Start the first lesson of a course
     */
    public function startLesson(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|uuid|exists:lessons,id',
        ]);
        $user = Auth::user();

        $lesson = Lesson::with('course')->findOrFail($request->lesson_id);

        // Ambil semua lesson di course ini, urutkan
        $allLessons = Lesson::where('course_id', $lesson->course_id)
            ->orderBy('lesson_order', 'asc')
            ->get();

        // Cari index/urutan lesson sekarang
        $currentIndex = $allLessons->search(function ($item) use ($lesson) {
            return $item->id === $lesson->id;
        });

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
            'message' => 'Mulai lesson',
            'data' => [
                'lesson' => $lesson,
                'progress' => $progress,
                'lesson_order' => $lesson->lesson_order,
                'lesson_index' => $currentIndex + 1, // urutan dimulai dari 1
                'total_lessons' => $allLessons->count()
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

        // Update quest progress untuk lesson
        QuestController::updateUserQuestProgress($user->id, 'lesson');

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
            'lesson_id' => 'required|uuid|exists:lessons,id',
            'answers' => 'required|array'
        ]);
        $user = Auth::user();

        // Ambil semua quiz di lesson ini
        $quizzes = Quiz::where('lesson_id', $request->lesson_id)
            ->orderBy('quiz_order', 'asc')
            ->get();

        $answers = $request->answers; // array: [quiz_id => user_answer, ...]

        $score = 0;
        $maxScore = $quizzes->count() * 100;

        foreach ($quizzes as $quiz) {
            $userAnswer = $answers[$quiz->id] ?? null;

            UserQuizAnswer::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'answer' => $userAnswer
            ]);

            // Hitung skor
            if ($userAnswer && $userAnswer == $quiz->correct_answer) {
                $score += 100;
            }

            // Simpan attempt per quiz (optional, jika ingin tracking per soal)
            QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => $userAnswer == $quiz->correct_answer ? 100 : 0
            ]);
        }

        // Update quest progress untuk quiz
        QuestController::updateUserQuestProgress($user->id, 'quiz');

        // Ambil lesson berikutnya
        $lesson = Lesson::findOrFail($request->lesson_id);
        $nextLesson = Lesson::where('course_id', $lesson->course_id)
            ->where('lesson_order', '>', $lesson->lesson_order)
            ->orderBy('lesson_order', 'asc')
            ->first();

        // Ambil leaderboard untuk semua quiz di lesson ini (total skor per user)
        $leaderboard = QuizAttempt::selectRaw('user_id, SUM(score) as total_score')
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->groupBy('user_id')
            ->orderBy('total_score', 'desc')
            ->with('user')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => $nextLesson ? 'Lanjut ke lesson berikutnya' : 'Course selesai',
            'data' => [
                'score' => $score,
                'max_score' => $maxScore,
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

    /**
     * Complete a course
     */
    public function completeCourse(Request $request)
    {
        $request->validate([
            'course_id' => 'required|uuid|exists:courses,id',
        ]);

        $user = Auth::user();

        $progress = CourseProgress::where('user_id', $user->id)
            ->where('course_id', $request->course_id)
            ->first();

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'Progress kursus tidak ditemukan.'
            ], 404);
        }

        $progress->update([
            'completion_percentage' => 100,
            'is_completed' => true
        ]);

        QuestController::updateUserQuestProgress($user->id, 'course');

        return response()->json([
            'success' => true,
            'message' => 'Kursus berhasil diselesaikan.',
            'data' => $progress
        ]);
    }

    /**
     * Get leaderboard for a quiz
     */
    public function leaderboardQuiz(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        // Ambil semua lesson di course ini
        $lessons = Lesson::where('course_id', $course->id)->pluck('id');

        // Ambil semua quiz dari semua lesson di course ini
        $quizzes = Quiz::whereIn('lesson_id', $lessons)->pluck('id');

        // Ambil leaderboard (top 10 skor tertinggi dari semua quiz di course ini, total skor per user)
        $leaderboard = QuizAttempt::selectRaw('user_id, SUM(score) as total_score')
            ->whereIn('quiz_id', $quizzes)
            ->groupBy('user_id')
            ->orderBy('total_score', 'desc')
            ->with('user')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Leaderboard quiz berhasil diambil',
            'data' => $leaderboard
        ]);
    }
}
