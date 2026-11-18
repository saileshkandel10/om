<?php

namespace App\Enums;

enum NoticeType: string
{
    case General = 'general';
    case Agm = 'agm';
    case FinancialReport = 'financial_report';
    case Announcement = 'announcement';
}
