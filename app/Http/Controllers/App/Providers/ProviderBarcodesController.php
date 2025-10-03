<?php

namespace App\Http\Controllers\App\Providers;

use App\Http\Controllers\Admin\Providers\ProviderBarcodesController as BaseProviderBarcodesController;
use App\Support\ClientContext;

class ProviderBarcodesController extends BaseProviderBarcodesController
{
    public function __construct(ClientContext $clientContext)
    {
        parent::__construct($clientContext);

        $this->providerRoutePrefix = 'app.providers.';
    }
}
