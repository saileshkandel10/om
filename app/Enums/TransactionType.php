<?php

namespace App\Enums;

enum TransactionType: string
{
    case Earned = 'earned';
    case Purchase = 'purchase';
    case Redeemed = 'redeemed';
    case Adjusted = 'adjusted';
}
