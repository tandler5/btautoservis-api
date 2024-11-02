<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $table = 'wp_latepoint_locations';

    protected $fillable = [
        'name',
        'full_address',
        'status',
        'selection_image_id',
        'category_id',
        'order_number',
    ];

    public function image()
    {
        return $this->belongsTo(Post::class, 'selection_image_id');
    }

}
