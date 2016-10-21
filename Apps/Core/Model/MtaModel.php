<?php
namespace Apps\Core\Model;

use Core\Framework\Amvc\Model\Model;

class MtaModel extends Model
{

    protected $scheme = [
        'table' => 'core_mtas',
        'primary' => 'id_mta',
        'fields' => [
            'id_mta' => [
                'type' => 'int'
            ],
            'title' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ]
            ],
            'description' => [
                'type' => 'string'
            ],
            'host' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ]
            ],
            'port' => [
                'type' => 'number'
            ],
            'username' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ]
            ],
            'password' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ]
            ],
            'smtp_secure' => [
                'type' => 'string',
                'validate' => [
                    'empty',
                    [
                        'enum',
                        [
                            'none',
                            'tls',
                            'ssl'
                        ]
                    ]
                ]
            ],
            'is_default' => [
                'type' => 'int',
                'validate' => [
                    'empty',
                    [
                        'enum',
                        [
                            0,
                            1
                        ]
                    ]
                ]
            ],
            'type' => [
                'type' => 'int',
                'validate' => [
                    'empty',
                    [
                        'enum',
                        [
                            1,
                            2,
                            3
                        ]
                    ]
                ]
            ],
            'smtp_auth' => [
                'type' => 'int',
                'validate' => [
                    'empty',
                    [
                        'enum',
                        [
                            0,
                            1
                        ]
                    ]
                ]
            ],
            'smtp_options' => [
                'type' => 'string'
            ]
        ]
    ];

    public function getMtaIdTitleList()
    {
        $db = $this->getDbConnector();
        $db->qb([
            'scheme' => $this->scheme,
            'fields' => [
                'title',
                'is_default'
            ],
            'order' => 'is_default DESC'
        ]);

        $mtalist = $db->fetchAll();

        $out = [];

        foreach ($mtalist as $mta) {
            $out[$mta['title']] = $mta['is_default'] == 1 ? $mta['title'] . ' (' . $this->app->language->get('default') . ')' : $mta['title'];
        }

        return $out;
    }
}
