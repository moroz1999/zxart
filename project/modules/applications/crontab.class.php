<?php

use Illuminate\Database\Connection;
use ZxArt\Ai\AiQueryService;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;

class crontabApplication extends controllerApplication
{
    protected $applicationName = 'crontab';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    private $structureManager;
    private string $logFilePath;
    private QueueService $queueService;
    private Connection $db;

    /**
     * @return void
     */
    public function initialize()
    {
        ignore_user_abort(1);
        set_time_limit(60 * 6);
        $this->createRenderer();
        $this->queueService = $this->getService('QueueService');
        $this->db = $this->getService('db');
    }

    /**
     * @return void
     */
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
            $this->queryAiSeo();
            $this->queryAiIntro();
        }
    }

    private function recalculate(): void
    {
        $start_time = microtime(true);

        $limit = 5000;

        while ($limit) {
            $limit--;
            $elementId = $this->queueService->getNextElementId(QueueType::RECALCULATION);
            if ($elementId === null) {
                return;
            }
            $this->queueService->updateStatus($elementId, QueueType::RECALCULATION, QueueStatus::STATUS_INPROGRESS);

            $status = QueueStatus::STATUS_FAIL;
            if ($element = $this->structureManager->getElementById($elementId)) {
                if ($element instanceof Recalculable) {
                    $element->recalculate();
                    $end_time = microtime(true);
                    $execution_time = $end_time - $start_time;
                    $status = QueueStatus::STATUS_SUCCESS;
                    $this->logMessage('Recalculated ' . $element->getId() . ' ' . $element->structureType . ' ' . $element->getTitle(), round($execution_time));
                }
            } else {
                $this->logMessage('Unable to read ' . $elementId, 0);
            }
            $this->queueService->updateStatus($elementId, QueueType::RECALCULATION, $status);
        }
    }

    private function logMessage(string $message, int|float $seconds): void
    {
        $text = date('Y-m-d H:i:s') . " - " . $seconds . " - " . $message;
        echo $text . '<br/>';
        file_put_contents($this->logFilePath, $text . PHP_EOL, FILE_APPEND);
    }

    private function getLanguages(): array
    {
        $languagesManager = $this->getService(LanguagesManager::class);

        return [
            'spa' => $languagesManager->getLanguageId('spa'),
            'eng' => $languagesManager->getLanguageId('eng'),
            'rus' => $languagesManager->getLanguageId('rus'),
        ];
    }

    /**
     * @return void
     */
    private function queryAiSeo(): void
    {
        /**
         * @var AiQueryService $aiManager
         */
        $aiManager = $this->getService(AiQueryService::class);

        $counter = 0;
        $executionLimit = 60 * 2;
        $totalExecution = 0;
        while ($totalExecution <= $executionLimit) {
            $counter++;

            $elementId = $this->queueService->getNextElementId(QueueType::AI_SEO);
            if ($elementId === null) {
                return;
            }
            $this->queueService->updateStatus($elementId, QueueType::AI_SEO, QueueStatus::STATUS_INPROGRESS);

            /**
             * @var zxProdElement $prodElement
             */
            $prodElement = $this->structureManager->getElementById($elementId);
//            $prodElement = $this->structureManager->getElementById(406707);
            if (!$prodElement) {
                $this->queueService->updateStatus($elementId, QueueType::AI_SEO, QueueStatus::STATUS_FAIL);
                $this->logMessage($counter . ' AI SEO request prod not found ' . $elementId, 0);
                continue;
            }
            $startTime = microtime(true);

            $this->logMessage($counter . ' AI SEO request start ' . $prodElement->id . ' ' . $prodElement->title, 0);
            $metaData = $aiManager->querySeoForProd($prodElement);
            if ($metaData) {
                $this->updateProdSeo($prodElement->id, $metaData);
            }
            $this->queueService->updateStatus($elementId, QueueType::AI_SEO, QueueStatus::STATUS_SUCCESS);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            $totalExecution += $executionTime;
            $this->logMessage($counter . ' AI SEO request success ' . $prodElement->id . ' ' . $prodElement->title, round($executionTime));
        }
        $this->logMessage(' AI SEO requesting completed with total execution time ' . $totalExecution, 0);
    }

    /**
     * @return void
     */
    private function queryAiIntro(): void
    {
        /**
         * @var AiQueryService $aiManager
         */
        $aiManager = $this->getService(AiQueryService::class);

        $counter = 0;
        $executionLimit = 60 * 2;
        $totalExecution = 0;
        while ($totalExecution <= $executionLimit) {
            $counter++;

            $elementId = $this->queueService->getNextElementId(QueueType::AI_INTRO);
            if ($elementId === null) {
                return;
            }
            $this->queueService->updateStatus($elementId, QueueType::AI_INTRO, QueueStatus::STATUS_INPROGRESS);

            /**
             * @var zxProdElement $prodElement
             */
            $prodElement = $this->structureManager->getElementById($elementId);
//            $prodElement = $this->structureManager->getElementById(406707);
            if (!$prodElement) {
                $this->queueService->updateStatus($elementId, QueueType::AI_INTRO, QueueStatus::STATUS_FAIL);
                $this->logMessage($counter . ' AI Intro request prod not found ' . $elementId, 0);
                continue;
            }
            $startTime = microtime(true);

            $this->logMessage($counter . ' AI Intro request start ' . $prodElement->id . ' ' . $prodElement->title, 0);
            $metaData = $aiManager->queryIntroForProd($prodElement);
            if ($metaData) {
                $this->updateProdIntro($prodElement->id, $metaData);
            }

            $this->queueService->updateStatus($elementId, QueueType::AI_INTRO, QueueStatus::STATUS_SUCCESS);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            $totalExecution += $executionTime;
            $this->logMessage($counter . ' AI Intro request success ' . $prodElement->id . ' ' . $prodElement->title, round($executionTime));
        }
        $this->logMessage(' AI Intro requesting completed with total execution time ' . $totalExecution, 0);
    }

    private function convertMp3(): void
    {
        /**
         * @var mp3ConversionManager $mp3ConversionManager
         */
        $mp3ConversionManager = $this->getService('mp3ConversionManager');
        $mp3ConversionManager->convertQueueItems();
    }

    private function parseArtItems(string $table, string $fileColumn, string $fileNameColumn): void
    {
        $counter = 0;
        $skipIds = [];

        while ($counter++ <= 10) {
            $query = $this->db->table($table)
                ->select('id')
                ->where($fileNameColumn, '!=', '')
                ->whereNotIn('id', $skipIds)
                ->whereNotIn('id', function ($query) {
                    $query->from('files_registry')->select('elementId');
                })
                ->limit(500)
                ->orderBy('id');
            $records = $query->get();
            if (!$records) {
                break;

            }

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
        }
    }

    private function parseReleases(): void
    {
        $counter = 0;
        $skipIds = [];

        while ($counter++ <= 10) {
            $query = $this->db->table('module_zxrelease')
                ->select('id')
                ->where('parsed', '!=', 1)
                ->where('fileName', '!=', '')
                ->whereNotIn('id', $skipIds)
                ->limit(500)
                ->orderBy('id');
            $records = $query->get();
            if (!$records) {
                break;
            }
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
        }
    }

    private function updateProdSeo(int $prodElementId, array $metaData): void
    {
        $languages = $this->getLanguages();
        foreach ($languages as $language => $languageId) {
            $this->db->table('module_zxprod_meta')
                ->updateOrInsert(
                    [
                        'id' => $prodElementId,
                        'languageId' => $languageId
                    ],
                    [
                        'id' => $prodElementId,
                        'metaTitle' => $metaData[$language]['pageTitle'],
                        'h1' => $metaData[$language]['h1'],
                        'metaDescription' => $metaData[$language]['metaDescription'],
                        'languageId' => $languageId
                    ]
                );
        }
    }

    private function updateProdIntro(int $prodElementId, array $metaData): void
    {
        $languages = $this->getLanguages();
        foreach ($languages as $language => $languageId) {
            $this->db->table('module_zxprod_meta')
                ->updateOrInsert(
                    [
                        'id' => $prodElementId,
                        'languageId' => $languageId
                    ],
                    [
                        'id' => $prodElementId,
                        'generatedDescription' => $metaData[$language]['intro'] ?? '',
                        'languageId' => $languageId
                    ]
                );
        }
    }
}

