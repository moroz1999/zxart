<?php

namespace ZxArt\Queue;
class QueueService
{
    protected $db;

    const STATUS_TODO = 0;
    const STATUS_INPROGRESS = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = 3;


    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getNextElement(QueueType $type): array
    {
        return $this->db->table('queue')
            ->select('elementId')
            ->where('status', '=', self::STATUS_TODO)
            ->where('type', '=', $type->value)
            ->limit(1)
            ->orderBy('elementId', 'desc')
            ->get();
    }

    public function updateStatus(int $elementId, QueueType $type, string $status): void
    {
        $this->db->table('queue')
            ->where('elementId', '=', $elementId)
            ->where('type', '=', $type->value)
            ->update(['status' => $status]);
    }
}