<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Get all courses or by category
     */
    public function index(Request $request)
    {
        $categoryId = $request->get('category_id');

        $query = Course::with(['instructor', 'category'])
            ->orderBy('rating', 'desc')
            ->orderBy('total_students', 'desc');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
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

        if (empty($searchTerm)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata kunci pencarian diperlukan'
            ], 400);
        }

        $courses = Course::with(['instructor', 'category'])
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
            })
            ->orderBy('rating', 'desc')
            ->orderBy('total_students', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Hasil pencarian kursus berhasil diambil',
            'data' => $courses
        ]);
    }
}
