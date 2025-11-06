<?php

namespace App\Enum;

enum VolunteerRequestStatus: string
{
    case PENDING = 'En attente';
    case ACCEPTED = 'Acceptée';
    case REJECTED = 'Refusée';
}
