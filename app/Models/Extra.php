<?php

namespace App\Models;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Extra extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'description',
        'category_id',
        'price',
        'stock_tracked',
        'min_stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_tracked' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the hotel that owns the extra
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the category for this extra
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExtraCategory::class, 'category_id');
    }

    /**
     * Get all POS sale items for this extra
     */
    public function posSaleItems(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }

    /**
     * Get all stock movements for this extra
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    /**
     * Get current stock balance (computed from movements)
     */
    public function getStockBalance(?int $hotelId = null): int
    {
        if (!$this->stock_tracked) {
            return 0;
        }

        $hotelId = $hotelId ?? session('hotel_id');

        $in = StockMovement::where('hotel_id', $hotelId)
            ->where('product_id', $this->id)
            ->where('type', 'in')
            ->sum('quantity');

        $out = StockMovement::where('hotel_id', $hotelId)
            ->where('product_id', $this->id)
            ->where('type', 'out')
            ->sum('quantity');

        return ($in ?? 0) - ($out ?? 0);
    }

    /**
     * Check if stock is low (below minimum)
     */
    public function isLowStock(?int $hotelId = null): bool
    {
        if (!$this->stock_tracked || !$this->min_stock) {
            return false;
        }

        return $this->getStockBalance($hotelId) <= $this->min_stock;
    }
}
