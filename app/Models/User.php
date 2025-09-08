<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verification_token'
    ];
    
    /**
     * The attributes that should be hidden for serialization.
    *
    * @var list<string>
    */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function generateEmailVerificationToken()
    {
        $this->email_verification_token = Str::random(10);
        $this->save();
        return $this->email_verification_token;
    }

    public function markEmailAsVerified()
    {
        $this->email_verified_at = now();
        $this->email_verification_token = null;
        $this->save();
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class, 'user_id');
    }

    public function favoriteCourses()
    {
        return $this->hasMany(FavoriteCourse::class, 'user_id');
    }

    public function userQuests()
    {
        return $this->hasMany(UserQuest::class, 'user_id');
    }

    public function quests()
    {
        return $this->belongsToMany(Quests::class, 'user_quests', 'user_id', 'quest_id')
                    ->withPivot('current_progress', 'is_completed')
                    ->withTimestamps();
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'user_id');
    }

    public function categoryPreferences()
    {
        return $this->hasMany(UserCategoryPreferences::class, 'user_id');
    }

    public function preferredCategories()
    {
        return $this->belongsToMany(Category::class, 'user_category_preferences', 'user_id', 'category_id')
                    ->withTimestamps();
    }
}
