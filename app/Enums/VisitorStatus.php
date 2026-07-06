<?php

namespace App\Enums;

enum VisitorStatus: string
{
    case ACCEPTED = 'accepted';
    case PENDING = 'pending';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case ENTERED = 'entered';
    case EXITED = 'exited';
    case CANCELLED = 'cancelled';
}
