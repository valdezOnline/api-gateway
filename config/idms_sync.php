<?php

return [
    'use_queue' => (bool) env('IDMS_SYNC_USE_QUEUE', true),
    'queue' => env('IDMS_SYNC_QUEUE'),

    'staging' => [
        'page_size' => (int) env('IDMS_STAGING_PAGE_SIZE', 5000),
        'insert_chunk_size' => (int) env('IDMS_STAGING_INSERT_CHUNK_SIZE', 1000),
        'schedule_cron' => env('IDMS_STAGING_SYNC_CRON', '0 2 * * 1,3,5'),
    ],

    'merge' => [
        'chunk_size' => (int) env('IDMS_MERGE_CHUNK_SIZE', 2500),
        'schedule_cron' => env('IDMS_MERGE_SYNC_CRON', '0 2 * * 2,4,6'),
    ],
];
