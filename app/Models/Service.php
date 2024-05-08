<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'wp_latepoint_services';
    use HasFactory;

    public function category_id()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
