<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $table = 'wp_latepoint_agents';

    protected $fillable = [
        'first_name',
        'last_name',
        'display_name',
        'email',
        'phone',
        'password',
        'wp_user_id',
        'bio_image_id',
        'title',
        'bio',
        'features',
        'extra_emails',
        'extra_phones',
        'status',
        'avatar_image_id',
        'custom_hours'
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'wp_latepoint_agents_services', 'agent_id', 'service_id');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'wp_latepoint_agents_services', 'agent_id', 'location_id');
    }


    public function image()
    {
        return $this->belongsTo(Post::class, 'avatar_image_id');
    }
}
