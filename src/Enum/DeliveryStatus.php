<?php
namespace App\Enum;

enum DeliveryStatus: string
{
    case PLANNED = 'Préparée';
    case IN_PROGRESS = 'En cours';
    case COMPLETED = 'Terminée';
}
