<?php
namespace issyrocks12\UsersList;

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->subscribe(Listener\AddAdminMailApi::class);
    $events->subscribe(Listener\AddClientAssets::class);
};
