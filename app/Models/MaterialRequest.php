<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    protected $fillable = [
        'chef_id',
        'section_id',
        'status',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    public function items()
    {
        return $this->hasMany(MaterialRequestItem::class);
    }
}
