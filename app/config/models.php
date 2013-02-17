<?php

return [
        
    // Roles
    'roles' => [
        'model' => [
            // 'class' => 'Slender\Model\Roles' // optional
            'parents' => [],
            'children' => [],
        ],

//            // entire section is optional
//            'controller' => [
//                'class' => 'Slender\Controller\RolesController' // optional
//            ],
    ],

    // Users
    'users' => [
        'model' => [
            // 'class' => 'Slender\Model\Users' // optional
            'parents' => [],
            'children' => [],
        ],
//          // entire section is optional
//            'controller' => [
//                'class' => 'Slender\Controller\UsersController' // optional
//            ],
    ],

    // Sites
    'sites' => [
        'model' => [
            // 'class' => 'Slender\Model\Sites' // optional
            'parents' => [],
            'children' => [],
        ],
//          // entire section is optional
//            'controller' => [
//                'class' => 'Slender\Controller\SitesController' // optional
//            ],
    ],


    // Per-site config
    'per-site' => [


        // AI
        'ai' => [
            'photos' => [
                'model' => [
                    // 'class' => 'Slender\Model\Photos', // optional, obvious default
                    'parents' => [
                        'albums' => [
//                                'class' => 'Slender\Model\Albums' // optional, obvious default
                        ],
                    'children' => [],
                    ],
                ]
            ],
            'albums' => [
                'model' => [
                    // 'class' => 'Slender\Model\Albums', // optional
                    'parents' => [],
                    'children' => [                            
                        'photos' => [
//                                'class' => 'Slender\Model\Albums' // optional, obvious default
                            'embed' => true,
                            'embedKey' => 'pics',  // optional, default to key in 'children' array
                        ],
                    ],
                ]
            ]
        ],
    ],
];