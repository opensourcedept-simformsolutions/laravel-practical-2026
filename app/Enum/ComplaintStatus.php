<?php

namespace App\Enum;

enum ComplaintStatus: string
{
    case OPEN = 'open';
    case INPROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
}
