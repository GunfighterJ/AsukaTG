<?php

return [
    'groups' => [
        'groups_mode' => env('ASUKA_GROUPS_MODE', 'whitelist'),
        'groups_list' => array_map(function($val) {
            return intval(trim($val));
        }, explode(',', env('ASUKA_GROUPS_LIST', '')))
    ]
];
