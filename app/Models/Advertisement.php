<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'image_path',
        'background_color',
        'text_color',
        'button_color',
        'button_text_color',
        'cta_text',
        'cta_action',
        'target_role',
        'start_date',
        'end_date',
        'display_order',
        'is_active',
        'impressions_count',
        'clicks_count',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
    ];

    /**
     * Scope for currently active advertisements (within start & end date).
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = Carbon::today()->toDateString();

        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $today);
            })
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            });
    }

    /**
     * Scope for targeting a specific user role or 'all'.
     */
    public function scopeForRole(Builder $query, ?string $role): Builder
    {
        if (empty($role)) {
            return $query->where('target_role', 'all');
        }

        $normalized = strtolower($role);
        $roleCandidates = ['all', $normalized];

        if (in_array($normalized, ['patient', 'customer'], true)) {
            $roleCandidates = ['all', 'patient', 'customer'];
        }

        return $query->whereIn('target_role', $roleCandidates);
    }

    /**
     * Get public URL for image banner.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return url(Storage::url($this->image_path));
    }

    /**
     * Calculate Click-Through Rate (CTR) percentage.
     */
    public function getCtrAttribute(): float
    {
        if ($this->impressions_count <= 0) {
            return 0.0;
        }

        return round(($this->clicks_count / $this->impressions_count) * 100, 2);
    }

    /**
     * Check if the advertisement is currently active and within schedule.
     */
    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = Carbon::today();

        if ($this->start_date && $this->start_date->gt($today)) {
            return false;
        }

        if ($this->end_date && $this->end_date->lt($today)) {
            return false;
        }

        return true;
    }

    /**
     * Record impression count.
     */
    public function recordImpression(): void
    {
        $this->increment('impressions_count');
    }

    /**
     * Record click count.
     */
    public function recordClick(): void
    {
        $this->increment('clicks_count');
    }
}
