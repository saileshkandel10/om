<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IllnessCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function otherProducts(): HasMany
    {
        return $this->hasMany(OtherProduct::class, 'illness_category_id');
    }

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function patientIllnesses(): HasMany
    {
        return $this->hasMany(PatientIllness::class);
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'patient_illnesses', 'illness_category_id', 'user_id')
            ->withPivot(['source_type', 'source_id', 'product_id', 'detected_at'])
            ->withTimestamps()
            ->distinct();
    }
}
