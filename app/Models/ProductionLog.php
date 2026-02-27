<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'recipe_id',
        'section_id',
        'chef_id',
        'quantity_produced',
        'production_date',
        'variance',
        'notes'
    ];

    protected $casts = [
        'production_date' => 'date'
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function materials()
    {
        return $this->hasMany(ProductionMaterial::class);
    }
}
