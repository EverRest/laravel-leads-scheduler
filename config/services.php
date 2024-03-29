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

    'astro' => [
        'url' => env('ASTRO_PROXY_URL'),
        'token' => env('ASTRO_PROXY_TOKEN'),
    ],

    'startkirev' => [
        'url' => env('STARTK_IREV_URL'),
    ],

    'cmaffs' => [
        'url' => env('CMAFFS_URL'),
    ],

    'affiliatekingz' => [
        'url' => env('AFFILIATE_KINGS_URL'),
    ],

    'telegram' => [
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],

    'puppeteer' => [
        'url' => env('PUPPETEER_URL', 'localhost'),
        'port' => env('PUPPETEER_PORT', 4000),
    ],
];
