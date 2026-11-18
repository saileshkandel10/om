<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShareholderMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'boid',
        'shareholder_name',
        'mobile',
        'email',
        'status',
        'source_note',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(ShareholderAccount::class, 'shareholder_master_id');
    }
}
