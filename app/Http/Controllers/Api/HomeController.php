<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\UserCategoryPreferences;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Get home data for mobile app
     */
    public function index()
    {
        try {
            $user = Auth::user();

            // Get all categories
            $categories = Category::all();

            // Get user's preferred categories
            $userPreferences = UserCategoryPreferences::where('user_id', $user->id)
                ->pluck('category_id')
                ->toArray();

            // Get recommended courses based on user preferences
            $recommendedCourses = collect();
            if (!empty($userPreferences)) {
                $recommendedCourses = Course::with(['instructor', 'category'])
                    ->whereIn('category_id', $userPreferences)
                    ->orderBy('rating', 'desc')
                    ->limit(10)
                    ->get();
            }

            // If no preferences or not enough recommended courses, get popular courses
            // if ($recommendedCourses->count() < 5) {
            //     $additionalCourses = Course::with(['instructor', 'category'])
            //         ->whereNotIn('id', $recommendedCourses->pluck('id'))
            //         ->orderBy('total_students', 'desc')
            //         ->orderBy('rating', 'desc')
            //         ->limit(10 - $recommendedCourses->count())
            //         ->get();
                
            //     $recommendedCourses = $recommendedCourses->merge($additionalCourses);
            // }
            
            // Get courses user is enrolled in (has progress)
            $enrolledCourses = Course::with(['instructor', 'category'])
                ->whereHas('progress', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();

            // Get continuing courses (enrolled but not completed)
            $continuingCourses = $enrolledCourses->filter(function ($course) {
                // Assuming you have completion status in CourseProgress
                return true; // You can add logic here when CourseProgress model is complete
            });

            return response()->json([
                'success' => true,
                'message' => 'Home data retrieved successfully',
                'data' => [
                    'categories' => $categories,
                    'recommended_courses' => $recommendedCourses,
                    'enrolled_courses' => $enrolledCourses,
                    'user_preferences' => $userPreferences
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve home data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search courses (recommended, enrolled, and all available)
     */
    public function search(Request $request)
    {
        try {
            $user = Auth::user();
            $searchTerm = $request->get('q', '');

            if (empty($searchTerm)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search term is required'
                ], 400);
            }

            // Get user's preferred categories for recommended courses
            $userPreferences = UserCategoryPreferences::where('user_id', $user->id)
                ->pluck('category_id')
                ->toArray();

            // Search in recommended courses (based on user preferences)
            $recommendedCourses = collect();
            if (!empty($userPreferences)) {
                $recommendedCourses = Course::with(['instructor', 'category'])
                    ->whereIn('category_id', $userPreferences)
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
                    ->limit(10)
                    ->get();
            }

            // Search in enrolled courses
            $enrolledCourses = Course::with(['instructor', 'category'])
                ->whereHas('progress', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
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
                ->get();

            // Search in all available courses
            $allCourses = Course::with(['instructor', 'category'])
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
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => [
                    'recommended_courses' => $recommendedCourses,
                    'enrolled_courses' => $enrolledCourses,
                    'all_courses' => $allCourses
                ],
                'search_term' => $searchTerm
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get courses by category
     */
    public function getCoursesByCategory($categoryId)
    {
        try {
            $courses = Course::with(['instructor', 'category'])
                ->where('category_id', $categoryId)
                ->orderBy('rating', 'desc')
                ->orderBy('total_students', 'desc')
                ->get();

            $category = Category::findOrFail($categoryId);

            return response()->json([
                'success' => true,
                'message' => 'Courses by category retrieved successfully',
                'data' => [
                    'category' => $category,
                    'courses' => $courses
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve courses by category',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
