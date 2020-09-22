<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'restaurant_id', "user_id"];

    protected $casts = [
        "rating" => "float",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)    ;
    }
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, "imageable");
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function getRatingAttribute()
    {
        return $this->ratings()->avg("value");
    }
}
