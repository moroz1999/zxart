<?php
declare(strict_types=1);

namespace ZxArt\Queue;

use Illuminate\Database\Connection;

readonly class QueueRepository
{
    public function __construct(
        private Connection $db
    )
    {
    }

    public function getNextElementId(QueueType $type): ?int
    {
        $result = $this->db->table('queue')
            ->select('elementId')
            ->where('status', QueueStatus::STATUS_TODO->value)
            ->where('type', $type->value)
            ->orderBy('elementId', 'desc')
            ->first();

        return $result !== null ? $result['elementId'] : null;
    }

    public function updateStatus(int $elementId, QueueType $type, QueueStatus $status): void
    {
        $this->db->table('queue')
            ->where('elementId', '=', $elementId)
            ->where('type', '=', $type->value)
            ->update(['status' => $status->value]);
    }

    public function insertStatus(int $elementId, QueueType $type, QueueStatus $status): void
    {
        $this->db->table('queue')
            ->insert([
                'elementId' => $elementId,
                'status' => $status->value,
                'type' => $type->value,
            ]);
    }

    public function load(int $elementId, array $types): array
    {
        $stringTypes = array_map(fn($type) => $type->value, $types);
        return $this->db->table('queue')
            ->where('elementId', '=', $elementId)
            ->whereIn('type', $stringTypes)
            ->get();
    }

    public function loadStatus(int $elementId, QueueType $type): ?QueueStatus
    {
        $value = $this->db->table('queue')
            ->where('elementId', '=', $elementId)
            ->where('type', '=', $type->value)
            ->select('status')
            ->value('status');
        if ($value !== null) {
            return QueueStatus::from($value);
        }

        return null;
    }

    public function addElementRecords(int $elementId, array $types, QueueStatus $status): bool
    {
        $data = [];
        foreach ($types as $type) {
            $data[] = [
                'elementId' => $elementId,
                'type' => $type->value,
                'status' => $status->value,
            ];
        }

        return $this->db->table('queue')
            ->where('elementId', '=', $elementId)
            ->insert($data);
    }

    public function deleteElementRecords(int $elementId, array $types): int
    {
        $stringTypes = array_map(fn($type) => $type->value, $types);
        return $this->db->table('queue')
            ->where('elementId', '=', $elementId)
            ->whereIn('type', $stringTypes)
            ->delete();
    }
}