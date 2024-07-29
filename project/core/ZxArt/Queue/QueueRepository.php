<?php

namespace ZxArt\Queue;
class QueueRepository
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getNextElementId(QueueType $type): ?int
    {
        $result = $this->db->table('queue')
            ->select('elementId')
            ->where('status', QueueStatus::STATUS_TODO)
            ->where('type', $type->value)
            ->orderBy('elementId', 'desc')
            ->first();

        return $result ? $result['elementId'] : null;
    }

    public function updateStatus(int $elementId, QueueType $type, QueueStatus $status): void
    {
        $this->db->table('queue')
            ->where('elementId', '=', $elementId)
            ->where('type', '=', $type->value)
            ->update(['status' => $status]);
    }

    public function load(int $elementId, array $types): array
    {
        return $this->db->table('queue')
            ->where('elementId', '=', $elementId)
            ->whereIn('type', $types)
            ->get();
    }

    public function addElementRecords(int $elementId, array $types, QueueStatus $status): array
    {
        $data = [];
        foreach ($types as $type) {
            $data[] = [
                'type' => $type,
                'status' => $status,
            ];
        }

        return $this->db->table('queue')
            ->where('elementId', '=', $elementId)
            ->insert($data);
    }

    public function deleteElementRecords(int $elementId, array $types): array
    {
        return $this->db->table('queue')
            ->where('elementId', '=', $elementId)
            ->whereIn('type', $types)
            ->delete();
    }
}