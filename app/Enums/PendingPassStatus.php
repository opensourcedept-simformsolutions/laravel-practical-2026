<?php

namespace App\Enums;

enum PendingPassStatus: string
{
    case PENDING = 'pending';
    case ENTERED = 'entered';
    case EXITED = 'exited';
}
