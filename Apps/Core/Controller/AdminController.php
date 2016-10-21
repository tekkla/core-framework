<?php
namespace Apps\Core\Controller;

use Core\Framework\Amvc\Controller\Controller;

/**
 * AdminController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class AdminController extends Controller
{

    protected $access = [
        '*' => [
            'admin'
        ]
    ];

    public function Admin()
    {
        $this->setVar([
            'loaded_apps' => $this->model->getApplist(),
            'logs' => '',

            // Links to users and permissions
            'menu' => [
                'users' => [
                    'title' => $this->app->language->get('admin.menu.users'),
                    'links' => [
                        'users' => [
                            'url' => $this->app->url('generic.action', [
                                'app' => 'core',
                                'controller' => 'user',
                                'action' => 'index'
                            ]),
                            'text' => $this->app->language->get('user.plural')
                        ],
                        'groups' => [
                            'url' => $this->app->url('generic.action', [
                                'app' => 'core',
                                'controller' => 'Group',
                                'action' => 'Index'
                            ]),
                            'text' => $this->app->language->get('group.plural')
                        ]
                    ]
                ]
            ]
        ]);
    }
}
