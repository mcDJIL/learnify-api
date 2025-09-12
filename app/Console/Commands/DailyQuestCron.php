<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Quests;
use App\Models\UserQuest;

class DailyQuestCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quest:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tambah dan hapus user quest tipe daily setiap hari';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Hapus semua user quest daily yang belum diambil
        UserQuest::whereHas('quest', function ($q) {
            $q->where('quest_type', 'daily');
        })->delete();

        // Ambil semua quest daily
        $dailyQuests = Quests::where('quest_type', 'daily')->get();

        // Ambil semua user
        $users = User::all();

        foreach ($users as $user) {
            foreach ($dailyQuests as $quest) {
                UserQuest::create([
                    'user_id' => $user->id,
                    'quest_id' => $quest->id,
                    'current_progress' => 0,
                    'is_completed' => false
                ]);
            }
        }

        $this->info('Daily quests berhasil direset dan ditambahkan ke semua user.');
    }
}
