<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quiz_id',
        'quiz_category_id',
        'question_text',
        'explanation',
        'points',
        'question_type',
        'difficulty',
        'product_id',
        'product_reference',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'points' => 'integer',
        'display_order' => 'integer',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(QuizCategory::class, 'quiz_category_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuizQuestionOption::class)->orderBy('display_order', 'asc')->orderBy('id', 'asc');
    }

    public function correctOption(): HasOne
    {
        return $this->hasOne(QuizQuestionOption::class)->where('is_correct', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDifficulty(Builder $query, ?string $difficulty): Builder
    {
        if (empty($difficulty)) {
            return $query;
        }

        return $query->where('difficulty', strtolower($difficulty));
    }

    public function scopeCategory(Builder $query, $categoryId): Builder
    {
        if (empty($categoryId)) {
            return $query;
        }

        return $query->where('quiz_category_id', $categoryId);
    }

    /**
     * Get the alphabetical letter (A, B, C, D) corresponding to the correct answer.
     */
    public function getCorrectOptionLetterAttribute(): ?string
    {
        $options = $this->options;
        foreach ($options as $index => $option) {
            if ($option->is_correct) {
                return chr(65 + $index); // 65 = 'A'
            }
        }

        return null;
    }
}
