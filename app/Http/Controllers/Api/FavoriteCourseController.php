<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FavoriteCourse;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteCourseController extends Controller
{
    /**
     * Add course to favorites
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|uuid|exists:courses,id',
        ]);

        $user = Auth::user();

        // Prevent duplicate favorite
        $exists = FavoriteCourse::where('user_id', $user->id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kursus sudah ada di favorit'
            ], 409);
        }

        $favorite = FavoriteCourse::create([
            'user_id' => $user->id,
            'course_id' => $request->course_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kursus berhasil ditambahkan ke favorit',
            'data' => $favorite
        ], 201);
    }

    /**
     * Get all favorite courses for user
     */
    public function index()
    {
        $user = Auth::user();

        $favorites = FavoriteCourse::with('course')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar kursus favorit berhasil diambil',
            'data' => $favorites
        ]);
    }
}
