<?php

return [
    'groups' => [
        'groups_mode' => env('ASUKA_GROUPS_MODE', 'whitelist'),
        'groups_list' => array_map(function($val) {
            return intval(trim($val));
        }, explode(',', env('ASUKA_GROUPS_LIST', '')))
    ],
    'keys' => [
        'google' => [
            'api_key' => env('GOOGLE_API_KEY'),
            'custom_search_engine_id' => env('GOOGLE_SEARCH_ENGINE_ID'),
        ]
    ]
];
