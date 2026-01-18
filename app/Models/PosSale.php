<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSale extends Model
{
    protected $fillable = [
        'hotel_id',
        'room_id',
        'booking_id',
        'sale_reference',
        'user_id',
        'sale_date',
        'total_amount',
        'discount',
        'final_amount',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    /**
     * Get the hotel that owns the POS sale
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the room (if attached)
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the booking (if attached)
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who created this sale
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get all items in this sale
     */
    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }

    /**
     * Get all payments for this POS sale
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get total paid amount
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->sum('amount') ?? 0;
    }

    /**
     * Get outstanding balance
     */
    public function getOutstandingBalanceAttribute(): float
    {
        return max(0, $this->final_amount - $this->total_paid);
    }

    /**
     * Check if sale is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->outstanding_balance <= 0;
    }

    /**
     * Update payment status based on payments
     */
    public function updatePaymentStatus(): void
    {
        $outstanding = $this->outstanding_balance;
        
        if ($outstanding <= 0) {
            $this->payment_status = 'paid';
        } elseif ($this->total_paid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }
        
        $this->save();
    }

    /**
     * Generate unique sale reference
     */
    public static function generateSaleReference(): string
    {
        do {
            $reference = 'POS' . strtoupper(substr(uniqid(), -8)) . rand(1000, 9999);
        } while (self::where('sale_reference', $reference)->exists());
        
        return $reference;
    }

    /**
     * Calculate total profit for this sale
     */
    public function getTotalProfitAttribute(): float
    {
        $totalProfit = 0;
        
        foreach ($this->items as $item) {
            $extra = $item->extra;
            if ($extra && $extra->cost) {
                $profitPerUnit = $item->unit_price - $extra->cost;
                $totalProfit += $profitPerUnit * $item->quantity;
            }
        }
        
        return round($totalProfit, 2);
    }

    /**
     * Boot method to auto-generate sale reference
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($sale) {
            if (empty($sale->sale_reference)) {
                $sale->sale_reference = self::generateSaleReference();
            }
            if (empty($sale->user_id) && auth()->check()) {
                $sale->user_id = auth()->id();
            }
        });
    }
}
