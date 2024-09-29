<?php

use Illuminate\Database\Connection;
use ZxArt\Ai\QueryFailException;
use ZxArt\Ai\QuerySkipException;
use ZxArt\Ai\ProdQueryService as AiQueueService;
use ZxArt\Ai\TextBeautifier;
use ZxArt\Ai\TranslatorService;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\Strings\LanguageDetector;
use ZxArt\ZxProdCategories\Ids;

class crontabApplication extends controllerApplication
{
    protected $applicationName = 'crontab';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    private $structureManager;
    private string $logFilePath;
    private QueueService $queueService;
    private Connection $db;
    private ?AiQueueService $aiQueryService;
    private ?array $languages = null;
    private ?TranslatorService $translatorService;
    private ?TextBeautifier $textBeautifier;
    private ?LanguageDetector $languageDetector;

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
        $this->aiQueryService = $this->getService(AiQueueService::class);
        $this->textBeautifier = $this->getService(TextBeautifier::class);
        $this->translatorService = $this->getService(TranslatorService::class);
        $this->languageDetector = $this->getService(LanguageDetector::class);
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

        /**
         * @var rendererPlugin $renderer
         */
        $renderer = $this->getService('renderer');
        $renderer->endOutputBuffering();
        while (ob_get_level()) {
            ob_end_flush();
        }

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
//            $this->convertMp3();
//            $this->recalculate();
//            $this->parseReleases();
//            $this->parseArtItems('module_zxpicture', 'image', 'originalName');
//            $this->parseArtItems('module_zxmusic', 'file', 'fileName');
//            $this->queryAiSeo();
//            $this->queryAiIntro();
//            $this->queryAiCategories();
            $this->queryAiPressBeautifier();
            $this->queryAiPressTranslation();
        }
    }

    private function queryAiPressBeautifier(): void
    {
        $languagesManager = $this->getService('LanguagesManager');
        $languagesManager->setCurrentLanguageCode('rus');

        $counter = 0;
        $executionLimit = 60 * 5;
        $totalExecution = 0;
        while ($totalExecution <= $executionLimit) {
            $counter++;

            $elementId = $this->queueService->getNextElementId(QueueType::AI_PRESS_FIX);
            if ($elementId === null) {
                return;
            }
            $this->queueService->updateStatus($elementId, QueueType::AI_PRESS_FIX, QueueStatus::STATUS_INPROGRESS);

            /**
             * @var pressArticleElement $pressArticleElement
             */
            $pressArticleElement = $this->structureManager->getElementById($elementId);
            if (!$pressArticleElement) {
                $this->queueService->updateStatus($elementId, QueueType::AI_PRESS_FIX, QueueStatus::STATUS_FAIL);
                $this->logMessage($counter . ' AI Fix article not found ' . $elementId, 0);
                continue;
            }
            $startTime = microtime(true);
            $updatedContent = null;
            $this->logMessage($counter . ' AI Fix request start ' . $pressArticleElement->id . ' ' . $pressArticleElement->title, 0);
            try {
                $updatedContent = $this->textBeautifier->beautify(html_entity_decode($pressArticleElement->getFormattedContent()));
            } catch (Exception $e) {
                $this->logMessage($counter . ' AI Fix request failed. ' . $e->getMessage(), 0);
                $this->queueService->updateStatus($elementId, QueueType::AI_PRESS_FIX, QueueStatus::STATUS_FAIL);
            }
            if ($updatedContent) {
                $destLanguageId = $languagesManager->getLanguageId('rus');
                $pressArticleElement->setValue('content', $updatedContent, $destLanguageId);
                $pressArticleElement->persistElementData();
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            $totalExecution += $executionTime;
            $this->logMessage($counter . ' AI Fix request success ' . $pressArticleElement->id . ' ' . $pressArticleElement->title, round($executionTime));

            $this->queueService->updateStatus($elementId, QueueType::AI_PRESS_FIX, QueueStatus::STATUS_SUCCESS);
        }
        $this->logMessage(' AI Fix requesting completed with total execution time ' . $totalExecution, 0);
    }

    private function queryAiPressTranslation(): void
    {
        $languagesManager = $this->getService('LanguagesManager');
        $languagesManager->setCurrentLanguageCode('rus');

        $allLanguageCodes = [
            'rus' => ['eng', 'spa'],
            'eng' => ['rus', 'spa'],
            'spa' => ['eng', 'rus'],
        ];

        $counter = 0;
        $executionLimit = 60 * 5;
        $totalExecution = 0;
        while ($totalExecution <= $executionLimit) {
            $counter++;

            $elementId = $this->queueService->getNextElementId(QueueType::AI_PRESS_TRANSLATE);
            if ($elementId === null) {
                return;
            }
            $this->queueService->updateStatus($elementId, QueueType::AI_PRESS_TRANSLATE, QueueStatus::STATUS_INPROGRESS);

            /**
             * @var pressArticleElement $pressArticleElement
             */
            $pressArticleElement = $this->structureManager->getElementById($elementId);
            if (!$pressArticleElement) {
                $this->queueService->updateStatus($elementId, QueueType::AI_PRESS_TRANSLATE, QueueStatus::STATUS_FAIL);
                $this->logMessage($counter . ' AI Translation article not found ' . $elementId, 0);
                continue;
            }
            $content = $pressArticleElement->getFormattedContent();
            $contentLanguageCode = $this->languageDetector->detectLanguage($content);
            $languageCodes = $allLanguageCodes[$contentLanguageCode];
            if (!$languageCodes) {
                $this->logMessage($counter . ' AI Translation request failed for ' . $elementId . '. Failed to detect language', 0);
                continue;
            }
            foreach ($languageCodes as $languageCode) {
                $startTime = microtime(true);
                $translation = null;
                $this->logMessage($counter . ' AI Translation request start ' . $pressArticleElement->id . ' ' . $pressArticleElement->title . ' ' . $languageCode, 0);
                try {
                    $translation = $this->translatorService->translate($pressArticleElement->getFormattedContent(), $contentLanguageCode, $languageCode);
                } catch (Exception $e) {
                    $this->logMessage($counter . ' AI Translation request failed. ' . $e->getMessage(), 0);
                    $this->queueService->updateStatus($elementId, QueueType::AI_PRESS_TRANSLATE, QueueStatus::STATUS_FAIL);
                }
                if ($translation) {
                    $destLanguageId = $languagesManager->getLanguageId($languageCode);
                    $pressArticleElement->setValue('content', $translation, $destLanguageId);
                    $pressArticleElement->persistElementData();
                }

                $endTime = microtime(true);
                $executionTime = $endTime - $startTime;
                $totalExecution += $executionTime;
                $this->logMessage($counter . ' AI Translation request success ' . $pressArticleElement->id . ' ' . $pressArticleElement->title, round($executionTime));
            }
            $this->queueService->updateStatus($elementId, QueueType::AI_PRESS_TRANSLATE, QueueStatus::STATUS_SUCCESS);
        }
        $this->logMessage(' AI Translation requesting completed with total execution time ' . $totalExecution, 0);
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
        flush();
        echo $text . '<br/>';
        file_put_contents($this->logFilePath, $text . PHP_EOL, FILE_APPEND);
    }

    private function getLanguages(): array
    {
        if ($this->languages !== null) {
            return $this->languages;
        }
        $languagesManager = $this->getService(LanguagesManager::class);

        return $this->languages = [
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

            if (!$prodElement) {
                $this->queueService->updateStatus($elementId, QueueType::AI_SEO, QueueStatus::STATUS_FAIL);
                $this->logMessage($counter . ' AI SEO request prod not found ' . $elementId, 0);
                continue;
            }
            $startTime = microtime(true);

            $this->logMessage($counter . ' AI SEO request start ' . $prodElement->id . ' ' . $prodElement->title, 0);
            $metaData = $this->aiQueryService->querySeoForProd($prodElement);
            if ($metaData === null) {
                $this->logMessage($counter . ' AI SEO request wrong response ' . $prodElement->id . ' ' . $prodElement->title, 0);
                continue;
            }
            $this->updateProdSeo($prodElement->id, $metaData);
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

            if (!$prodElement) {
                $this->queueService->updateStatus($elementId, QueueType::AI_INTRO, QueueStatus::STATUS_FAIL);
                $this->logMessage($counter . ' AI Intro request prod not found ' . $elementId, 0);
                continue;
            }
            $startTime = microtime(true);

            $this->logMessage($counter . ' AI Intro request start ' . $prodElement->id . ' ' . $prodElement->title, 0);
            $metaData = $this->aiQueryService->queryIntroForProd($prodElement);
            if ($metaData === null) {
                $this->logMessage($counter . ' AI Intro request wrong response ' . $prodElement->id . ' ' . $prodElement->title, 0);
                continue;
            }
            $this->updateProdIntro($prodElement->id, $metaData);

            $this->queueService->updateStatus($elementId, QueueType::AI_INTRO, QueueStatus::STATUS_SUCCESS);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            $totalExecution += $executionTime;
            $this->logMessage($counter . ' AI Intro request success ' . $prodElement->id . ' ' . $prodElement->title, round($executionTime));
        }
        $this->logMessage(' AI Intro requesting completed with total execution time ' . $totalExecution, 0);
    }

    private function isQueryCategoriesEnabled(zxProdElement $prodElement): bool
    {
        $disabledCategories = [
            Ids::GAME_PINBALL->value,
        ];
        $parentCategoriesMap = $prodElement->getParentCategoriesMap();
        foreach ($disabledCategories as $disabledCategory) {
            if (isset($parentCategoriesMap[$disabledCategory])) {
                return false;
            }
        }
        return true;
    }

    private function queryAiCategories(): void
    {
        $languagesManager = $this->getService('LanguagesManager');
        $languagesManager->setCurrentLanguageCode('eng');

        $counter = 0;
        $executionLimit = 60 * 5;
        $totalExecution = 0;
        while ($totalExecution <= $executionLimit) {
            $counter++;

            $elementId = $this->queueService->getNextElementId(QueueType::AI_CATEGORIES_TAGS);
            if ($elementId === null) {
                return;
            }
            $this->queueService->updateStatus($elementId, QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_INPROGRESS);

            /**
             * @var zxProdElement $prodElement
             */
            $prodElement = $this->structureManager->getElementById($elementId);
            $queryCategoriesEnabled = $this->isQueryCategoriesEnabled($prodElement);
            if (!$prodElement) {
                $this->queueService->updateStatus($elementId, QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_FAIL);
                $this->logMessage($counter . ' AI Categories request prod not found ' . $elementId, 0);
                continue;
            }
            $startTime = microtime(true);
            $metaData = null;
            $this->logMessage($counter . ' AI Categories request start ' . $prodElement->id . ' ' . $prodElement->title, 0);
            try {
                $metaData = $this->aiQueryService->queryCategoriesAndTagsForProd($prodElement, $queryCategoriesEnabled);
            } catch (QueryFailException $e) {
                $this->logMessage($counter . ' AI Categories request failed. ' . $e->getMessage(), 0);
                $this->queueService->updateStatus($elementId, QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_FAIL);
            } catch (QuerySkipException $e) {
                $this->logMessage($counter . ' AI Categories request skipped. ' . $e->getMessage(), 0);
                $this->queueService->updateStatus($elementId, QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);
            }
            if ($metaData) {
                $this->updateProdCategoriesAndTags($prodElement, $metaData, $queryCategoriesEnabled);
            }
            $this->queueService->updateStatus($elementId, QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SUCCESS);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            $totalExecution += $executionTime;
            $this->logMessage($counter . ' AI Categories request success ' . $prodElement->id . ' ' . $prodElement->title, round($executionTime));
        }
        $this->logMessage(' AI Categories requesting completed with total execution time ' . $totalExecution, 0);
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

    private function updateProdCategoriesAndTags(zxProdElement $prodElement, array $metaData, bool $queryCategories): void
    {
        if ($queryCategories) {
            $prodElement->categories = [$metaData['category']];
            $prodElement->checkAndPersistCategories();
        }
        $prodElement->addTags($metaData['tags']);
        $prodElement->persistElementData();
    }

    private function updateProdSeo(int $prodElementId, array $metaData): void
    {
        $languages = $this->getLanguages();
        foreach ($languages as $language => $languageId) {
            $this->db->table('module_zxprod_meta')
                ->updateOrInsert(
                    [
                        'id' => $prodElementId,
                        'languageId' => $languageId,
                    ],
                    [
                        'id' => $prodElementId,
                        'metaTitle' => $metaData[$language]['pageTitle'],
                        'h1' => $metaData[$language]['h1'],
                        'metaDescription' => $metaData[$language]['metaDescription'],
                        'languageId' => $languageId,
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
                        'languageId' => $languageId,
                    ],
                    [
                        'id' => $prodElementId,
                        'generatedDescription' => $metaData[$language]['intro'] ?? '',
                        'languageId' => $languageId,
                    ]
                );
        }
    }
}

