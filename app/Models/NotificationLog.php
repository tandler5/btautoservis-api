<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $table = 'wp_notifications_log';
    protected $fillable = ['customer_token', 'status', 'message'];

    
}
