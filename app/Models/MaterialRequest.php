<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialRequest extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'chef_id',
        'section_id',
        'status',
        'approved_by',
        'approved_at',
        'fulfilled_by',
        'fulfilled_at'
    ];

    protected $casts = [
        'chef_id' => 'integer',
        'approved_at' => 'datetime',
        'fulfilled_at' => 'datetime'
    ];

    public function items()
    {
        return $this->hasMany(MaterialRequestItem::class);
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function fulfiller()
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }
}
