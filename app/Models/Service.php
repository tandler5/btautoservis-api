<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'wp_latepoint_services';
    use HasFactory;

    protected $casts = [
        'capacity_min' => 'integer',
        'capacity_max' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function extras()
    {
        return $this->belongsToMany(ServiceExtra::class, 'wp_latepoint_services_service_extras', 'service_id', 'service_extra_id');
    }

    public function agents()
    {
        return $this->belongsToMany(
            Agent::class, // Model, se kterým je vytvořen vztah
            'wp_latepoint_agents_services', // Spojovací tabulka
            'service_id', // Sloupec v spojovací tabulce, který odkazuje na tento model
            'agent_id' // Sloupec v spojovací tabulce, který odkazuje na model Agent
        );
    }

    // determine how much capacity service can accept before the slot is blocked
    public function get_capacity_needed_before_slot_is_blocked(): int
    {
        $capacity_min = $this->capacity_min ? $this->capacity_min : 1;
        $capacity_max = $this->capacity_max ? $this->capacity_max : 1;
        return ($this->get_meta_by_key('block_timeslot_when_minimum_capacity_met', 'off') == 'on') ? $capacity_min : $capacity_max;
    }

    public function is_new_record()
    {
        if ($this->id) {
            return false;
        } else {
            return true;
        }
    }

    public function get_meta_by_key($meta_key, $default = false)
    {
        if ($this->is_new_record()) return $default;

        $meta = new ServiceMeta();
        return $meta->get_by_key($meta_key, $this->id, $default);
    }
}
