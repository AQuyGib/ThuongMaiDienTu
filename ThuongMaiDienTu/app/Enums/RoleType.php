<?php

namespace App\Enums;

enum RoleType: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';
    case STAFF = 'staff';
}
