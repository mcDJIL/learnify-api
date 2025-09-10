<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Get authenticated user's profile
     */
    public function show()
    {
        $user = Auth::user();

        // Load profile relation
        $user->load('profile');

        return response()->json([
            'success' => true,
            'message' => 'Profil pengguna berhasil diambil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified' => !is_null($user->email_verified_at),
                    'profile' => $user->profile
                ]
            ]
        ]);
    }

    /**
     * Get courses user enrolled and their progress
     */
    public function enrolledCourses()
    {
        $user = Auth::user();

        // Ambil kursus yang didaftar user beserta progressnya
        $courses = Course::with([
                'instructor',
                'category',
                'lessons',
                'progress' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }
            ])
            ->whereHas('progress', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        // Progress hanya satu per user per course
        $courses->map(function ($course) {
            $course->progress = $course->progress->first();
            return $course;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar kursus yang didaftar dan progress berhasil diambil',
            'data' => $courses
        ]);
    }
}
