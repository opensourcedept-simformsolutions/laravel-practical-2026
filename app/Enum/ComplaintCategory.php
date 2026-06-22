<?php

namespace App\Enum;

enum ComplaintCategory: string
{
    case SECURITY = 'security';
    case CLEANING = 'cleaning';
    case WATER = 'water';
    case PARKING = 'parking';
}
