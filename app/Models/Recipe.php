<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'section_id',
        'created_by',
        'status',
        'description',
        'expected_yield',
        'yield_unit',
        'selling_price',
        'instructions'
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions()
    {
        return $this->hasMany(RecipeVersion::class);
    }
}
