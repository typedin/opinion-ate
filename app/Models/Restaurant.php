<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address'];

    /**
     * @return HasMany Dish
     */
    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }


    public function images()
    {
        return $this->morphMany(Image::class, "imageable");
    }
}
