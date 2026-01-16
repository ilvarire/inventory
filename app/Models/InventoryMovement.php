<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'raw_material_id',
        'procurement_item_id',
        'from_location',
        'to_location',
        'quantity',
        'movement_type',
        'reference_id',
        'performed_by',
        'approved_by'
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProcurementItem::class, 'procurement_item_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
