<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'action_type',
        'status',
        'payload',
        'requested_by',
        'approved_by'
    ];

    protected $casts = ['payload' => 'array'];

    public function approvable()
    {
        return $this->morphTo();
    }
}
