<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserQuest;
use App\Models\Quests;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestController extends Controller
{
    /**
     * Finish quest, update XP, and remove from userQuest
     */
    public function finish(Request $request)
    {
        $request->validate([
            'user_quest_id' => 'required|uuid|exists:user_quests,id',
        ]);

        $user = Auth::user();

        $userQuest = UserQuest::with('quest')
            ->where('id', $request->user_quest_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$userQuest) {
            return response()->json([
                'success' => false,
                'message' => 'Quest tidak ditemukan untuk user ini'
            ], 404);
        }

        if (!$userQuest->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Quest belum selesai'
            ], 400);
        }

        // Update XP di profile
        $profile = Profile::where('user_id', $user->id)->first();
        if ($profile) {
            $profile->total_xp += $userQuest->quest->xp_reward;
            $profile->save();
        }

        // Hapus quest dari userQuest
        $userQuest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quest selesai, XP berhasil ditambahkan dan quest dihapus dari daftar',
            'xp_reward' => $userQuest->quest->xp_reward,
            'profile' => $profile
        ]);
    }

    /**
     * Get daily quests for authenticated user
     */
    public function daily()
    {
        $user = Auth::user();

        $userQuests = UserQuest::with('quest')
            ->where('user_id', $user->id)
            ->whereHas('quest', function ($q) {
                $q->where('quest_type', 'daily');
            })
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar daily quest berhasil diambil',
            'data' => $userQuests
        ]);
    }

    /**
     * Get weekly quests for authenticated user
     */
    public function weekly()
    {
        $user = Auth::user();

        $userQuests = UserQuest::with('quest')
            ->where('user_id', $user->id)
            ->whereHas('quest', function ($q) {
                $q->where('quest_type', 'weekly');
            })
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar weekly quest berhasil diambil',
            'data' => $userQuests
        ]);
    }

    /**
     * Update user quest progress
     */
    public static function updateUserQuestProgress($userId, $type)
    {
        // Ambil semua quest user yang belum selesai dan sesuai tipe
        $userQuests = UserQuest::with('quest')
            ->where('user_id', $userId)
            ->where('is_completed', false)
            ->whereHas('quest', function ($q) use ($type) {
                $q->where('quest_type', $type);
            })
            ->get();

        foreach ($userQuests as $userQuest) {
            // Update progress
            $userQuest->current_progress += 1;

            // Jika sudah mencapai target, tandai selesai
            if ($userQuest->current_progress >= $userQuest->quest->target_value) {
                $userQuest->is_completed = true;
            }

            $userQuest->save();
        }
    }
}
