<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'name',
        'section_id',
        'created_by',
        'status'
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
