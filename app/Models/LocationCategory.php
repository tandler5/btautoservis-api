<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocationCategory extends Model
{
    use HasFactory;

    protected $table = 'wp_latepoint_location_categories';

    protected $fillable = [
        'name',
        'short_description',
        'parent_id',
        'selection_image_id',
        'order_number',
    ];

    public function parent()
    {
        return $this->belongsTo(LocationCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(LocationCategory::class, 'parent_id');
    }

    public function locations()
    {
        return $this->hasMany(Location::class, 'category_id');
    }

    // Další metody můžete přidat podle potřeby, například:
    // public function getSelectionImageUrl() {...}
    // atd.
}
