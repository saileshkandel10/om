<?php

namespace App\Enums;

enum UserType: string
{
    case Admin = 'admin';
    case Staff = 'staff';
    case Shareholder = 'shareholder';
    case Pharmacy = 'pharmacy';
    case Doctor = 'doctor';
    case Customer = 'customer';
}
