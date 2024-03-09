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

    /**
     * @return void
     */
    public function initialize()
    {
        $this->createRenderer();
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 60);

        $this->aggregate();
        $this->checkINodes();
    }

    public function aggregate(): void
    {
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

        $eventsLog->aggregateEvents('addZxProd', $todayStart, 'userId');
        $eventsLog->deleteEvents('addZxProd', $todayStart);

        $eventsLog->aggregateEvents('comment', $todayStart, 'userId');
        $eventsLog->deleteEvents('comment', $todayStart);

        $eventsLog->aggregateEvents('tagAdded', $todayStart, 'userId');
        $eventsLog->deleteEvents('tagAdded', $todayStart);
    }

    public function checkINodes(): void
    {
        $path = $this->getService('PathsManager')->getPath('zxCache');
        $this->clearInodes($path);
        $path = $this->getService('PathsManager')->getPath('imagesCache');
        $this->clearInodes($path);
    }

    private function clearInodes($path): void
    {
        if (is_dir($path)) {
            $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
            $count = iterator_count($iterator);
            echo($path . ' ' . $count);
            foreach ($iterator as $file) {
                if ($count > 200000) {
                    $count--;
                    unlink($file);
                } else {
                    break;
                }
            }
        }
    }
}