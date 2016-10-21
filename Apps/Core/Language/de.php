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
        'on' => 'an',
        'off' => 'aus',
        'yes' => 'ja',
        'no' => 'nein'
    ],

    	/* ERRORS */
    	'error' => [
        'headline' => 'Fehler',
        'general' => 'Ein allgemeiner Fehler ist aufgetreten.',
        '404' => 'Die angeforderte Seite xistiert nicht.',
        '403' => 'Sie haben keine Rechte auf diese Seite zuzugreifen.',
        '500' => 'Ein interner Fehler ist  aufgetreten.'
    ],

    'default' => 'Standard',
    'none' => 'Keine/r',
    'please' => [
        'select' => 'Bitte auswählen...',
        'confirm' => 'Bitte bestätigen...'
    ],

    'noscript' => '<span style="color: #FF0000, font-size: 16px, border: 1px solid #FF0000, padding: 3px, width: 100%, text-align: center,DIESE SEITE BENÖTIGT JAVASCRIPT.<br BITTE AKTIVIERE ES IN DEINEN BRWOSEREINSTELLUNGEN.</spa',

    'action' => [
        'next' => [
            'text' => 'Weiter',
            'icon' => 'angle-double-right'
        ],
        'prev' => [
            'text' => 'Zurück',
            'icon' => 'angle-double-left'
        ],
        'refresh' => [
            'text' => 'Neu laden',
            'icon' => 'refresh'
        ],
        'save' => [
            'text' => 'Speichern',
            'cofirm' => 'Wirklich speichern?',
            'icon' => 'floppy-o'
        ],
        'cancel' => [
            'text' => 'Abbrechen',
            'cofirm' => 'Wirklich abbrechen?',
            'icon' => 'ban'
        ],
        'update' => [
            'text' => 'Aktualisieren',
            'cofirm' => 'Wirklich aktualisieren?',
            'icon' => 'floppy-o'
        ],
        'delete' => [
            'text' => 'Löschen',
            'cofirm' => 'Wirklich löschen?',
            'icon' => 'trash-o'
        ],
        'add' => [
            'text' => 'Hinzufühgen',
            'confirm' => 'Wirklich hinzufügen?',
            'icon' => 'plus-square-o'
        ],
        'edit' => [
            'text' => 'Bearbeiten',
            'confirm' => 'Bearbeitung starten?',
            'icon' => 'pencil-square-o'
        ]
    ],

    'menu' => [
        'login' => 'Anmelden',
        'logout' => 'Abmelden',
        'register' => 'Registrieren',
        'reset' => 'Passwort zurücksetzen',
        'admin' => 'Administration'
    ],

    // USER
    'login' => [
        'text' => '@@menu.login',
        'failed' => 'Login fehlgeschlagen! Bitte Überprüfen sie den Benutzernamen und das Passwort.',
        'form' => [
            'username' => 'E-Mail',
            'password' => 'Password',
            'already_loggedin' => 'Sie sind bereits angemeldet',
            'remember' => 'Angemeldet bleiben',
            'success' => 'Login war erfolgreich.',
            'login' => '@@login.text'
        ]
    ],

    'register' => [
        'form' => [
            'headline' => 'Registrierung',
            'username' => '@@login.form.username',
            'password' => '@@login.form.password',
            'compare' => 'Passwort erneut',
            'button' => 'Registrieren'
        ],
        'mail' => [
            'subject' => 'Benutzerkontoaktivierung bei {brand}',
            'body' => [
                'html' => '<h2>Hallo und willkommen bei {brand}!</h2>
<p>Bevor das Benutzerkonto verwendet werden kann, bedarf es einer Aktivierung. Klicken Sie hierzu bitte auf den nachfolgenden Link.</p>
<p><a href="{url.activate}" title="{url.activate}">Hier zu Kontoaktivierung klicken</a></p>
<p>Vielen Dank und auf Wiedersehen</p><p>Das Team von {brand}</p>
<hr>
<p><small><strong>Hinweis:</strong> Wenn sie diese Mail ohne ihr zutun bekommen haben, dann hat sich jemand unter angabe ihrer Mailadresse bei uns auf der Seite registriert.
Das tut uns selbstverständlich leid. Wenn sie nicht aktiv werden, dann wird der Eintrag spätestens nach einigen Tagen ohne Aktivierung autmonatisch gelöscht. Falls sie dies sofort veranlassen möchten, so steht ihnen dies über den nachfolgenden Link zur Verfügung
<a href="{url.deny}" title="{url.deny}">Aktivierung ablehnen und Eintrag löschen!</a></small></p>',
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
            'notice' => 'Dieses Benutzerkonnte muss noch aktiviert werden. Bitte klicken Sie den Aktivierungslink in der an ihre Mailadresse versendeten Aktivierungsmail.',
            'wait' => [
                'headline' => 'Es fehlt noch ein kleiner Schritt...',
                'text' => 'Wir haben eine Aktivierungsmail an die von Ihnen angegebene E-Mailadresse versendet. Bitte überprüfen Sie den Posteingang Ihres E-Mailpostfaches und klicken zum Abschluss der Benutzerregistrierung auf den in der Aktivierungsmail enthaltenen Link.'
            ],
            'done' => [
                'headline' => 'Ihr Benutzerkonto ist nun aktiv!',
                'text' => 'Vielen Dank für ihrer Registierung. Wir heissen Sie als neuen Benutzer willkommen. Sie können sich nun einloggen.'
            ],
            'fail' => [
                'headline' => 'Da ist etwas scheifgelaufen.',
                'text' => 'Leider konnte die aktivierung nicht erfolgreich abgeschlossen werden. Falls der Link in der Aktivierungsmail geklickt wurde, so kann es sein, dass der Link nicht richtig übergeben wurde. Um dies auszuschließen, sollte der Link aus der E-Mail kopiert und in die Adresszeile des Browser eingefügt werden.<br><br>Sollte der Fehler immernoch auftreten, dann steht eine Kontaktaufnahme unter %s zur Verfügung.'
            ],
            'deny' => [
                'ok' => [
                    'headline' => 'Ihr Eintrag wurde gelöscht.',
                    'text' => 'Ihre eingetragene E-Mailadresse wurde nun aus dem System gelöscht.'
                ],
                'nouser' => [
                    'headline' => 'Kein Eintrag zu diesem Schlüssel gefunden.',
                    'text' => 'Entweder wurde der Eintrag bereits gelöscht oder der der Schlüssel ist nicht korrekt.'
                ]
            ]
        ]
    ],

    /**
     * **************************************************************************
     * Admin
     * **************************************************************************
     */
    'admin' => [
        'menu' => [
            'users' => 'Benutzer & Rechte'
        ]
    ],

    /**
     * **************************************************************************
     * CONFIG
     * **************************************************************************
     */
    'config' => [
        'headline' => 'Core Framework Einstellungen',
        'desc' => '',

        'site' => [
            'head' => 'Seite',
            'desc' => '',
            'general' => [
                'name' => [
                    'label' => 'Name (Brand)',
                    'desc' => 'Der Name der Seite, welche als genereller Titel und als Brand an diversen Stellen innerhalb des Frameworks genutzt wird.'
                ],
                'url' => [
                    'label' => 'Basisurl',
                    'desc' => 'Die FQDN Url der Seite. Dieseer MUSS das Protkoll (http(s)://) enthalten und darf keinen abschließenden / enthalten (Beispiel: http://meinedomain.tld).'
                ],
                'webmaster_email' => [
                    'label' => 'Webmaster (Admin) emailaddress',
                    'desc' => 'E-Mailaddresse des Webmasters'
                ]
            ],
            'language' => [
                'head' => 'Sprache',
                'desc' => '',
                'default' => [
                    'label' => 'Standardsprache',
                    'desc' => 'Legt die zu verwendende Standardsprache fest.'
                ]
            ]
        ],

        // Security
        'security' => [
            'head' => 'Sicherheit',
            'desc' => '',
            'encrypt' => [
                'head' => 'Verschlüsselung',
                'salt' => [
                    'label' => 'Salt',
                    'desc' => 'Zeichenkette, mit der Hashwerte generiert werden.'
                ]
            ],
            'login' => [
                'head' => 'Anmeldung',
                'autologin' => [
                    'active' => [
                        'label' => 'Automatische Anmeldung aktiv',
                        'desc' => 'Aktiviert die automatische Anmeldung von Benutzern. Dazu wird im Loginformular ein entsprechendes feld angeboten und innerhalb des Anmeldeprozesses ein Autologin Cookie gesetzt, der bei einer Wiederkehr des Benutzers zur automatischen Anmeldung ausgewertet wird. (Standard: An)'
                    ],
                    'expires_after' => [
                        'label' => 'TTL autom. Anmeldung (in Tagen)',
                        'desc' => 'Alle für eine automatische Anmeldung erforderlichen Daten haben eine Gültigkeit von x Tagen. Nach Ablauf dieser Gültigkeit wird der Benutzer aufgefordert sich erneut anzumelden. Mit jeder automatischen Anmeldung wird diese Frist auf den hier festgelegten Wert zurückgesetzt. (Standard: 30)'
                    ]
                ]
            ],
            'ban' => [
                'head' => 'Bann',
                'active' => [
                    'label' => 'Aktiv',
                    'desc' => 'Schlatet das Bannsystem ein oder aus.'
                ],
                'tries' => [
                    'label' => 'Versuche vor Bann',
                    'desc' => 'When set >0 a ban counter is startet on a ban enabled request. Example: Failed logins. (Default: 0 = no Bans)'
                ],
                'ttl' => [
                    'head' => 'TTL',
                    'log' => [
                        'label' => 'TTL Logeintrag',
                        'desc' => 'Zeit in Sekunden, die ein Bannlogeintrag als relevant angesehen werden soll.'
                    ],
                    'ban' => [
                        'label' => 'TTL Banndauer',
                        'desc' => 'Zeit in Sekunden, die ein Bann dauern soll.'
                    ]
                ]
            ],
            'form' => [
                'head' => 'Formulare',
                'token' => [
                    'label' => 'Tokenname',
                    'desc' => 'Name des Tokens, dass mit jedem Formular mitgesendet werden muss.'
                ]
            ]
        ],

        // User
        'user' => [
            'head' => 'Benutzer',
            'username' => [
                'head' => 'Benutzername',
                'min_length' => [
                    'label' => 'Minimallänge',
                    'desc' => 'Minimale Anzahl an Zeichen für den Benutznamen.'
                ],
                'regexp' => [
                    'label' => 'RegExp',
                    'desc' => 'Regulärer Ausdruck der zur Prüfung auf den Benutzernamen angewendet werden soll.'
                ]
            ],
            'password' => [
                'head' => 'Passwort',
                'min_length' => [
                    'label' => 'Minimallänge',
                    'desc' => 'Minimale Anzahl an Zeichen für das Passwort.'
                ],
                'max_length' => [
                    'label' => 'Maximallänge',
                    'desc' => 'Maximale Anzahl an Zeichen für das Passwort.'
                ],
                'regexp' => [
                    'label' => 'RegExp',
                    'desc' => 'Regulärer Ausdruck der zur Prüfung auf das Passwort angewendet werden soll.'
                ],
                'reactivate_after_password_change' => [
                    'label' => 'Kontoreaktivierung nach Passworwechsel',
                    'desc' => 'Erzwing eine erneute Aktivierung des Benutzkontos nachdem das Passwort vom Benutzer geändert wurde. Dazu wird dem Benutzer an die hinterlegte E-mailadresse eine Aktivierungsmail geschickt.'
                ]
            ],
            'register' => [
                'head' => 'Registrierung',
                'use_compare_password' => [
                    'label' => 'Password Vergleichsfeld',
                    'desc' => 'Switch to control the display and use of a senconde password field to compare passwords before sending it to DB.'
                ]
            ],
            'activation' => [
                'head' => 'Activation Settings',
                'use' => [
                    'label' => 'Activate via',
                    'desc' => 'Switch to de-/activate activation by clicking on activationling send by mail to the user. Turn to off to activte users directly after registration.'
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
                    'name' => [
                        'label' => 'Sender address',
                        'desc' => 'The emailaddress to use when sending activationmail to the user'
                    ],
                    'from' => [
                        'label' => 'From name',
                        'desc' => 'Optional FromName to use when sending activationmail toi the user'
                    ]
                ]
            ]
        ],

        // Startseite
        'home' => [
            'head' => 'Startseite',
        ],

        // Startseite
        'asset' => [
            'head' => 'Assets',
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
                'head' => 'Fehlerbenachrichtigung per Mail',
                'desc' => '',
                'use' => [
                    'label' => 'E-Mail senden?'
                ],
                'mta' => [
                    'head' => 'Empfänger & MTA',
                    'desc' => '',
                    'recipient' => [
                        'label' => 'Empfänger',
                        'desc' => 'An diese/n Empfäger wird die Fehlerbenachrichtigung gesendet. Es ist möglich mehrer Empfänger durch ; getrennt anzugeben. Wenn kein Empfänger angegeben wird, dann wird der Fehlerbericht an den Webmaster versendet.'
                    ],
                    'use' => [
                        'label' => 'Senden über MTA',
                        'desc' => 'Der MTA über die eine Fehlerbenachrichtigung gesendet werden soll.'
                    ]
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
            'ttl' => [
                'head' => 'TTL Settings (in seconds)',
                'desc' => 'time-to-live in seconds for cached files.',
                'js' => [
                    'label' => 'Js files (in seconds)',
                    'desc' => 'TTL for all javacript (.js) files. Default: %s'
                ],
                'css' => [
                    'label' => 'Css files (in seconds)',
                    'desc' => 'TTL for all stylesheet (.css) files. Default: %s'
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
        'singular' => 'Benutzer',
        'plural' => 'Benutzer',
        'icon' => 'user',
        'action' => [
            'edit' => [
                'text' => 'Benutzer bearbeiten'
            ],
            'new' => [
                'text' => 'Neuer Benutzer'
            ]
        ],
        'list' => 'Benutzerliste',
        'field' => [
            'username' => 'Benutzername',
            'display_name' => 'Anzeigename',
            'password' => 'Passwort',
            'groups' => 'Benutzergruppen'
        ],
        'error' => [
            'username' => [
                'in_use' => 'Dieser Benutzname steht nicht zu Verfügung.',
                'min_lenght' => 'Der Benutzname muss mindesten %s Zeichen haben.',
                'regexp' => 'Benutzname enthält ungültige Zeichen.'
            ],
            'password' => [
                'mismatch' => 'Die Passwörter stimmen nicht überein.',
                'regexp' => 'Das Passwort enthält ungültihe Zeichen.',
                'max_length' => 'Das Passwort darf maximal %s Zeichen lang sein.',
                'range' => 'Das passwort muss zwischen %s und %s Zeichen lang sein.'
            ]
        ]
    ],

    /**
     * **************************************************************************
     * GROUP
     * **************************************************************************
     */
    'group' => [
        'singular' => 'Gruppe',
        'plural' => 'Gruppen',
        'members' => 'Mitglieder',
        'icon' => 'users',
        'action' => [
            'edit' => [
                'text' => 'Gruppe bearbeiten'
            ],
            'new' => [
                'text' => 'Neue Gruppe'
            ]
        ],
        'list' => 'Gruppenliste',
        'field' => [
            'id_group' => 'Gruppen ID',
            'title' => 'Name',
            'display_name' => 'Anzeigename'
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
                'text' => 'Gruppenberechtigung bearbeiten'
            ],
            'new' => [
                'text' => 'Neue Gruppenberechtigung'
            ]
        ],
        'field' => [
            'permission' => 'permission.singular',
            'notes' => 'Notizen'
        ]
    ],

    /**
     * **************************************************************************
     * Permissions
     * **************************************************************************
     */
    'permission' => [
        'singular' => 'Zugriffsrecht',
        'plural' => 'Zugriffsrechte',
        'admin' => [
            'text' => 'Administrator',
            'desc' => 'Gewährt administrativen Zugriff auf alle (!) Bereiche der Seite. Das Betrifft auch alle Apps.'
        ],
        'config' => [
            'text' => 'Konfiguration',
            'desc' => 'Gewährt Zugriff auf alle (!) Konfigurationsbereiche der Seite. Das Betrifft auch alle Apps.'
        ]
    ],

    /**
     * **************************************************************************
     * VALIDATORS
     * ****************************************************************************
     */
    'validator' => [
        'rule' => [
            'required' => 'Dieses Feld darf nicht fehlen existieren.',
            'empty' => 'Dieses Feld darf nicht leer sein',
            // Strings
            'textrange' => 'Der Inhalt muss zwischen %d und %d Zeichen lang sein. Der aktuelle Inhalt hat %d Zeichen.',
            'textminlength' => 'Der Inhalt muss mindestens %d Zeichen lang sein.',
            'textmaxlength' => 'Der Inhalt darf maximal %d Zeichen lang sein.',
            // Dates
            'date_iso' => 'Datum im ISO Format (YYYY-MM-DD) erforderlich.',
            'date' => 'Bitte ein gültiges Datum eingeben.',
            // Time
            'time24' => 'Zeitangabe im 24 Stunden Format (HH:II:ss) erwartet.',
            // Number
            'compare' => 'Comparecheck failed. Checked: $1 $3 $2',
            'numbermin' => 'Die Zahl darf nicht kleiner als %d sein.',
            'numbermax' => 'Die Zahl darf nicht größer als %d sein.',
            'numberrange' => 'Die Zahl muss zwischen %d und %d liegen.',
            // Email
            'email' => 'Dies ist keine gültige E-Mailadresse.',
            'email_dnscheck' => 'Der  E-MailHost "%s" ist unbekannt bzw existiert nicht.'
        ]
    ],

    /**
     * **************************************************************************
     * TIMESTRINGS
     * ****************************************************************************
     */
    'time' => [
        'text' => [
            'ago' => 'vor %s %s'
        ],
        'strings' => [
            '__preserve' => true,
            'year' => 'Jahr',
            'years' => 'Jahre',
            'month' => 'Monat',
            'months' => 'Monate',
            'week' => 'Woche',
            'weeks' => 'Wochen',
            'day' => 'Tag',
            'days' => 'Tage',
            'hour' => 'Stunde',
            'hours' => 'Stunden',
            'minute' => 'Minute',
            'minutes' => 'Minuten',
            'second' => 'Sekunde',
            'seconds' => 'Sekunden'
        ],
        'months' => [
            '__preserve' => true,
            1 => 'Januar',
            2 => 'Februar',
            3 => 'März',
            4 => 'April',
            5 => 'Mai',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'August',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Dezember'
        ],
        'days' => [
            '__preserve' => true,
            0 => 'Sontag',
            1 => 'Montag',
            2 => 'Dienstag',
            3 => 'Mittwoch',
            4 => 'Donnerstag',
            5 => 'Freitag',
            6 => 'Samstag'
        ]
    ]
];
