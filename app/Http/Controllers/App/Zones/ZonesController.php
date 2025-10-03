<?php

namespace App\Http\Controllers\App\Zones;

use App\Http\Controllers\Admin\Zones\ZonesController as BaseZonesController;
use App\Support\ClientContext;

class ZonesController extends BaseZonesController
{
    public function __construct(ClientContext $clientContext)
    {
        parent::__construct($clientContext);

        $this->routePrefix = 'app.zones.';
    }
}
