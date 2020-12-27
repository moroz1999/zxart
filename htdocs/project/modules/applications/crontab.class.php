<?php

class crontabApplication extends controllerApplication
{
    protected $applicationName = 'crontab';
    public $rendererName = 'smarty';
    public $requestParameters = [];
    use ReleaseFormatsProvider;
    use ReleaseFileTypesGatherer;

    public function initialize()
    {
        ignore_user_abort(1);
        set_time_limit(60 * 60);
        $this->startSession('crontab');
        $this->createRenderer();
    }

    public function execute($controller)
    {
        /**
         * @var Cache $cache
         */
        $cache = $this->getService('Cache');
        $cache->enable(false, false, true);

        $renderer = $this->getService('renderer');
        $renderer->endOutputBuffering();

        $user = $this->getService('user');
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);
            $structureManager = $this->getService(
                'structureManager',
                [
                    'rootUrl' => $controller->rootURL,
                    'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerAdmin'),
                ],
                true
            );

            $mp3ConversionManager = $this->getService('mp3ConversionManager');
            $mp3ConversionManager->convertQueueItems();

            $db = $this->getService('db');
            $query = $db->table('module_zxrelease')
                ->select('id')
                ->where('parsed', '=', 0)
                ->where('fileName', '!=', '')
                ->limit(10000)
                ->orderBy('id');
            if ($records = $query->get()) {
                $counter = 0;
                //                $records = [['id'=>105567]];
                foreach ($records as $record) {
                    echo $counter . ' ';
                    echo $record['id'] . ' ';
                    /**
                     * @var zxReleaseElement $releaseElement
                     */
                    if ($releaseElement = $structureManager->getElementById($record['id'])) {
                        echo $releaseElement->id . ' ';
                        echo $releaseElement->title . ' ';
                        /**
                         * @var ZxParsingManager $zxParsingManager
                         */
                        $zxParsingManager = $this->getService('ZxParsingManager');
                        $zxParsingManager->deleteFileStructure($releaseElement->getId());
                        if ($structure = $zxParsingManager->saveFileStructure(
                            $releaseElement->getId(),
                            $releaseElement->getFilePath(),
                            $releaseElement->fileName
                        )) {
                            if ($files = $this->gatherReleaseFiles($structure)) {
                                $files = array_unique($files);
                                $releaseElement->releaseFormat = $files;
                            }
                        }
                        $releaseElement->parsed = 1;
                        $releaseElement->persistElementData();
                    }
                    echo '<br />';
                    flush();

                    $counter++;
                }
            }
        }
    }
}

