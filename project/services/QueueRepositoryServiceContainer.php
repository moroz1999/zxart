<?php


use ZxArt\Queue\QueueRepository;

class QueueRepositoryServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return QueueRepository
     */
    public function makeInstance()
    {
        $db = $this->registry->getService('db');
        return new QueueRepository($db);
    }

    /**
     * @return void
     */
    public function makeInjections($instance)
    {
    }
}