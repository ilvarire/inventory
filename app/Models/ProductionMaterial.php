<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionMaterial extends Model
{
    protected $fillable = [
        'production_log_id',
        'raw_material_id',
        'procurement_item_id',
        'quantity_used',
        'unit_cost'
    ];

    public function productionLog()
    {
        return $this->belongsTo(ProductionLog::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProcurementItem::class, 'procurement_item_id');
    }
}
