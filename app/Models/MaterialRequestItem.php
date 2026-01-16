<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequestItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'material_request_id',
        'raw_material_id',
        'quantity'
    ];
}
