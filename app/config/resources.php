<?php

return [

    // core resources
    'roles' => [
    ],

    'users' => [
    ],

    'sites' => [
    ],

    // base non-core resources (requires a site)

    'photos' => [
//        'model' => [
//            'parents' => [
//                'albums' => [],
//            ],
//        ],
    ],

    'albums' => [
//        'model' => [
//            'class' => 'Slender\Model\Albums',
//            'children' => [
//                'photos' => [
//                    'embed' => true,        // optional: default to false
//                    'embedKey' => 'pics',  // optional, default to key in 'children' array
//                ],
//            ],
//        ]
    ],

    'news' => [],

    // per-site overrides or custom resources
    'per-site' => [

        // AI
        'ai' => [
            'photos' => [],
            'albums' => [],
        ],
        'demo' => [
            'news',
        ],
    ],
];