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
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // Get all categories
            $categories = Category::all();

            // Get user's preferred categories
            $userPreferences = UserCategoryPreferences::where('user_id', $user->id)
                ->pluck('category_id')
                ->toArray();

            // Ambil parameter category_id dari request
            $categoryId = $request->get('category_id');

            // Get recommended courses based on user preferences
            $recommendedCourses = collect();
            if (!empty($userPreferences)) {
                $recommendedCoursesQuery = Course::with(['instructor', 'category', 'lessons'])
                    ->whereIn('category_id', $userPreferences)
                    ->orderBy('rating', 'desc')
                    ->limit(10);

                // Jika ada category_id, filter juga berdasarkan category_id
                if ($categoryId) {
                    $recommendedCoursesQuery->where('category_id', $categoryId);
                }

                $recommendedCourses = $recommendedCoursesQuery->get();
            }

            // Get courses user is enrolled in (has progress)
            $enrolledCoursesQuery = Course::with(['instructor', 'category', 'lessons'])
                ->whereHas('progress', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });

            // Jika ada category_id, filter juga berdasarkan category_id
            if ($categoryId) {
                $enrolledCoursesQuery->where('category_id', $categoryId);
            }

            $enrolledCourses = $enrolledCoursesQuery->get();

            $mapEnrolledCourse = function ($course) use ($user) {
                $totalVideo = $course->lessons->count();
                $totalDurationMinutes = $course->lessons->sum('duration_minutes');
                $totalDurationHours = round($totalDurationMinutes / 60, 2);

                // Hitung jumlah lesson yang sudah selesai (progress 100%)
                $completedLessons = 0;
                foreach ($course->lessons as $lesson) {
                    $progress = $lesson->progress()->where('user_id', $user->id)->first();
                    if ($progress && $progress->completion_percentage == 100) {
                        $completedLessons++;
                    }
                }

                // Cari lesson aktif (belum selesai, urutan paling kecil)
                $activeLesson = $course->lessons
                    ->sortBy('lesson_order')
                    ->first(function ($lesson) use ($user) {
                        $progress = $lesson->progress()->where('user_id', $user->id)->first();
                        return !$progress || $progress->completion_percentage < 100;
                    })
                    ->pluck('title')->first() ?? null;

                // Persentase progress course
                $progressCourse = $totalVideo > 0 ? round(($completedLessons / $totalVideo) * 100, 2) : 0;

                $course->total_video = $totalVideo;
                $course->total_duration_hours = $totalDurationHours;
                $course->progress_course = $progressCourse;
                $course->active_lesson = $activeLesson;

                return $course;
            };

            $enrolledCourses = $enrolledCourses->map($mapEnrolledCourse);

            // Get all courses (default or by category)
            $allCoursesQuery = Course::with(['instructor', 'category', 'lessons'])
                ->orderBy('rating', 'desc')
                ->orderBy('total_students', 'desc');

            if ($categoryId) {
                $allCoursesQuery->where('category_id', $categoryId);
            }

            $allCourses = $allCoursesQuery->get();

            // Tambahkan total video dan total duration (jam) ke setiap course
            $mapCourse = function ($course) {
                $totalVideo = $course->lessons->count();
                $totalDurationMinutes = $course->lessons->sum('duration_minutes');
                $totalDurationHours = round($totalDurationMinutes / 60, 2);

                $course->total_video = $totalVideo;
                $course->total_duration_hours = $totalDurationHours;
                return $course;
            };

            $recommendedCourses = $recommendedCourses->map($mapCourse);
            $enrolledCourses = $enrolledCourses->map($mapCourse);
            $allCourses = $allCourses->map($mapCourse);

            return response()->json([
                'success' => true,
                'message' => 'Data home berhasil diambil',
                'data' => [
                    'categories' => $categories,
                    'recommended_courses' => $recommendedCourses,
                    'enrolled_courses' => $enrolledCourses,
                    'all_courses' => $allCourses,
                    'user_preferences' => $userPreferences
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data home',
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

            $userPreferences = UserCategoryPreferences::where('user_id', $user->id)
                ->pluck('category_id')
                ->toArray();

            // Mapping function (sama seperti index)
            $mapCourse = function ($course) use ($user) {
                $totalVideo = $course->lessons->count();
                $totalDurationMinutes = $course->lessons->sum('duration_minutes');
                $totalDurationHours = round($totalDurationMinutes / 60, 2);

                // Progress & active lesson (untuk enrolled)
                $completedLessons = 0;
                foreach ($course->lessons as $lesson) {
                    $progress = $lesson->progress()->where('user_id', $user->id)->first();
                    if ($progress && $progress->completion_percentage == 100) {
                        $completedLessons++;
                    }
                }
                $activeLesson = $course->lessons
                    ->sortBy('lesson_order')
                    ->first(function ($lesson) use ($user) {
                        $progress = $lesson->progress()->where('user_id', $user->id)->first();
                        return !$progress || $progress->completion_percentage < 100;
                    });

                $progressCourse = $totalVideo > 0 ? round(($completedLessons / $totalVideo) * 100, 2) : 0;

                $course->total_video = $totalVideo;
                $course->total_duration_hours = $totalDurationHours;
                $course->progress_course = $progressCourse;
                $course->active_lesson = $activeLesson;

                return $course;
            };

            // Recommended courses
            $recommendedCourses = collect();
            if (!empty($userPreferences)) {
                $recommendedCourses = Course::with(['instructor', 'category', 'lessons'])
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

            // Enrolled courses
            $enrolledCourses = Course::with(['instructor', 'category', 'lessons'])
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

            // All courses
            $allCourses = Course::with(['instructor', 'category', 'lessons'])
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

            // Apply mapping
            $recommendedCourses = $recommendedCourses->map($mapCourse);
            $enrolledCourses = $enrolledCourses->map($mapCourse);
            $allCourses = $allCourses->map($mapCourse);

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
            $user = Auth::user();

            $category = Category::findOrFail($categoryId);

            $userPreferences = UserCategoryPreferences::where('user_id', $user->id)
                ->pluck('category_id')
                ->toArray();

            $mapCourse = function ($course) use ($user) {
                $totalVideo = $course->lessons->count();
                $totalDurationMinutes = $course->lessons->sum('duration_minutes');
                $totalDurationHours = round($totalDurationMinutes / 60, 2);

                $completedLessons = 0;
                foreach ($course->lessons as $lesson) {
                    $progress = $lesson->progress()->where('user_id', $user->id)->first();
                    if ($progress && $progress->completion_percentage == 100) {
                        $completedLessons++;
                    }
                }
                $activeLesson = $course->lessons
                    ->sortBy('lesson_order')
                    ->first(function ($lesson) use ($user) {
                        $progress = $lesson->progress()->where('user_id', $user->id)->first();
                        return !$progress || $progress->completion_percentage < 100;
                    });

                $progressCourse = $totalVideo > 0 ? round(($completedLessons / $totalVideo) * 100, 2) : 0;

                $course->total_video = $totalVideo;
                $course->total_duration_hours = $totalDurationHours;
                $course->progress_course = $progressCourse;
                $course->active_lesson = $activeLesson;

                return $course;
            };

            // Recommended courses in this category
            $recommendedCourses = collect();
            if (!empty($userPreferences) && in_array($categoryId, $userPreferences)) {
                $recommendedCourses = Course::with(['instructor', 'category', 'lessons'])
                    ->where('category_id', $categoryId)
                    ->orderBy('rating', 'desc')
                    ->limit(10)
                    ->get();
            }

            // Enrolled courses in this category
            $enrolledCourses = Course::with(['instructor', 'category', 'lessons'])
                ->where('category_id', $categoryId)
                ->whereHas('progress', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();

            // All courses in this category
            $allCourses = Course::with(['instructor', 'category', 'lessons'])
                ->where('category_id', $categoryId)
                ->orderBy('rating', 'desc')
                ->orderBy('total_students', 'desc')
                ->get();

            // Apply mapping
            $recommendedCourses = $recommendedCourses->map($mapCourse);
            $enrolledCourses = $enrolledCourses->map($mapCourse);
            $allCourses = $allCourses->map($mapCourse);

            return response()->json([
                'success' => true,
                'message' => 'Courses by category retrieved successfully',
                'data' => [
                    'category' => $category,
                    'recommended_courses' => $recommendedCourses,
                    'enrolled_courses' => $enrolledCourses,
                    'all_courses' => $allCourses
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
