<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentServices extends Model
{
    protected $table = 'wp_latepoint_agents_services';
    
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function get_results_as_models($query = false, $values = [])
    {
        if (isset($query)) {

        } else {
            return Agent::all();
        }
    }
}
