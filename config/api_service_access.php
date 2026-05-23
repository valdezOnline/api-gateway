<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Allow Behavior
    |--------------------------------------------------------------------------
    |
    | If true, requests with no explicit assignment record are allowed.
    | If false, explicit enabled assignment is required.
    |
    */
    'default_allow_when_unassigned' => env('API_SERVICE_ACCESS_DEFAULT_ALLOW', true),
];
