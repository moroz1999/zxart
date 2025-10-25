<?php

namespace ZxArt\Queue;
/**
 * @property int $id
 */
trait QueueStatusProvider
{
    public function getQueueStatus(QueueType $queueType)
    {
        $queueService = $this->getService(QueueService::class);
        $status = $queueService->getStatus($this->id, $queueType);
        return $status === null ? '' : $status->toString();
    }
}