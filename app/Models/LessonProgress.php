<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'completion_percentage',
        'watch_time_seconds',
        'is_completed',
        'last_watched_at'
    ];

    protected $casts = [
        'last_watched_at' => 'datetime',
        'is_completed' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }
}
