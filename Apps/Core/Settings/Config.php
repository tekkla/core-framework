<?php

/**
 * Config.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
// Apps default config
return [
    'settings' => [
        'headline' => 'Core Framework Config'
    ],
    'groups' => [
        'site' => [
            'groups' => [
                'general' => [
                    'controls' => [
                        'name' => [
                            'validate' => [
                                'empty'
                            ],
                            'default' => 'MySite'
                        ],
                        'webmaster_email' => [
                            'control' => 'mail',
                            'validate' => [
                                'empty',
                                'email'
                            ]
                        ]
                    ]
                ],
                'language' => [
                    'controls' => [
                        'default' => [
                            'control' => 'select',
                            'data' => [
                                'type' => 'array',
                                'source' => [
                                    'en',
                                    'de'
                                ],
                                'index' => 1
                            ],
                            'default' => 'en'
                        ]
                    ]
                ]
            ]
        ],
        'security' => [
            'groups' => [
                'encrypt' => [
                    'controls' => [
                        'salt' => [
                            'default' => '@m@rschH@ngtDerH@mmer1234',
                            'validate' => [
                                'empty'
                            ]
                        ]
                    ]
                ],
                'login' => [
                    'groups' => [
                        'autologin' => [
                            'controls' => [
                                'active' => [
                                    'control' => 'switch',
                                    'default' => 1
                                ],
                                'expires_after' => [
                                    'control' => 'number',
                                    'default' => 30
                                ]
                            ]
                        ]
                    ]
                ],
                'ban' => [
                    'controls' => [
                        'active' => [
                            'control' => 'switch',
                            'default' => 1,
                        ],
                        'tries' => [
                            'control' => 'number',
                            'default' => 5
                        ]
                    ],
                    'groups' => [
                        'ttl' => [
                            'controls' => [
                                'log' => [
                                    'control' => 'number',
                                    'default' => 300
                                ],
                                'ban' => [
                                    'control' => 'number',
                                    'default' => 600
                                ]
                            ]
                        ]
                    ]
                ],
                'form' => [
                    'controls' => [
                        'token' => [
                            'default' => '__token'
                        ]
                    ]
                ]
            ]
        ],
        'user' => [
            'groups' => [
                'username' => [
                    'controls' => [
                        'min_length' => [
                            'type' => 'int',
                            'control' => 'number',
                            'default' => 8,
                            'validate' => [
                                'empty',
                                [
                                    'min',
                                    5
                                ]
                            ]
                        ],
                        'regexp' => [
                            'control' => [
                                'textarea',
                                [
                                    'rows' => 2
                                ]
                            ]
                        ]
                    ]
                ],
                'password' => [
                    'controls' => [
                        'min_length' => [
                            'control' => 'number',
                            'default' => 8,
                            'validate' => [
                                'empty',
                                [
                                    'min',
                                    8
                                ]
                            ]
                        ],
                        'max_length' => [
                            'control' => 'number',
                            'default' => 1024,
                            'validate' => [
                                'empty',
                                [
                                    'max',
                                    4096
                                ]
                            ]
                        ],
                        'regexp' => [
                            'control' => [
                                'textarea',
                                [
                                    'rows' => 2
                                ]
                            ]
                        ],
                        'reactivate_after_password_change' => [
                            'default' => 0,
                            'control' => 'switch',
                            'validate' => [
                                [
                                    'enum',
                                    [
                                        0,
                                        1
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'register' => [
                    'controls' => [
                        'use_compare_password' => [
                            'control' => 'switch',
                            'default' => 1
                        ]
                    ]
                ],
                'activation' => [
                    'controls' => [
                        'mode' => [
                            'control' => 'select',
                            'data' => [
                                'type' => 'array',
                                'source' => [
                                    0 => 'instant',
                                    1 => 'mail',
                                    2 => 'useradmin'
                                ],
                                'index' => 0
                            ],
                            'default' => 1
                        ]
                    ],
                    'groups' => [
                        'mail' => [
                            'settings' => [
                                'require' => [
                                    'config' => [
                                        [
                                            'core',
                                            'user.activation.use',
                                            1
                                        ]
                                    ]
                                ]
                            ],
                            'controls' => [
                                'ttl' => [
                                    'control' => 'number',
                                    'default' => 3600
                                ],
                                'mta' => [
                                    'control' => [
                                        'select',
                                        [
                                            'required' => false
                                        ]
                                    ],
                                    'data' => [
                                        'type' => 'model',
                                        'source' => [
                                            'app' => 'core',
                                            'model' => 'mta',
                                            'action' => 'getMtaIdTitleList'
                                        ],
                                        'index' => 1
                                    ]
                                ],
                                'from' => [
                                    'control' => 'mail',
                                    'validate' => [
                                        'email'
                                    ]
                                ],
                                'name' => []
                            ]
                        ]
                    ]
                ]
            ]
        ],
        // Group: Execute
        'home' => [
            'groups' => [
                'guest' => [
                    'controls' => [
                        'route' => [
                            'control' => 'select',
                            'data' => [
                                'type' => 'model',
                                'source' => [
                                    'app' => 'core',
                                    'model' => 'config',
                                    'action' => 'getAllRoutes'
                                ],
                                'index' => 0
                            ],
                            'default' => 'generic.index'
                        ],
                        'params' => [
                            'control' => [
                                'textarea',
                                [
                                    'rows' => 4
                                ]
                            ],
                            'default' => 'app=Core' . PHP_EOL . 'controller=Index'
                        ]
                    ]
                ],
                'user' => [
                    'controls' => [
                        'route' => [
                            'control' => 'select',
                            'data' => [
                                'type' => 'model',
                                'source' => [
                                    'app' => 'core',
                                    'model' => 'config',
                                    'action' => 'getAllRoutes'
                                ],
                                'index' => 0
                            ],
                            'default' => 'generic.index'
                        ],
                        'params' => [
                            'control' => 'textarea',
                            'default' => 'app=Core' . PHP_EOL . 'controller=Index'
                        ]
                    ]
                ]
            ]
        ],

        // Group: Asset
        'asset' => [
            'groups' => [
                'general' => [
                    'controls' => [
                        'minify_js' => [
                            'control' => 'switch',
                            'default' => 1
                        ],
                        'minify_css' => [
                            'control' => 'switch',
                            'default' => 1
                        ]
                    ]
                ]
            ]
        ],

        // Group: JS
        'js' => [
            'groups' => [
                'general' => [
                    'controls' => [
                        'position' => [
                            'control' => 'select',
                            'data' => [
                                'type' => 'array',
                                'source' => [
                                    't' => 'Top',
                                    'b' => 'Bottom'
                                ],
                                'index' => 0
                            ],
                            'default' => 't',
                            'validate' => [
                                [
                                    'enum',
                                    [
                                        't',
                                        'b'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],

                'jquery' => [
                    'controls' => [
                        'version' => [
                            'default' => '2.2.0'
                        ],
                        'local' => [
                            'control' => 'switch',
                            'default' => 1
                        ]
                    ]
                ],
                'style' => [
                    'controls' => [
                        'fadeout_time' => [
                            'control' => [
                                'number',
                                [
                                    'min' => 100
                                ]
                            ],
                            'default' => 5000,
                            'validate' => [
                                'empty',
                                [
                                    'min',
                                    100
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'style' => [
            'groups' => [
                // Bootstrap
                'bootstrap' => [
                    'controls' => [
                        'version' => [
                            'control' => 'input',
                            'default' => '3.3.6',
                            'validate' => [
                                'empty'
                            ]
                        ],
                        'local' => [
                            'control' => 'switch',
                            'default' => 1
                        ]
                    ]
                ],
                'fontawesome' => [
                    'controls' => [
                        'version' => [
                            'default' => '4.5.0',
                            'validate' => [
                                'empty'
                            ]
                        ],
                        'local' => [
                            'control' => 'switch',
                            'default' => 1
                        ]
                    ]
                ],
                'theme' => [
                    'controls' => [
                        'name' => [
                            'control' => 'text',
                            'default' => 'Core',
                            'validate' => [
                                'empty'
                            ]
                        ]
                    ]
                ]
            ]
        ],
        // Error handling
        'error' => [
            'groups' => [
                'display' => [
                    'controls' => [
                        'skip_security_check' => [
                            'control' => 'switch'
                        ]
                    ]
                ],
                'mail' => [
                    'controls' => [
                        'use' => [
                            'control' => 'switch',
                            'default' => 1
                        ]
                    ],
                    'groups' => [
                        'mta' => [
                            'controls' => [
                                'recipient' => [],
                                'use' => [
                                    'control' => 'select',
                                    'data' => [
                                        'type' => 'model',
                                        'source' => [
                                            'app' => 'core',
                                            'model' => 'mta',
                                            'action' => 'getMtaIdTitleList'
                                        ],
                                        'index' => 0
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'log' => [
                    'controls' => [
                        'use' => [
                            'control' => 'switch',
                            'default' => 1
                        ]
                    ],
                    'groups' => [
                        'modes' => [
                            'controls' => [
                                'db' => [
                                    'control' => 'switch',
                                    'default' => 1
                                ],
                                'php' => [
                                    'control' => 'switch',
                                    'default' => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],

        // Caching
        'cache' => [
            'groups' => [
                'ttl' => [
                    'controls' => [
                        'js' => [
                            'control' => 'number',
                            'default' => '3600'
                        ],
                        'css' => [
                            'control' => 'number',
                            'default' => '3600'
                        ]
                    ]
                ]
            ]
        ],

        // Mailsystem
        'mail' => [
            'groups' => [
                'general' => [
                    'controls' => [
                        'smtpdebug' => [
                            'name' => 'smtpdebug',
                            'control' => 'select',
                            'data' => [
                                'type' => 'array',
                                'source' => [
                                    0 => 'off',
                                    1 => 1,
                                    2 => 2,
                                    3 => 3,
                                    4 => 4,
                                    5 => 5,
                                    6 => 6
                                ],
                                'index' => 0
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
