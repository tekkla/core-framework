<?php
namespace Apps\Core\Controller;

use Core\Framework\Amvc\Controller\Controller;

class LogController extends Controller
{

    protected $access = [
        '*' => [
            'admin'
        ]
    ];

    public function Index()
    {
        $this->setVar([
            'headline' => $this->app->language->get('logs.headline'),
            'logs' => $this->app->getController()
                ->run('Logs')
        ]);
        
        $this->setAjaxTarget('#core-admin');
    }

    public function Logs($entries = null)
    {
        if (! $entries) {
            $entries = $this->app->config->get('log.display.entries');
            $entries = 20;
        }
        
        $data = $this->model->getLogs($entries);
        
        $this->setVar([
            'logs' => $data
        ]);
        
        $this->setAjaxTarget('#core-admin-logs');
    }
}

