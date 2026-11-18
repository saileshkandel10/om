<?php

namespace App\Enums;

enum RedemptionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Disbursed = 'disbursed';
}
