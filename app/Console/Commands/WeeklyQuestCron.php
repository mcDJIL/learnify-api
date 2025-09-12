<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Quests;
use App\Models\UserQuest;

class WeeklyQuestCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quest:weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tambah dan hapus user quest tipe weekly setiap minggu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Hapus semua user quest weekly yang belum diambil
        UserQuest::whereHas('quest', function ($q) {
            $q->where('quest_type', 'weekly');
        })->delete();

        // Ambil semua quest weekly
        $weeklyQuests = Quests::where('quest_type', 'weekly')->get();

        // Ambil semua user
        $users = User::all();

        foreach ($users as $user) {
            foreach ($weeklyQuests as $quest) {
                UserQuest::create([
                    'user_id' => $user->id,
                    'quest_id' => $quest->id,
                    'current_progress' => 0,
                    'is_completed' => false
                ]);
            }
        }

        $this->info('Weekly quests berhasil direset dan ditambahkan ke semua user.');
    }
}
