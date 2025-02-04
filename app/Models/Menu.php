<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'image',
        'is_available'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
