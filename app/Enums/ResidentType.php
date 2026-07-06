<?php

namespace App\Enums;

enum ResidentType: string
{
    case OWNER = 'owner';
    case TENANT = 'tenant';
}
