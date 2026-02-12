<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'item_name',
        'quantity',
        'unit_price',
        'cost_price',
        'source_type',
        'source_id'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function preparedInventory()
    {
        return $this->belongsTo(PreparedInventory::class, 'source_id');
    }
}
