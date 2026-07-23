<?php

namespace App\Domain\Survey;

enum RoundAvailability: string
{
    case Active = 'active';
    case Blocked = 'blocked';
    case Scheduled = 'scheduled';
    case Closed = 'closed';
    case Unavailable = 'unavailable';
}
