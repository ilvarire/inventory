<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'manager_id',
        'section_id',
        'type',
        'amount',
        'description',
        'expense_date'
    ];

    protected $casts = [
        'expense_date' => 'date'
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
