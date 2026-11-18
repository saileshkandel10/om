<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtherProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_name',
        'generic_name',
        'category_id',
        'illness_category_id',
        'suggested_illness_category_id',
        'confidence_score',
        'description',
        'status',
        'is_verified',
        'source',
        'raw_ocr_text',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'confidence_score' => 'decimal:2',
        ];
    }

    /**
     * Product category relation.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Associated illness category relation.
     */
    public function illnessCategory(): BelongsTo
    {
        return $this->belongsTo(IllnessCategory::class, 'illness_category_id');
    }

    /**
     * AI/Fuzzy suggested illness category relation.
     */
    public function suggestedIllnessCategory(): BelongsTo
    {
        return $this->belongsTo(IllnessCategory::class, 'suggested_illness_category_id');
    }

    /**
     * User who created or mapped this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for verified/active other products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for unmapped queue items.
     */
    public function scopeUnmapped(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('status', 'unmapped')
                ->orWhereNull('illness_category_id')
                ->orWhere('is_verified', false);
        });
    }

    /**
     * Scope for searching by brand name or generic name.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('brand_name', 'like', "%{$term}%")
                ->orWhere('generic_name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
