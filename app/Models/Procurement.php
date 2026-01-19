<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procurement extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'procurement_user_id',
        'supplier_id',
        'purchase_date',
        'status'
    ];

    protected $appends = ['reference_number', 'total_cost'];

    /**
     * Get the procurement reference number.
     */
    public function getReferenceNumberAttribute()
    {
        return 'PRO-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the total cost of the procurement.
     */
    public function getTotalCostAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_cost;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'procurement_user_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(ProcurementItem::class);
    }
}
