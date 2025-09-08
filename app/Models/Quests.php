<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quests extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'target_value',
        'xp_reward',
        'quest_type'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    // Relationships
    public function userQuests()
    {
        return $this->hasMany(UserQuest::class, 'quest_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_quests', 'quest_id', 'user_id')
                    ->withPivot('current_progress', 'is_completed')
                    ->withTimestamps();
    }
}
