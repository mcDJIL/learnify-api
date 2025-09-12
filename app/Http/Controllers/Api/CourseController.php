<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\Request;

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
        $user = auth('sanctum')->user();

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
}
