<?php
namespace App\Enum;

enum AidRequestStatus: string {
    case PENDING = 'En attente';
    case VALIDATED = 'Acceptée';
    case REFUSED = 'Refusée';
}