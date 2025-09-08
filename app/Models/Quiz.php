<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quiz extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'lesson_id',
        'question',
        'choices',
        'correct_answer',
        'quiz_order'
    ];

    protected $casts = [
        'choices' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    // Relationships
    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function userAnswers()
    {
        return $this->hasMany(UserQuizAnswer::class, 'quiz_id');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }
}
