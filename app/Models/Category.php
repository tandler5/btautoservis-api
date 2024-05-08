<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'wp_latepoint_service_categories';
    use HasFactory;
    
    public function services()
    {
        return $this->hasMany(Service::class);
    }
    
}
