<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseReviewController extends Controller
{
    /**
     * Add review to a course
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|uuid|exists:courses,id',
            'rating' => 'required|numeric|min:1|max:5',
        ]);

        $user = Auth::user();

        // Cek apakah user sudah pernah review course ini
        $exists = CourseReview::where('user_id', $user->id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberikan review untuk kursus ini.'
            ], 409);
        }

        $review = CourseReview::create([
            'user_id' => $user->id,
            'course_id' => $request->course_id,
            'rating' => $request->rating
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review berhasil ditambahkan.',
            'data' => $review
        ], 201);
    }
}
