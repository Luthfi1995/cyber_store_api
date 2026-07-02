<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'description',
        'location',
        'proof_photo',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
