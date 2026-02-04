<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialRequestItem extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    protected $fillable = [
        'material_request_id',
        'raw_material_id',
        'quantity'
    ];

    public function materialRequest()
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
