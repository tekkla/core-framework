<?php
namespace Apps\Core\Model;

class LogModel extends AbstractCoreModel
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

