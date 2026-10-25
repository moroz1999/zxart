<?php
declare(strict_types=1);

namespace ZxArt\Queue;

use ZxArt\Queue\Dbal\QueueDbal;

readonly class QueueService
{
    public function __construct(
        private QueueDbal $queueDbal,
    )
    {

    }

    public function getNextElementId(QueueType $type): ?int
    {
        return $this->queueDbal->getNextElementId($type);
    }

    public function updateStatus(int $elementId, QueueType $type, QueueStatus $status): void
    {
        $records = $this->queueDbal->load($elementId, [$type]);
        if ($records) {
            $this->queueDbal->updateStatus($elementId, $type, $status);
        } else {
            $this->queueDbal->insertStatus($elementId, $type, $status);
        }
    }

    public function checkElementInQueue(int $elementId, array $types)
    {
        $records = $this->queueDbal->load($elementId, $types);

        $existingTypes = array_reduce($records, static function ($result, $record) {
            $result[] = $record['type'];
            return $result;
        }, []);
        $missingTypes = [];
        foreach ($types as $type) {
            if (!in_array($type->value, $existingTypes, true)) {
                $missingTypes[] = $type;
            }
        }
        if (count($missingTypes) === 0) {
            return;
        }
        $this->queueDbal->addElementRecords($elementId, $missingTypes, QueueStatus::STATUS_TODO);
    }

    public function getStatus(int $elementId, QueueType $type): ?QueueStatus
    {
        return $this->queueDbal->loadStatus($elementId, $type);
    }

    /**
     * @return int[]
     */
    public function getUpcomingElementIds(QueueType $type, int $limit): array
    {
        return $this->queueDbal->getUpcomingElementIds($type, $limit);
    }

    public function removeElementFromQueue(int $elementId, array $types)
    {
        $this->queueDbal->deleteElementRecords($elementId, $types);
    }
}
