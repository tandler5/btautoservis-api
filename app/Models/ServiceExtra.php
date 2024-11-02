<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceExtra extends Model
{
    protected $table = 'wp_latepoint_service_extras';
    use HasFactory;
  
    public function service_id()
    {
        return $this->belongsTo(Service::class);
    }

    public function service_extra_id()
    {
        return $this->belongsTo(ServiceExtra::class);
    }
    public function image()
    {
        return $this->belongsTo(Post::class, 'selection_image_id');
    }

}
