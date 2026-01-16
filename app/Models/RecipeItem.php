<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'recipe_version_id',
        'raw_material_id',
        'quantity_required'
    ];

    public function recipeVersion()
    {
        return $this->belongsTo(RecipeVersion::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
