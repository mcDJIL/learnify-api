<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserCategoryPreferences;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserCategoryPreferencesController extends Controller
{
    /**
     * Store category preferences (one or multiple)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_ids' => 'required|array|min:1',
                'category_ids.*' => 'required|uuid|exists:categories,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $categoryIds = $request->category_ids;

            // Clear existing preferences
            UserCategoryPreferences::where('user_id', $user->id)->delete();

            // Add new preferences
            foreach ($categoryIds as $categoryId) {
                UserCategoryPreferences::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryId
                ]);
            }

            // Get saved preferences with category data
            $savedPreferences = UserCategoryPreferences::with('category')
                ->where('user_id', $user->id)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Category preferences saved successfully',
                'data' => $savedPreferences
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
