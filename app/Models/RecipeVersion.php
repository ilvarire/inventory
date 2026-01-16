<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeVersion extends Model
{
    protected $fillable = [
        'recipe_id',
        'version_number',
        'created_by',
        'effective_date'
    ];

    protected $casts = [
        'effective_date' => 'date'
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function items()
    {
        return $this->hasMany(RecipeItem::class);
    }
}
