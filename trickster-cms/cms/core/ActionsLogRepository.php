<?php

use Illuminate\Database\Connection;

class ActionsLogRepository
{
    const table = 'actions_log';

    public function __construct(
        private Connection $db,
    )
    {
    }

    public function getTopUsersByAction(string $moduleType, string $action, ?int $limit = null)
    {
        $sql = $this->db->table(self::table);
        $sql->select(['userId', $this->db->raw('count(userId) as amount')]);
        $sql->where('elementType', '=', $moduleType);
        $sql->where('action', '=', $action);
        if ($limit) {
            $sql->limit($limit);
        }
        $sql->groupBy('userId');
        $sql->orderBy('amount', 'desc');
        return $sql->get();
    }
}