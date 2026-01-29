<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'category',
        'section_id',
        'min_quantity',
        'reorder_quantity',
        'preferred_supplier_id'
        // reorder_email_sent_at (nullable)
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function batches()
    {
        return $this->hasMany(ProcurementItem::class);
    }

    // Alias for batches - used in reporting and reorder services
    public function procurementItems()
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
