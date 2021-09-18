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
            $counter = 0;
            $skipIds = [];
            /**
             * @var \Illuminate\Database\Connection $db
             */
            $db = $this->getService('db');
            while ($counter++ <= 5000) {
                $query = $db->table('module_zxrelease')
                    ->select('id')
                    ->where('parsed', '=', 0)
                    ->where('fileName', '!=', '')
                    ->whereNotIn('id', $skipIds)
                    ->limit(1)
                    ->orderBy('id');
                if ($record = $query->first()) {
                    echo $counter . ' ';
                    echo $record['id'] . ' ';
                    /**
                     * @var zxReleaseElement $releaseElement
                     */
                    if ($releaseElement = $structureManager->getElementById($record['id'])) {
                        $releaseElement->parsed = 1;
                        $releaseElement->persistElementData();

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
                        $releaseElement->persistElementData();
                    } else {
                        echo 'skipped ';
                        $skipIds[] = $record['id'];
                    }
                    echo '<br />';
                    flush();
                }
            }
        }
    }
}

