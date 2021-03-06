<?php

class statsApplication extends controllerApplication
{
    protected $applicationName = 'stats';
    public $rendererName = 'smarty';
    public $requestParameters = [
        'id',
        'type',
        'action',
        'view',
        'start',
        'end',
        'types',
        'language',
        'categoryId',
        'number',
        'page',
        'plugin',
    ];

    public function initialize()
    {
        $this->createRenderer();
    }

    public function execute($controller)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "7200");
        $eventsLog = $this->getService('eventsLog');
        $todayStart = strtotime("today");

        $eventsLog->aggregateEvents('view', $todayStart, 'elementId');
        $eventsLog->deleteEvents('view', $todayStart);

        $eventsLog->aggregateEvents('play', $todayStart, 'elementId');
        $eventsLog->deleteEvents('play', $todayStart);

        $eventsLog->aggregateEvents('vote', $todayStart, 'userId');
        $eventsLog->deleteEvents('vote', $todayStart);

        $eventsLog->aggregateEvents('addZxPicture', $todayStart, 'userId');
        $eventsLog->deleteEvents('addZxPicture', $todayStart);

        $eventsLog->aggregateEvents('addZxMusic', $todayStart, 'userId');
        $eventsLog->deleteEvents('addZxMusic', $todayStart);

        $eventsLog->aggregateEvents('comment', $todayStart, 'userId');
        $eventsLog->deleteEvents('comment', $todayStart);

        $eventsLog->aggregateEvents('tagAdded', $todayStart, 'userId');
        $eventsLog->deleteEvents('tagAdded', $todayStart);
    }
}