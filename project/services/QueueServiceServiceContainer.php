<?php


use App\Queue\QueueService;

class QueueServiceServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return QueueService
     */
    public function makeInstance()
    {
        $db = $this->registry->getService('db');
        return new QueueService($db);
    }

    /**
     * @return void
     */
    public function makeInjections($instance)
    {
    }
}