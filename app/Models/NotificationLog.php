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
        'sent_at',
        'read_at',
        'action_url'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
