<?php

namespace App\Enums;

enum ComplaintCategory: string
{
    case SECURITY = 'security';
    case CLEANING = 'cleaning';
    case WATER = 'water';
    case PARKING = 'parking';
}
