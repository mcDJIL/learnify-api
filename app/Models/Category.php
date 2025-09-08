<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    protected $fillable = ['name'];

    public function userPreferences()
    {
        return $this->hasMany(UserCategoryPreferences::class, 'category_id');
    }

    public function preferredByUsers()
    {
        return $this->belongsToMany(User::class, 'user_category_preferences', 'category_id', 'user_id')
                    ->withTimestamps();
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id');
    }
}
