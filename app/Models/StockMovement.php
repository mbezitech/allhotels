<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'hotel_id',
        'product_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the hotel that owns the stock movement
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the product (extra) for this movement
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Extra::class, 'product_id');
    }

    /**
     * Get the user who created this movement
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the parent reference (polymorphic)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
