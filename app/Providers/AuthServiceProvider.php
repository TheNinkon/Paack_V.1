<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Courier;
use App\Models\Provider;
use App\Models\Parcel;
use App\Models\ActivityLog;
use App\Models\Scan;
use App\Models\User;
use App\Models\Zone;
use App\Policies\ClientPolicy;
use App\Policies\CourierPolicy;
use App\Policies\ProviderPolicy;
use App\Policies\ParcelPolicy;
use App\Policies\ActivityLogPolicy;
use App\Policies\ScanPolicy;
use App\Policies\UserPolicy;
use App\Policies\ZonePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Client::class => ClientPolicy::class,
        Provider::class => ProviderPolicy::class,
        Parcel::class => ParcelPolicy::class,
        Zone::class => ZonePolicy::class,
        Courier::class => CourierPolicy::class,
        User::class => UserPolicy::class,
        Scan::class => ScanPolicy::class,
        ActivityLog::class => ActivityLogPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
