<?php
namespace App\Enum;

enum AidRequestStatus: string {
    case PENDING = 'pending';
    case VALIDATED = 'validated';
    case REFUSED = 'refused';
}