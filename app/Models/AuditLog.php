<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $casts = [
        'before' => 'array',
        'after' => 'array'
    ];
}

//Audit Trait
// AuditLog::create([
//                 'user_id' => auth()->id(),
//                 'action' => 'update',
//                 'entity_type' => get_class($model),
//                 'entity_id' => $model->id,
//                 'before' => $model->getOriginal(),
//                 'after' => $model->getDirty(),
//             ]);