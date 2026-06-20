<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'sku',
        'type',
        'price',
        'quantity',
        'min_stock_level',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quantity' => 'integer',
            'min_stock_level' => 'integer',
        ];
    }

    /**
     * A product can have many production runs.
     */
    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }

    /**
     * A product can appear in many purchase order line items.
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * A product can appear in many sale line items.
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Check if the product is below the minimum stock level.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock_level;
    }
}
