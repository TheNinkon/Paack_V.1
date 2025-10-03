<?php

namespace App\Http\Controllers\App\Providers;

use App\Http\Controllers\Admin\Providers\ProvidersController as BaseProvidersController;
use App\Support\ClientContext;

class ProvidersController extends BaseProvidersController
{
    public function __construct(ClientContext $clientContext)
    {
        parent::__construct($clientContext);

        $this->routePrefix = 'app.providers.';
        $this->barcodeRoutePrefix = 'app.providers.barcodes.';
    }
}
