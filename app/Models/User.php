<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $workos_id
 * @property string|null $remember_token
 * @property string $avatar
 * @property string|null $mobile
 * @property string|null $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'workos_id', 'avatar', 'mobile', 'status'])]
#[Hidden(['workos_id', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function patientIllnesses(): HasMany
    {
        return $this->hasMany(PatientIllness::class);
    }

    public function illnessCategories(): BelongsToMany
    {
        return $this->belongsToMany(IllnessCategory::class, 'patient_illnesses', 'user_id', 'illness_category_id')
            ->withPivot(['source_type', 'source_id', 'product_id', 'detected_at'])
            ->withTimestamps()
            ->distinct();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_has_roles');
    }

    public function pharmacy(): HasOne
    {
        return $this->hasOne(Pharmacy::class);
    }

    public function shareholderAccount(): HasOne
    {
        return $this->hasOne(ShareholderAccount::class);
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function rewardWallet(): HasOne
    {
        return $this->hasOne(RewardWallet::class);
    }

    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(UploadedFile::class);
    }

    public function purchaseBills(): HasMany
    {
        return $this->hasMany(PurchaseBill::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function rewardTransactions(): HasMany
    {
        return $this->hasMany(RewardTransaction::class);
    }

    public function rewardRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function boidValidationLogs(): HasMany
    {
        return $this->hasMany(BoidValidationLog::class, 'requested_by_user_id');
    }

    public function assignedReviews(): HasMany
    {
        return $this->hasMany(OcrReviewQueue::class, 'assigned_to');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->whereRaw('LOWER(name) = ?', [strtolower($role)])->exists();
    }

    public function patientAnalytics(): HasMany
    {
        return $this->hasMany(PatientHealthAnalytic::class, 'patient_id');
    }
}
