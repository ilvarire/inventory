<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{
    protected $fillable = [
        'procurement_user_id',
        'supplier_id',
        'purchase_date',
        'status'
    ];

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
