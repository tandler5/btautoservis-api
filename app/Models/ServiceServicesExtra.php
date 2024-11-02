<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceServicesExtra extends Model
{
    protected $table = 'wp_latepoint_services_service_extras';
    use HasFactory;

    public function service_extra_id()
    {
        return $this->belongsToMany(ServiceExtra::class, 'id');
    }

    public function service_id()
    {
        return $this->belongsToMany(Service::class, 'id');
    }

}
