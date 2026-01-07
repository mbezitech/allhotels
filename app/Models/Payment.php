<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'hotel_id',
        'booking_id',
        'pos_sale_id',
        'amount',
        'payment_method',
        'reference_number',
        'paid_at',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the hotel that owns the payment
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the booking (if payment is for booking)
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the POS sale (if payment is for POS sale)
     */
    public function posSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class);
    }

    /**
     * Get the user who received/recorded this payment
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
