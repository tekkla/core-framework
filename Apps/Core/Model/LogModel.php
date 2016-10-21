<?php
namespace Apps\Core\Model;

use Core\Framework\Amvc\Model\Model;

class LogModel extends Model
{

    public function getLogs($entries = 20)
    {
        $qb = [
            'table' => 'core_logs',
            'order' => 'logdate DESC',
            'limit' => $entries
        ];
        
        $db = $this->getDbConnector();
        $db->qb($qb);
        
        return $db->all();
    }
}

