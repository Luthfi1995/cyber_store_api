<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\EncryptsRouteKey;

class Expedition extends Model
{
    use HasFactory, EncryptsRouteKey;

    protected $fillable = [
        'name',
        'code',
        'service',
        'base_cost',
        'estimated_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_cost' => 'decimal:2',
            'estimated_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
