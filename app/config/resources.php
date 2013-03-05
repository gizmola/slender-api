<?php

/**
 * The resource config has the structure:
 *
 * [
 *      //  Core resources - things stored in the 'default' db
 *      'core' => [
 *          // core resources described here
 *      ],
 *
 *      // Standard resources - things stored in a per-site db
 *      // Ex: photos, albums, etc
 *
 *      // Per-site resources and possible overrides
 *      'per-site' => [
 *          'site-1' => [
 *              // specific overrides or site-specific resources
 *          ]
 *      ],
 * ]
 *
 * From this information, we control:
 *
 * 1. Routing
 * 2. Model classes
 * 3. Controller classes
 * 4. Relations
 *
 * The basic structure of each resource-descriptor is:
 *
 *      'my-resource' => [
 *          'controller' => [
 *              'class' => 'My\Controller\Class\Name',
 *          ],
 *          'model' => [
 *              'class' => 'My\Model\Class\Name',
 *              'parents' => [
 *                  'my-parent-1' => [
 *                      'class' => 'My\Parent\Class\Name',
 *                  ],
 *              ],
 *              'children' => [
 *                  'my-child-1' => [
 *                      'class' => 'My\Child\Class\Name',
 *                      'embed' => true, // or false
 *                      'embedKey' => 'sweet-child-of-mine',
 *                  ],
 *              ],
 *          ],
 *      ],
 *
 * Much of this is optional and there are many sensible defaults and conventions
 * built in (the ResourceResolver class handles all of that).
 *
 * A reasonable working version of the above is (with parents and children) is:
 *
 *      'my-resource' => [
 *          'model' => [
 *              'parents' => [
 *                  'my-parent-1' => [],
 *              ],
 *              'children' => [
 *                  'my-child-1' => [],
 *              ],
 *          ],
 *      ],
 *
 * The minimal working version of the above (no parents/children) is:
 *
 *      'my-resource' => [],
 *
 * The default fallback base namespace for models and controllers is defined
 * in app/config/app.php under the key 'fallback-namespaces.resources'
 *
 * The ResourceResolver is populated with this data and then is able to construct
 * (with proper fallbacks) classnames for models, controlllers, and relations.
 *
 * The RouteCreator uses this ResourceResolver to construct routes and callbacks
 * corresponding to this resource data.
 *
 * Also, a PermissionsResolver uses this ResourceResolver to construct - given a
 * URL request path - a collection of MongoDB dot-separated path corresponding
 * to the permissions in our user records. This can then be used in the auth filter.
 *
 * To get a sense of what those two components:
 *
 *  ResourceResolver
 *  PermissionsResolver
 *
 * actually do, take a look at the unit-tests. I think that they do a decent job
 * or articulating the formats that each one consumes as input and delivers as output.
 *
 */
return [

    // core resources
    'core' => [
        'roles' => [],

        'users' => [],

        'sites' => [],
    ],

    // Base non-core resources.
    // Requires a site, but available to *all* sites. We may wish to add change this.
    // Perhaps add a per-site key that disables such a resource.
    // @todo
    //
    'albums' => [
       'model' => [
           'children' => [
               'photos' => [
                   // 'class' => 'Slender\API\Model\Photos',
                   // 'embed' => true,
                   // 'embedKey' => 'photos',
               ],
           ],
       ],
    ],
    'photos' => [
       'model' => [
           'parents' => [
               'albums' => [
                   // 'class' => 'Slender\API\Model\Albums',
               ],
           ],
       ],
    ],
    'news' => [],
    'pages' => [],
    'videos' => [],

    // per-site overrides or custom resources
    'per-site' => [
        'eb' => [
            'vendor-profiles' => [
                'controller' => [
                  'class' => 'Slender\API\Controller\Site\Eb\VendorProfilesController',
                ],
                'model' => [
                  'class' => 'Slender\API\Model\Site\Eb\VendorProfiles',
                ],
                'parents' => [
                   'users' => [
                       'class' => 'Slender\API\Model\Site\Eb\Users',
                   ],
                ],
            ],
            'customer-profiles' => [
                'controller' => [
                  'class' => 'Slender\API\Controller\Site\Eb\CustomerProfilesController',
                ],
                'model' => [
                  'class' => 'Slender\API\Model\Site\Eb\CustomerProfiles',
                ],
                'parents' => [
                   'users' => [
                       'class' => 'Slender\API\Model\Site\Eb\Users',
                   ],
                ],
            ],
            'users' => [
                'controller' => [
                  'class' => 'Slender\API\Controller\Site\Eb\UsersController',
                ],
                'model' => [
                  'class' => 'Slender\API\Model\Site\Eb\Users',
                ],
                'children' => [
                   'vendor-profiles' => [
                       'class' => 'Slender\API\Model\Site\Eb\VendorProfiles',
                       'embed' => true,
                       'embedKey' => 'vendor_profiles',
                   ],
                   'customer-profiles' => [
                       'class' => 'Slender\API\Model\Site\Eb\CustomerProfiles',
                       'embed' => true,
                       'embedKey' => 'customer_profiles',
                   ],
                ],
            ],
        ],
    ],
];