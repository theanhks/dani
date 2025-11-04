<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $table = 'notification_logs';

    protected $fillable = [
        'notification_date',
        'type',
        'status',
        'retry_count',
        'error_message',
        'data',
    ];

    protected $casts = [
        'notification_date' => 'date',
        'retry_count' => 'integer',
        'data' => 'array',
    ];
}
