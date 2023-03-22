<?php

namespace Modules\Messages\Entities\Helpers;

enum DeliveryStatus
{
    case DEFERRED;
    case IN_QUEUE;
    case ERROR;
    case PENDING;
    case DELIVERED;
    case READ;

}
