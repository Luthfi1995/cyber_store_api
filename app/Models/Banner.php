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
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->orderBy('order');
    }
}
