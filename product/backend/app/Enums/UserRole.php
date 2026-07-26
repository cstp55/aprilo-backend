<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case HrAdmin = 'hr_admin';
    case Employee = 'employee';
}
