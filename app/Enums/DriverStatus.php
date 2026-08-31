<?php

declare(strict_types=1);

namespace App\Enums;

enum DriverStatus: string {
    case ENROUTE_TO_GARAGE = 'Enroute to garage';
    case ARRIVED_AT_GARAGE = 'Arrived at garage';
    case ARRIVED_AT_LOCATION = 'Arrived at location';
    case ON_ASSIGNMENT = 'On assignment';
    case END_OF_SHIFT = 'End of shift';
    case EMERGENCY = 'Emergency';
}

?>