<?php

class crontabApplication extends controllerApplication
{
    protected $applicationName = 'crontab';
    public $rendererName = 'smarty';
    public $requestParameters = [];
    use ReleaseFormatsProvider;
    use ReleaseFileTypesGatherer;

    private $structureManager;

    public function initialize()
    {
        ignore_user_abort(1);
        set_time_limit(60 * 5);
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
            $this->structureManager = $this->getService(
                'structureManager',
                [
                    'rootUrl' => $controller->rootURL,
                    'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerPublic'),
                ],
                true
            );

            $languagesManager = $this->getService('LanguagesManager');
            $this->structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
            $this->structureManager->setPrivilegeChecking(false);
            $mp3ConversionManager = $this->getService('mp3ConversionManager');
            $mp3ConversionManager->convertQueueItems();

            $this->parseReleases();
            $this->parseArtItems('module_zxpicture', 'image', 'originalName');
            $this->parseArtItems('module_zxmusic', 'file', 'fileName');
        }
    }

    private function parseArtItems($table, $fileColumn, $fileNameColumn)
    {
        /**
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');
        $counter = 0;
        $skipIds = [];

        while ($counter++ <= 10) {
            $query = $db->table($table)
                ->select('id')
                ->where($fileNameColumn, '!=', '')
                ->whereNotIn('id', $skipIds)
                ->whereNotIn('id', function ($query) {
                    $query->from('files_registry')->select('elementId');
                })
                ->limit(500)
                ->orderBy('id');
            $records = $query->get();
            if ($records) {
                foreach ($records as $record) {
                    echo $counter . ' ';
                    echo $record['id'] . ' ';
                    /**
                     * @var ZxArtItem $element
                     */
                    if ($element = $this->structureManager->getElementById($record['id'])) {
                        echo $element->getId() . ' ';
                        echo $element->getTitle() . ' ';
                        $result = $element->updateMd5($this->getService('PathsManager')->getPath('uploads') . $element->$fileColumn, $element->$fileNameColumn);
                        if (!$result) {
                            echo 'file not found';
                            $skipIds[] = $record['id'];
                        }
                    } else {
                        echo 'skipped ';
                        $skipIds[] = $record['id'];
                    }
                    echo '<br />';
                    flush();
                }
            } else {
                break;
            }
        }
    }

    private function parseReleases()
    {
        /**
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');
        $counter = 0;
        $skipIds = [];

        while ($counter++ <= 10) {
            $query = $db->table('module_zxrelease')
                ->select('id')
                ->where('parsed', '!=', 1)
                ->where('fileName', '!=', '')
                ->whereNotIn('id', $skipIds)
                ->limit(500)
                ->orderBy('id');
            $records = $query->get();
            if ($records) {
                foreach ($records as $record) {
                    echo $counter . ' ';
                    echo $record['id'] . ' ';
                    /**
                     * @var zxReleaseElement $releaseElement
                     */
                    if ($releaseElement = $this->structureManager->getElementById($record['id'])) {
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
            } else {
                break;
            }
        }
    }
}

