<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreparedInventory extends Model
{
    protected $fillable = [
        'production_log_id',
        'recipe_id',
        'section_id',
        'item_name',
        'quantity',
        'unit',
        'selling_price',
        'expiry_date',
        'status'
    ];

    protected $casts = [
        'expiry_date' => 'date'
    ];

    public function productionLog()
    {
        return $this->belongsTo(ProductionLog::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
