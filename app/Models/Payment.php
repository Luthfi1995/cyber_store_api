<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\EncryptsRouteKey;

class Payment extends Model
{
    use HasFactory, EncryptsRouteKey;

    public const STATUS_WAITING_PAYMENT = 'waiting_payment';

    public const STATUS_PAID = 'paid';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'order_id',
        'bank_code',
        'virtual_account_number',
        'biller_code',
        'amount',
        'status',
        'paid_at',
        'expired_at',
        'external_reference',
        'snap_token',
        'snap_url',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
