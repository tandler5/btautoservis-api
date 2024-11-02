<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkPeriod extends Model
{
    protected $table = 'wp_latepoint_work_periods';
    use HasFactory;

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
