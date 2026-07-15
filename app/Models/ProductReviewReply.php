<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReviewReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_review_id',
        'user_id',
        'reply',
    ];

    /**
     * Get the review that owns the reply.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(ProductReview::class, 'product_review_id');
    }

    /**
     * Get the user (admin/superadmin) who wrote the reply.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
