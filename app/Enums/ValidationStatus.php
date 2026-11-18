<?php

namespace App\Enums;

enum ValidationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case UnderReview = 'under_review';
}
