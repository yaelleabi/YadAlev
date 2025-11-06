<?php
namespace App\Enum;


enum DeliveryAssignmentStatus: string
{
    case WAITING = 'En attente';
    case PARTIALLY_ASSIGNED = 'Partiellement attribuée';
    case FULLY_ASSIGNED = 'Complet';
    case DELIVERED = 'Livré';
}
