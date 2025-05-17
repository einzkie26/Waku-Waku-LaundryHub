<?php

return [
    App\Providers\AppServiceProvider::class,
];

$app->routeMiddleware([
    'staff.auth' => App\Http\Middleware\StaffAuth::class,
    // other middleware...
]);
