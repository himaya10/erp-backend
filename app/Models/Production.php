<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'status',
        'production_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'production_date' => 'date',
        ];
    }

    /**
     * The product being manufactured.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
