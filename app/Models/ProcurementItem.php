<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'procurement_id',
        'raw_material_id',
        'quantity',
        'unit_cost',
        'received_quantity',
        'quality_note',
        'notes',
        'expiry_date'
    ];

    protected $casts = [
        'expiry_date' => 'date'
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
