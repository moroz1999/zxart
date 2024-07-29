<?php
declare(strict_types=1);

namespace ZxArt\Queue;

class QueueService
{
    public function __construct(
        private readonly QueueRepository $queueRepository
    )
    {

    }

    public function getNextElementId(QueueType $type): ?int
    {
        return $this->queueRepository->getNextElementId($type);
    }

    public function updateStatus(int $elementId, QueueType $type, QueueStatus $status): void
    {
        $this->queueRepository->updateStatus($elementId, $type, $status);
    }

    public function checkElementInQueue(int $elementId, array $types)
    {
        $records = $this->queueRepository->load($elementId, $types);

        $existingTypes = array_reduce($records, static function ($result, $record) {
            if ($record['status'] !== QueueStatus::STATUS_TODO) {
                $result[] = $result['type'];
            }
            return $result;
        }, []);

        $missingTypes = array_diff($types, $existingTypes);
        if (count($missingTypes) === 0) {
            return;
        }
        $this->queueRepository->addElementRecords($elementId, $missingTypes, QueueStatus::STATUS_TODO);
    }

    public function removeElementFromQueue(int $elementId, array $types)
    {
        $this->queueRepository->deleteElementRecords($elementId, $types);
    }
}