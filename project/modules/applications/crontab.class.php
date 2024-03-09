<?php

use App\Queue\QueueType;
use App\Queue\QueueService;
use Illuminate\Database\Connection;

class crontabApplication extends controllerApplication
{
    protected $applicationName = 'crontab';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    private $structureManager;
    private string $logFilePath;


    public function initialize()
    {
        ignore_user_abort(1);
        set_time_limit(60 * 6);
        $this->createRenderer();
    }

    public function execute($controller)
    {
        $pathsManager = $this->getService(PathsManager::class);
        $todayDate = date('Y-m-d');
        $this->logFilePath = $pathsManager->getPath('logs') . 'cron' . $todayDate . '.txt';

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
            $this->recalculate();
            $this->parseReleases();
            $this->parseArtItems('module_zxpicture', 'image', 'originalName');
            $this->parseArtItems('module_zxmusic', 'file', 'fileName');
//            $this->queryAiItems();
        }
    }

    private function recalculate()
    {
        /**
         * @var QueueService $queueService
         */
        $queueService = $this->getService('QueueService');
        $start_time = microtime(true);

        $limit = 5000;

        while ($limit) {
            $limit--;
            $records = $queueService->getNextElement(QueueType::RECALCULATION);
            foreach ($records as $record) {
                $elementId = $record['elementId'];

                $queueService->updateStatus($elementId, QueueType::RECALCULATION, QueueService::STATUS_INPROGRESS);

                $status = QueueService::STATUS_FAIL;
                if ($element = $this->structureManager->getElementById($elementId)) {
                    if ($element instanceof Recalculable) {
                        $element->recalculate();
                        $end_time = microtime(true);
                        $execution_time = $end_time - $start_time;
                        $status = QueueService::STATUS_SUCCESS;
                        $this->logMessage('Recalculated ' . $element->getId() . ' ' . $element->structureType . ' ' . $element->getTitle(), round($execution_time));
                    }
                } else {
                    $this->logMessage('Unable to read ' . $elementId, 0);
                }
                $queueService->updateStatus($elementId, QueueType::RECALCULATION, $status);
            }
        }
    }
    

    private function logMessage($message, $seconds)
    {
        $text = date('Y-m-d H:i:s') . " - " . $seconds . " - " . $message;
        echo $text . '<br/>';
        file_put_contents($this->logFilePath, $text . PHP_EOL, FILE_APPEND);
    }

    private function queryAiItems()
    {
        /**
         * @var AiManager $aiManager
         */
        $aiManager = $this->getService(AiManager::class);
        $languagesManager = $this->getService(LanguagesManager::class);
        $spa = $languagesManager->getLanguageId('spa');
        $eng = $languagesManager->getLanguageId('eng');
        $rus = $languagesManager->getLanguageId('rus');
        /**
         * @var Connection $db
         */
        $db = $this->getService('db');
        $counter = 0;

        $query = $db->table('module_zxprod')
            ->select('id')
            ->where('hasAiData', '!=', 1)
            ->where('legalStatus', '!=', 'mia')
            ->limit(20)
            ->orderBy('votes', 'desc');
        $records = $query->get();
        $totalExecution = 0;

        if ($records) {
            foreach ($records as $record) {
                $counter++;
                /**
                 * @var zxProdElement $prodElement
                 */
//                if ($prodElement = $this->structureManager->getElementById(406707)) {
                if ($prodElement = $this->structureManager->getElementById($record['id'])) {
                    $start_time = microtime(true);

                    $this->logMessage($counter . ' AI request start ' . $prodElement->id . ' ' . $prodElement->title, 0);
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

                    $end_time = microtime(true);
                    $execution_time = $end_time - $start_time;
                    $totalExecution += $execution_time;

                    $this->logMessage($counter . ' AI request success ' . $prodElement->id . ' ' . $prodElement->title, round($execution_time));
                } else {
                    $this->logMessage($counter . ' AI request prod not found ' . $record['id'], 0);
                }

                if ($totalExecution > 5 * 60) {
                    $this->logMessage(' AI requesting completed with total execution time ' . $totalExecution, 0);
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
         * @var Connection $db
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
                    $start_time = microtime(true);
                    /**
                     * @var ZxArtItem $element
                     */
                    if ($element = $this->structureManager->getElementById($record['id'])) {
                        $result = $element->updateMd5($this->getService('PathsManager')->getPath('uploads') . $element->$fileColumn, $element->$fileNameColumn);
                        if (!$result) {
                            $skipIds[] = $record['id'];
                            $this->logMessage($counter . ' parse art item ' . $record['id'] . ' ' . $element->getId() . ' ' . $element->getTitle() . ' - file not found', 0);
                        } else {
                            $end_time = microtime(true);
                            $this->logMessage($counter . ' parse art item ' . $record['id'] . ' ' . $element->getId() . ' ' . $element->getTitle(), round($end_time - $start_time, 2));
                        }
                    } else {
                        $skipIds[] = $record['id'];
                        $this->logMessage($counter . ' parse art item ' . $record['id'] . ' - skipped ', 0);
                    }
                }
            } else {
                break;
            }
        }
    }

    private function parseReleases()
    {
        /**
         * @var Connection $db
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
                    $start_time = microtime(true);
                    /**
                     * @var zxReleaseElement $releaseElement
                     */
                    if ($releaseElement = $this->structureManager->getElementById($record['id'])) {
                        $this->logMessage($counter . ' parse release ' . $record['id'] . ' started ' . $releaseElement->id . ' ' . $releaseElement->title, 0);
                        $releaseElement->updateFileStructure();
                        $end_time = microtime(true);
                        $this->logMessage($counter . ' parse release ' . $record['id'] . ' ' . $releaseElement->id . ' ' . $releaseElement->title, round($end_time - $start_time, 2));
                    } else {
                        $skipIds[] = $record['id'];
                        $this->logMessage($counter . ' parse release ' . $record['id'] . ' - skipped ', 0);
                    }
                }
            } else {
                break;
            }
        }
    }
}

