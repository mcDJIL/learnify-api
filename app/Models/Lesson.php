<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lesson extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'course_id',
        'title',
        'video_url',
        'duration_minutes',
        'lesson_order',
        'is_completed'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class, 'lesson_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'lesson_id');
    }
}
