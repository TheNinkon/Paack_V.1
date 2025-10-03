<?php

namespace App\Http\Controllers\App\Users;

use App\Http\Controllers\Admin\Users\UsersController as BaseUsersController;
use App\Support\ClientContext;

class UsersController extends BaseUsersController
{
    public function __construct(ClientContext $clientContext)
    {
        parent::__construct($clientContext);

        $this->routePrefix = 'app.users.';
    }
}
