<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteLog extends Model
{
    use SoftDeletes;
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

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function preparedInventory()
    {
        return $this->belongsTo(PreparedInventory::class, 'production_log_id');
    }

    public function loggedBy()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
