<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
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
}
