<?php

namespace App\Http\Controllers\App\Couriers;

use App\Http\Controllers\Admin\Couriers\CouriersController as BaseCouriersController;
use App\Support\ClientContext;

class CouriersController extends BaseCouriersController
{
    public function __construct(ClientContext $clientContext)
    {
        parent::__construct($clientContext);

        $this->routePrefix = 'app.couriers.';
    }
}
