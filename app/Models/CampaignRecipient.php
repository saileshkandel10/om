<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignRecipient extends Model
{
    use HasFactory;

    protected $table = 'campaign_recipients';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'illness_category_id',
        'sent_at',
        'engagement_status',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function illnessCategory(): BelongsTo
    {
        return $this->belongsTo(IllnessCategory::class);
    }
}
