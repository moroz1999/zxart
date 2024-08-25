<?php


use ZxArt\Queue\QueueService;

class QueueServiceServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return QueueService
     */
    public function makeInstance()
    {
        $queueRepository = $this->registry->getService('QueueRepository');
        return new QueueService($queueRepository);
    }

    /**
     * @return void
     */
    public function makeInjections($instance)
    {
    }
}