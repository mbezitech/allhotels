<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSaleItem extends Model
{
    protected $fillable = [
        'pos_sale_id',
        'extra_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the POS sale that owns this item
     */
    public function posSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class);
    }

    /**
     * Get the extra/product for this item
     */
    public function extra(): BelongsTo
    {
        return $this->belongsTo(Extra::class);
    }
}
