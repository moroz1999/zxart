<?php


use App\Queue\QueueService;

class QueueServiceServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        $db = $this->registry->getService('db');
        return new QueueService($db);
    }

    public function makeInjections($instance)
    {
    }
}