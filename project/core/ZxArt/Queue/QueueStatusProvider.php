<?php

namespace ZxArt\Queue;
/**
 * @property int $id
 */
trait QueueStatusProvider
{
    public function getQueueStatus(QueueType $queueType)
    {
        /**
         * @var QueueService $queueService
         */
        $queueService = $this->getService('QueueService');
        $status = $queueService->getStatus($this->id, $queueType);
        return $status === null ? '' : $status->toString();
    }
}