<?php
/**
 * Core.en.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
return [

    'name' => 'Core Framework',

    /*
     * **************************************************************************
     * BASICS
     * ****************************************************************************
     */
    'state' => [
        'on' => 'on',
        'off' => 'off',
        'yes' => 'yes',
        'no' => 'no'
    ],

	/* ERRORS */
	'error' => [
        'headline' => 'Error',
        'general' => 'A general error occured.',
        '404' => 'The requested page does not exist.',
        '403' => 'Access denied',
        '500' => 'A server error occured.'
    ],

    'default' => 'Default',
    'none' => 'None',

    'please' => [
        'select' => 'Bitte auswählen...',
        'confirm' => 'Bitte bestätigen...'
    ],

    // Basics
    'noscript' => '<span style="color: #FF0000, font-size: 16px, border: 1px solid #FF0000, padding: 3px, width: 100%, text-align: center,DIESE SEITE BENÖTIGT JAVASCRIPT.<br BITTE AKTIVIERE ES IN DEINEN BRWOSEREINSTELLUNGEN.</spa',

    'action' => [
        'next' => [
            'text' => 'Next',
            'icon' => 'angle-double-right'
        ],
        'prev' => [
            'text' => 'Back',
            'icon' => 'angle-double-left'
        ],
        'refresh' => [
            'text' => 'Refresh',
            'icon' => 'refresh'
        ],
        'save' => [
            'text' => 'Save',
            'cofirm' => 'Save data?',
            'icon' => 'floppy-o'
        ],
        'cancel' => [
            'text' => 'cancel',
            'cofirm' => 'Cancel action?',
            'icon' => 'ban'
        ],
        'update' => [
            'text' => 'Update',
            'cofirm' => 'Update data?',
            'icon' => 'floppy-o'
        ],
        'delete' => [
            'text' => 'delete',
            'cofirm' => 'Delete data?',
            'icon' => 'trash-o'
        ],
        'add' => [
            'text' => 'Add',
            'confirm' => 'Add data?',
            'icon' => 'plus-square-o'
        ],
        'edit' => [
            'text' => 'Edit',
            'confirm' => 'Edit data?',
            'icon' => 'pencil-square-o'
        ]
    ],

    'menu' => [
        'login' => 'Login',
        'logout' => 'Logout',
        'register' => 'Register',
        'reset' => 'Reset password',
        'admin' => 'Administration'
    ],

    // USER
    'login' => [
        'text' => '@@menu.login',
        'form' => [
            'username' => 'E-Mail',
            'password' => 'Password',
            'remember' => 'Stay logged in',
            'login' => '@@login.text'
        ],
        'message' => [
            'already_loggedin' => 'You are already logged in.',
            'failed' => 'Login failed! Please check your username and password.',
            'success' => 'Login was successful.'
        ]
    ],

    'register' => [
        'form' => [
            'headline' => 'New account',
            'username' => 'login.form.username',
            'password' => 'login.form.password',
            'compare' => 'Repeat password',
            'button' => 'Create account'
        ],
        'mail' => [
            'subject' => 'Account Activation on {brand}',
            'body' => [
                'html' => '<h2>Hello and welcome to {brand}!</h2>
<p>Bevor das Benutzerkonto verwendet werden kann, bedarf es einer Aktivierung. Klicken Sie hierzu bitte auf den nachfolgenden Link.</p>
<p><a href="{url.activate}">{url.activate}</a></p>
<p>Vielen Dank und auf Wiedersehen</p><p>Das Team von {brand}</p>
<hr>
<p><small><strong>Hinweis:</strong> Wenn sie diese Mail ohne ihr zutun bekommen haben, dann hat sich jemand unter angabe ihrer Mailadresse bei uns auf der Seite registriert.
Das tut uns selbstverständlich leid. Wenn sie nicht aktiv werden, dann wird der Eintrag spätestens nach einigen Tagen ohne Aktivierung autmonatisch gelöscht. Falls sie dies sofort veranlassen möchten, so steht ihnen dies über den nachfolgenden Link zur Verfügung
<a href="{url.deny}">{url.deny}</a></small></p>',
                'plain' => 'Hallo und willkommen bei {brand}!

Bevor das Benutzerkonto verwendet werden kann, bedarf es einer Aktivierung. Klicken Sie hierzu bitte auf den nachfolgenden Link.

{url.activate}

Vielen Dank und auf Wiedersehen
Das Team von {brand}

Hinweis: Wenn Sie diese Mail ohne ihr zutun bekommen haben, dann hat sich jemand unter angabe ihrer Mailadresse bei uns auf der Seite registriert.
Das tut uns selbstverständlich leid. Wenn sie nicht aktiv werden, dann wird der Eintrag spätestens nach einigen Tagen ohne Aktivierung autmonatisch gelöscht werden. Falls Sie dies sofort veranlassen möchten, so steht Ihnen dies über den nachfolgenden Link zur Verfügung
{url.deny}'
            ]
        ],
        'activation' => [
            'notice' => 'This accoutn needs to be activated by clicking the activationlink in the mail we sent to you.',
            'wait' => [
                'headline' => 'One small step left...',
                'text' => 'We\ve sent you an email to the address you provided to us. Please look into your email inbox and and klick the activation link inside the mail we sent to you to complete the registration process.',
            ],
            'done' => [
                'headline' => 'You\'re useraccount is now active!',
                'text' => 'Thank you for your registration. We welcome you as a new user of our services. You are now able to login with your usergredentials.'
            ],
            'fail' => [
                'headline' => 'Upps! Somthing went wrong.',
                'text' => 'Unfortunatly we cannot complete your accountactivation. In some mailclients the activationlink may gets corrupted. It may help you to copy the link into your browser addressfield.<br><br>If the error still occurs you can contact us under %s.'
            ],
            'deny' => [
                'ok' => [
                    'headline' => 'Ihr Eintrag wurde gelöscht.',
                    'text' => 'Ihre eingetragene E-Mailadresse wurde nun aus dem System gelöscht.'
                ],
                'nouser' => [
                    'headline' => 'No user for this key.',
                    'text' => 'Entweder wurde der Eintrag bereits gelöscht oder der der Schlüssel ist nicht korrekt.'
                ]
            ]
        ]
    ],

    /**
     * **************************************************************************
     * CONFIG
     * **************************************************************************
     */
    'config' => [
        'headline' => 'Core Framework Settings',
        'desc' => '',

        'site' => [
            'general' => [
                'head' => 'Seite',
                'desc' => '',
                'name' => [
                    'label' => 'Name (Brand)',
                    'desc' => 'The sites name which is also used as brand inside various methods inside framework.'
                ],
                'url' => [
                    'label' => 'Baseurl',
                    'desc' => 'The FQFN basurl of the site including leading http(s):// and without trailing slash (Example: http://mydomain.tld)'
                ],
                'webmaster_email' => [
                    'label' => 'Webmaster (Admin) emailaddress',
                    'desc' => 'E-Mailaddress for all Webmaster/Admin realted communication'
                ]
            ],

            'language' => [
                'default' => [
                    'label' => 'Default language',
                    'desc' => 'Default language of this website.'
                ]
            ]
        ],

        // Execute

        'execute' => [
            'head' => 'Runtime Execute',
            'desc' => '',
            'default' => [
                'head' => 'Default Settings',
                'desc' => '',
                'action' => [
                    'label' => 'Default action',
                    'desc' => 'Name of default action to use.'
                ],
                'app' => [
                    'label' => 'Default app',
                    'desc' => 'Name of app which is used for pagecontrol'
                ],
                'controller' => [
                    'label' => 'Default Controller',
                    'desc' => 'Name of controller to call in default app.'
                ]
            ],
            'content' => [
                'head' => 'Content Settings',
                'desc' => '',
                'handler' => [
                    'label' => 'Contenthandler app',
                    'desc' => 'Name of app which handles the content output.'
                ]
            ]
        ],
        // Security
        'security' => [
            'head' => 'Security',
            'desc' => '',
            'user' => [
                'head' => 'User Settings',
                'username' => [
                    'head' => 'Username rules',
                    'min_length' => [
                        'label' => 'Minimum lenght (chars)',
                        'desc' => 'The minimum number of chars the username has to contain.'
                    ],
                    'regexp' => [
                        'label' => 'Username regexpcheck',
                        'desc' => 'RegEx to check a username against on user creation.'
                    ]
                ],
                'password' => [
                    'head' => 'Password rules',
                    'min_length' => [
                        'label' => 'Minimum password length',
                        'desc' => 'The minimum number of chars needed to create a password.'
                    ],
                    'max_length' => [
                        'label' => 'Maximum password length',
                        'desc' => 'The maximum number of chars allowed to create a password.'
                    ],
                    'regexp' => [
                        'label' => 'Password regex check',
                        'desc' => 'RegEx to check a password against on user creation.'
                    ]
                ]
            ],
            'register' => [
                'head' => 'Registration Settings',
                'use_compare_password' => [
                    'label' => 'Password compare field',
                    'desc' => 'Switch to control the display and use of a senconde password field to compare passwords before sending it to DB.'
                ]
            ],
            'activation' => [
                'head' => 'Activation Settings',
                'use' => [
                    'label' => 'Activate via',
                    'desc' => 'Defines how useraccounts get activated.'
                ],
                'mail' => [
                    'ttl' => [
                        'label' => 'TTL of activation token',
                        'desc' => 'Time (in seconds) how long the activation token sent bei activationmal stays valid until a new token needs to be requested.'
                    ],
                    'mta' => [
                        'label' => 'MTA to use',
                        'desc' => 'Name of the Mail Transfer Agent to send the activation mail. <strong>Important:</strong> MTA must be registered!'
                    ],
                    'sender' => [
                        'label' => 'Sender address',
                        'desc' => 'The emailaddress to use when sending activationmail to the user'
                    ],
                    'from' => [
                        'label' => 'From name',
                        'desc' => 'Optional FromName to use when sending activationmail toi the user'
                    ]
                ]
            ],
            'login' => [
                'head' => 'Login Settings',
                'autologin' => [
                    'label' => 'Use autologin',
                    'desc' => 'Switch to set autologin on login form to be preselected. (Default: On)'
                ]
            ],
            'ban' => [
                'head' => 'Ban Settings',
                'tries' => [
                    'label' => 'Tries before ban',
                    'desc' => 'When set >0 a ban counter is startet on a ban enabled request. Example: Failed logins. (Default: 0 = no Bans)'
                ],
                'ttl' => [
                    'head' => 'TTL',
                    'log' => [
                        'label' => 'Log relevance (in seconds)',
                        'desc' => 'Time for how '
                    ],
                    'ban' => [
                        'label' => 'Bantime (in Seconds)',
                        'desc' => 'Time how long a ban is active'
                    ]
                ]
            ]
        ],
        // Javascript
        'js' => [
            'head' => 'Javascript',
            'desc' => '',
            'general' => [
                'head' => 'General Settings',
                'desc' => '',
                'position' => [
                    'label' => 'Script position',
                    'desc' => 'Setting to control the placement of javascript by default.'
                ]
            ],
            'jquery' => [
                'head' => 'jQuery Settings',
                'desc' => '',
                'version' => [
                    'label' => 'jQuery version',
                    'desc' => '<strong>Note:</strong> Make sure to have the needed files in the core js folder when using jQuery from local and not from CDN.'
                ],
                'local' => [
                    'label' => 'Local jQuery files',
                    'desc' => 'Use local jQuery files instead of CDN?'
                ]
            ],
            'style' => [
                'head' => 'Style Settings',
                'desc' => '',
                'fadeout_time' => [
                    'label' => 'Fadeouttime',
                    'desc' => 'Time (in milliseconds) to use as global fadeout timer.'
                ]
            ]
        ],
        // Style
        'style' => [
            'head' => 'Visuals (CSS & Theme)',
            'desc' => '',
            'bootstrap' => [
                'head' => 'Bootstrap Settings',
                'desc' => '',
                'version' => [
                    'label' => 'Version',
                    'desc' => 'Version number of Bootstrap css framework to use. Do not forget to place the corresponding file into frameworks css folder. The filename has to use "bootstrap-version.css" or "bootstrap-versions.min.css" pattern.'
                ],
                'local' => [
                    'label' => 'Local files',
                    'desc' => 'Local Bootstrap files instead of official CDN?'
                ]
            ],
            'fontawesome' => [
                'head' => 'Fontawesome Settings',
                'desc' => '',
                'version' => [
                    'label' => 'Version',
                    'desc' => 'Version number of Fontawesome css framework to use. Do not forget to place the corresponding file into frameworks css folder. The filename has to use "fontawesome-versionnumber.css" or "fontawesome-version.min.css" pattern.'
                ],
                'local' => [
                    'label' => 'Local files',
                    'desc' => 'Use local Fontawesome files instead of official CDN?'
                ]
            ],
            'theme' => [
                'head' => 'Theme Settings',
                'desc' => '',
                'name' => [
                    'label' => 'Theme',
                    'desc' => 'Name of the theme tu use.'
                ]
            ]
        ],

        // Error

        'error' => [
            'head' => 'Error Handling',
            'desc' => '',
            'display' => [
                'head' => 'Display Settings',
                'desc' => '',
                'skip_security_check' => [
                    'label' => 'Skip securitycheck',
                    'desc' => 'Switch to control how erros should be displayed. By default (off/no) non admin users will not see detailed error informations. They see a generic error message instead.'
                ]
            ],
            'mail' => [
                'head' => 'Mail Settings',
                'desc' => '',
                'use' => [
                    'label' => 'Send errormail',
                    'desc' => 'Sends an email to the set mailadress when an error occurs. Be careful with this option when activated on sites with high traffic.<br><strong>If no mail address is set below the webmaster adress from basic config will be used.</strong>'
                ],
                'address' => [
                    'label' => 'Errormail Reciepient(s)',
                    'desc' => 'Sends an email to the set mailadress when an error occurs. Be careful with this option when activated on sites with high traffic. Multiple recipients need to be seperated by ";"'
                ],
                'mta' => [
                    'label' => 'MTA to use',
                    'desc' => 'Defines which MTA should be used to send the errormail. Systemwide MTAs can be registred in Mailer settings. Default: "default"'
                ]
            ],
            'log' => [
                'head' => 'Logging Settings',
                'desc' => '',
                'use' => [
                    'label' => 'Global errorlogging switch',
                    'desc' => 'Switch to control error logging globally. It is <strong>recommended</strong> to let this option stay active and use the options below for finetuning in how errors should be logged.'
                ],
                'modes' => [
                    'head' => 'Logging Modes',
                    'desc' => '',
                    'db' => [
                        'label' => 'Database logging',
                        'desc' => 'Switch to activate error logging to error_log table in database (TODO: Add option to set logging server instead of default server).'
                    ],
                    'php' => [
                        'label' => 'PHP errorlog',
                        'desc' => 'Switch to enable or disable write of errors to the normal php error_log file.'
                    ]
                ]
            ]
        ],
        'cache' => [
            'head' => 'Caching',
            'desc' => '',
            'file' => [
                'head' => 'File Settings',
                'desc' => '',
                'ttl' => [
                    'label' => 'General TTL (in seconds)',
                    'desc' => 'TTL for alle cachefiles other than CCS or JS (Default: %s)'
                ],
                'ttl_js' => [
                    'label' => 'TTL Js files (in seconds)',
                    'desc' => 'TTL for all javacript (.js) files. Default: %s'
                ],
                'ttl_css' => [
                    'label' => 'TTL Css files (in seconds)',
                    'desc' => 'TTL for all stylesheet (.css) files. Default: %s'
                ]
            ],
            'memcache' => [
                'head' => 'Memcache Settings',
                'desc' => '',
                'use' => [
                    'label' => 'Use memchache',
                    'desc' => 'Switch to enable or disable use of Memcache'
                ],
                'server' => [
                    'label' => 'Server',
                    'desc' => 'IP of memchache server to use. Default: %s'
                ],
                'port' => [
                    'label' => 'Port',
                    'desc' => 'Port of memchache server to use. Default: %s'
                ]
            ]
        ],
        'mail' => [
            'head' => 'Mail',
            'desc' => '',
            'general' => [
                'head' => 'General Settings',
                'desc' => '',
                'smtpdebug' => [
                    'label' => 'SMTP Debugmode',
                    'desc' => 'Switch to activate SMTP debug out put which will be written into activated logs.'
                ]
            ],
            'mta' => [
                'head' => 'MTA Settings',
                'desc' => '',
                'default' => [
                    'head' => 'System Default',
                    'desc' => '',
                    'system' => [
                        'label' => 'System',
                        'desc' => 'System to use when sending mails'
                    ],
                    'host' => [
                        'label' => 'Host',
                        'desc' => 'Address of the SMPT host'
                    ],
                    'port' => [
                        'label' => 'Port',
                        'desc' => 'a'
                    ],
                    'username' => [
                        'label' => 'Username',
                        'desc' => 's'
                    ],
                    'password' => [
                        'label' => 'Password',
                        'desc' => 'd'
                    ],
                    'accept_selfsigned' => [
                        'label' => 'Accept selfsigned Cert',
                        'desc' => 'f'
                    ],
                    'protocol' => [
                        'label' => 'Connection protcol',
                        'desc' => 'g'
                    ]
                ]
            ]
        ]
    ],

    /**
     * **************************************************************************
     * USER
     * **************************************************************************
     */
    'user' => [
        'singular' => 'User',
        'plural' => 'Users',
        'icon' => 'user',
        'action' => [
            'edit' => [
                'text' => 'Edit user'
            ],
            'new' => [
                'text' => 'Add user'
            ]
        ],
        'list' => 'Userlist',
        'field' => [
            'username' => 'Username',
            'display_name' => 'Displayname',
            'password' => 'Password',
            'groups' => 'Usergroups'
        ]
    ],

    /**
     * **************************************************************************
     * GROUP
     * **************************************************************************
     */
    'group' => [
        'singular' => 'Usergroup',
        'plural' => 'Usergroups',
        'members' => 'Members',
        'icon' => 'users',
        'action' => [
            'edit' => [
                'text' => 'Edit usergroup'
            ],
            'new' => [
                'text' => 'Add usergroup'
            ]
        ],
        'list' => 'Grouplist',
        'field' => [
            'id_group' => 'Group ID',
            'title' => 'Name',
            'display_name' => 'Display name'
        ]
    ],

    /**
     * **************************************************************************
     * Group Permissions
     * **************************************************************************
     */
    'group_permission' => [
        'action' => [
            'edit' => [
                'text' => 'Edit group permission'
            ],
            'new' => [
                'text' => 'New  group permission'
            ]
        ],
        'field' => [
            'permission' => 'permission.singular',
            'notes' => 'Notes'
        ]
    ],

    /**
     * **************************************************************************
     * Permissions
     * **************************************************************************
     */
    'permission' => [
        'singular' => 'Accessright',
        'plural' => 'Accessrights',
        'admin' => [
            'text' => 'Administrator',
            'desc' => 'Grants administrative access for all (!) areas of the site (including all apps).'
        ],
        'config' => [
            'text' => 'Configuration',
            'desc' => 'Grants access for all (!) configareas of the site (including all apps).'
        ]
    ],

    /**
     * **************************************************************************
     * VALIDATORS
     * ****************************************************************************
     */
    'validator' => [
        'required' => 'This field has to be set.',
        'empty' => 'This field is not allowed to be empty.',
        // Strings
        'textrange' => 'The number of chars has to be between %d and %d. The checked string contains %d chars.',
        'textminlength' => 'The number of chars has to be %d at minimum.',
        'textmaxlength' => 'The number of chars has to be %d at maximum.',
        // Dates
        'date_iso' => 'Date in ISO Format (YYYY-MM-DD) expected.',
        'date' => 'Please provide proper date.',
        // Time
        'time24' => 'Time in 24h format (HH:II:ss) expected',
        // Number
        'compare' => 'Comparecheck failed. Checked: $1 $3 $2',
        'numbermin' => 'The value is not allowed to be smaller then %d.',
        'numbermax' => 'The value exeeds the set maximum of $1',
        'numberrange' => 'The value has to be between %d and %d.',
        // Email
        'email' => 'This is not a valid mailadress.',
        'email_dnscheck' => 'The email host "%s" is unknown eg does not exist.',

        // Url
        'url' => 'This is no valid url.'
    ],

    /**
     * **************************************************************************
     * Models
     * ****************************************************************************
     */
    'model_error_field_not_exist' => 'Column [%s does not exist in model [%s].',

    /**
     * **************************************************************************
     * TIMESTRINGS
     * ****************************************************************************
     */
    'time' => [
        'text' => [
            'ago' => '%s %s ago'
        ],
        'strings' => [
            '__preserve' => true,
            'year' => 'year',
            'years' => 'years',
            'month' => 'month',
            'months' => 'months',
            'week' => 'week',
            'weeks' => 'weeks',
            'day' => 'day',
            'days' => 'days',
            'hour' => 'hour',
            'hours' => 'hours',
            'minute' => 'minute',
            'minutes' => 'minutes',
            'second' => 'second',
            'seconds' => 'seconds'
        ],
        'months' => [
            '__preserve' => true,
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ],
        'days' => [
            '__preserve' => true,
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        ]
    ]
];

