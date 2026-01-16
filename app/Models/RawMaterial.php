<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'category',
        'min_quantity',
        'reorder_quantity',
        'preferred_supplier_id'
        // reorder_email_sent_at (nullable)
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'preferred_supplier_id');
    }

    public function batches()
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
