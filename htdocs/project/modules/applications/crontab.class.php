<?php

class crontabApplication extends controllerApplication
{
    protected $applicationName = 'crontab';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    private $structureManager;
    private $aiManager;

    public function initialize()
    {
        ignore_user_abort(1);
        set_time_limit(60 * 5);
        $this->createRenderer();
    }

    public function execute($controller)
    {
        //start this before ending output buffering
        $languagesManager = $this->getService('LanguagesManager');
        $currentLanguageCode = $languagesManager->getCurrentLanguageCode();

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

            $this->structureManager->setRequestedPath([$currentLanguageCode]);
            $this->structureManager->setPrivilegeChecking(false);
            $this->convertMp3();
            $this->parseReleases();
            $this->parseArtItems('module_zxpicture', 'image', 'originalName');
            $this->parseArtItems('module_zxmusic', 'file', 'fileName');
            $this->queryAiItems();
        }
    }

    private function queryAiItems()
    {
        $aiManager = $this->getService(AiManager::class);
        $languagesManager = $this->getService(LanguagesManager::class);
        $spa = $languagesManager->getLanguageId('spa');
        $eng = $languagesManager->getLanguageId('eng');
        $rus = $languagesManager->getLanguageId('rus');
        /**
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');
        $counter = 0;

        $query = $db->table('module_zxprod')
            ->select('id')
            ->where('hasAiData', '!=', 1)
            ->where('legalStatus', '!=', 'mia')
            ->limit(20)
            ->orderBy('votes', 'desc');
//                ->orderBy('id');
        $records = $query->get();
        $totalExecution = 0;

        if ($records) {
            foreach ($records as $record) {
                $counter++;
                echo $counter . ' AI request ';
                echo $record['id'] . ' ';
                /**
                 * @var zxProdElement $prodElement
                 */
                if ($prodElement = $this->structureManager->getElementById($record['id'])) {
//                    if ($prodElement = $this->structureManager->getElementById(404101)) {
                    $start_time = microtime(true);

                    echo $prodElement->id . ' ';
                    echo $prodElement->title . ' ';
                    $metaData = $aiManager->getProdData($prodElement);
                    if ($metaData) {
                        $db->table('module_zxprod_meta')
                            ->updateOrInsert(
                                [
                                    'id' => $prodElement->id,
                                    'languageId' => $spa
                                ],
                                [
                                    'id' => $prodElement->id,
                                    'metaTitle' => $metaData['spa']['pageTitle'],
                                    'h1' => $metaData['spa']['h1'],
                                    'metaDescription' => $metaData['spa']['metaDescription'],
                                    'generatedDescription' => $metaData['spa']['intro'] ?? '',
                                    'languageId' => $spa
                                ]);
                        $db->table('module_zxprod_meta')
                            ->updateOrInsert(
                                [
                                    'id' => $prodElement->id,
                                    'languageId' => $eng
                                ],
                                [
                                    'id' => $prodElement->id,
                                    'metaTitle' => $metaData['eng']['pageTitle'],
                                    'h1' => $metaData['eng']['h1'],
                                    'metaDescription' => $metaData['eng']['metaDescription'],
                                    'generatedDescription' => $metaData['eng']['intro'] ?? '',
                                    'languageId' => $eng
                                ]);
                        $db->table('module_zxprod_meta')
                            ->updateOrInsert(
                                [
                                    'id' => $prodElement->id,
                                    'languageId' => $rus
                                ],
                                [
                                    'id' => $prodElement->id,
                                    'metaTitle' => $metaData['rus']['pageTitle'],
                                    'h1' => $metaData['rus']['h1'],
                                    'metaDescription' => $metaData['rus']['metaDescription'],
                                    'generatedDescription' => $metaData['rus']['intro'] ?? '',
                                    'languageId' => $rus
                                ]);
                        $prodElement->hasAiData = true;
                        $prodElement->persistElementData();
                    }

                }
                $end_time = microtime(true);
                $execution_time = $end_time - $start_time;
                $totalExecution += $execution_time;
                echo ' ' . round($execution_time) . 's<br />';
                flush();

                if ($totalExecution > 5 * 60) {
                    return;
                }
            }

        }

    }

    private function convertMp3()
    {
        /**
         * @var mp3ConversionManager $mp3ConversionManager
         */
        $mp3ConversionManager = $this->getService('mp3ConversionManager');
        $mp3ConversionManager->convertQueueItems();
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
                        echo $releaseElement->id . ' ';
                        echo $releaseElement->title . ' ';
                        $releaseElement->updateFileStructure();
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

