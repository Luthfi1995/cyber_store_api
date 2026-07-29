<?php

namespace App\Models;

use App\Traits\EncryptsRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory, EncryptsRouteKey;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'order',
    ];

    /**
     * Scope a query to order banners by the `order` column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
