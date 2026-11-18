<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseBillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_bill_id',
        'product_id',
        'product_name_raw',
        'quantity',
        'unit_price',
        'amount',
        'batch_no',
        'confidence_score',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'amount' => 'decimal:2',
            'confidence_score' => 'decimal:2',
        ];
    }

    public function purchaseBill(): BelongsTo
    {
        return $this->belongsTo(PurchaseBill::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
