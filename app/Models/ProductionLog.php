<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionLog extends Model
{
    protected $fillable = [
        'recipe_version_id',
        'section_id',
        'chef_id',
        'quantity_produced',
        'production_date'
    ];

    protected $casts = [
        'production_date' => 'date'
    ];

    public function recipeVersion()
    {
        return $this->belongsTo(RecipeVersion::class);
    }

    public function materials()
    {
        return $this->hasMany(ProductionMaterial::class);
    }
}
