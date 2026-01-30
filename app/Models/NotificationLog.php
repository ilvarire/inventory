<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'recipient_email',
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime'
    ];
}
