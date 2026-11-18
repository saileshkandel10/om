<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacySourcingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'supplier_name',
        'source_invoice_no',
        'source_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'source_date' => 'date',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
