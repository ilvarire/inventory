<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteLog extends Model
{
    protected $fillable = [
        'raw_material_id',
        'production_log_id',
        'section_id',
        'quantity',
        'reason',
        'cost_amount',
        'logged_by',
        'approved_by'
    ];
}
