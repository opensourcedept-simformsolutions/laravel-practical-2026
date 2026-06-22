<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case RECEIVED = 'received';
    case DELIVERED = 'delivered';
}
