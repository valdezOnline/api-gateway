<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /**
     * Configuration for the Ex Libris API service, which is used to interact with the Ex Libris platform. The service requires an API key and a base URL, both of which are stored in environment variables for security. Additionally, cache durations for single and multiple data requests are specified to optimize performance and reduce unnecessary API calls.
     */
    'exLibris' => [
        'key' => env("EXLIBRIS_RW_API_KEY"),
        'baseUrl' => env("EXLIBRIS_API_BASEURL"),
        'singleDataMinutes' => env("SINGLE_DATA_MINUTES"),
        'multiDataMinutes' => env("MULTI_DATA_MINUTES"),
    ],

    /**
     * Starting below are the configurations for the various API services
     * provided by ITS used in the application. Each service has its own set of credentials and settings, which are typically stored in environment variables for security and flexibility.
     * IMPORTANT NOTE:
     * The key and baseUrl for each service are the same for now,
     * but they are separated here for clarity and potential future changes.
     */
    'ucrPerson' => [
        'key' => env("UCRGW_API_KEY"),
        'baseUrl' => env("UCRGW_API_BASEURL"),
        'singleDataMinutes' => env("SINGLE_DATA_MINUTES"),
        'multiDataMinutes' => env("MULTI_DATA_MINUTES"),
    ],

    'sisActiveStudent' => [
        'key' => env("UCRGW_API_KEY"),
        'baseUrl' => env("UCRGW_API_BASEURL"),
        'singleDataMinutes' => env("SINGLE_DATA_MINUTES"),
        'multiDataMinutes' => env("MULTI_DATA_MINUTES"),
    ],

    'hrEmployeeDetail' => [
        'key' => env("UCRGW_API_KEY"),
        'baseUrl' => env("UCRGW_API_BASEURL"),
        'singleDataMinutes' => env("SINGLE_DATA_MINUTES"),
        'multiDataMinutes' => env("MULTI_DATA_MINUTES"),
    ],

    'sisData' => [
        'key' => env("UCRGW_API_KEY"),
        'baseUrl' => env("UCRGW_API_BASEURL"),
        'singleDataMinutes' => env("SINGLE_DATA_MINUTES"),
        'multiDataMinutes' => env("MULTI_DATA_MINUTES"),
    ],

    'idmsCardData' => [
        'key' => env("UCRGW_API_KEY"),
        'baseUrl' => env("UCRGW_API_BASEURL"),
        'singleDataMinutes' => env("SINGLE_DATA_MINUTES"),
        'multiDataMinutes' => env("MULTI_DATA_MINUTES"),
    ],
];