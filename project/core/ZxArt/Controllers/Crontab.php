<?php

namespace ZxArt\Controllers;

use Cache;
use controllerApplication;
use Exception;
use Illuminate\Database\Connection;
use LanguagesManager;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use mp3ConversionManager;
use PathsManager;
use pressArticleElement;
use Recalculable;
use rendererPlugin;
use structureManager;
use ZxArt\Ai\QueryFailException;
use ZxArt\Ai\QuerySkipException;
use ZxArt\Ai\Service\PressParser;
use ZxArt\Ai\Service\ProdQueryService;
use ZxArt\Ai\Service\TextBeautifier;
use ZxArt\Ai\Service\Translator;
use ZxArt\Import\Press\DataUpdater\DataUpdater;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\Strings\LanguageDetector;
use ZxArt\ZxProdCategories\Ids;
use zxProdElement;
use zxReleaseElement;

class Crontab extends controllerApplication
{
    protected $applicationName = 'crontab';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    private structureManager $structureManager;
    private string $logFilePath;
    private QueueService $queueService;
    private Connection $db;
    private ?ProdQueryService $prodQueryService;
    private ?array $languages = null;
    private ?Translator $translatorService;
    private ?PressParser $pressParser;
    private ?TextBeautifier $textBeautifier;
    private ?LanguageDetector $languageDetector;
    private ?LanguagesManager $languagesManager;
    private ?Logger $logger;
    private ?DataUpdater $pressDataUpdater;

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
        $this->prodQueryService = $this->getService(ProdQueryService::class);
        $this->textBeautifier = $this->getService(TextBeautifier::class);
        $this->translatorService = $this->getService(Translator::class);
        $this->languageDetector = $this->getService(LanguageDetector::class);
        $this->languagesManager = $this->getService(LanguagesManager::class);
        $this->pressParser = $this->getService(PressParser::class);
        $this->pressDataUpdater = $this->getService(DataUpdater::class);
        $this->logger = new Logger('error_log');
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        $pathsManager = $this->getService(PathsManager::class);
        $todayDate = date('Y-m-d');
        $this->logFilePath = $pathsManager->getPath('logs') . 'cron' . $todayDate . '.txt';
        $streamHandler = new StreamHandler($this->logFilePath, Logger::DEBUG);

        $formatter = new LineFormatter(null, null, true, true);
        $streamHandler->setFormatter($formatter);

        $this->logger->pushHandler($streamHandler);


        //start this before ending output buffering
        $currentLanguageCode = $this->languagesManager->getCurrentLanguageCode();

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
            $this->convertMp3();
            $this->recalculate();
            $this->parseReleases();
            $this->parseArtItems('module_zxpicture', 'image', 'originalName');
            $this->parseArtItems('module_zxmusic', 'file', 'fileName');
//            $this->queryAiSeo();
//            $this->queryAiIntro();
//            $this->queryAiCategories();
//            $this->queryAiPressBeautifier();
//            $this->queryAiPressTranslation();
            $this->queryAiPressParser();
        }
    }

    private function processQueue(QueueType $queueType, callable $processElementCallback): void
    {
        $this->languagesManager->setCurrentLanguageCode('rus');

        $counter = 0;
        $executionLimit = 60 * 5;
        $totalExecution = 0;

        while ($totalExecution <= $executionLimit) {
            $counter++;

            $elementId = $this->queueService->getNextElementId($queueType);
            $elementId = 477718;
            if ($elementId === null) {
                break;
            }

            $this->queueService->updateStatus($elementId, $queueType, QueueStatus::STATUS_INPROGRESS);

            /** @var pressArticleElement $pressArticleElement */
            $pressArticleElement = $this->structureManager->getElementById($elementId);
            if (!$pressArticleElement) {
                $this->queueService->updateStatus($elementId, $queueType, QueueStatus::STATUS_FAIL);
                $this->logMessage("$counter $queueType->value article not found $elementId", 0);
                continue;
            }

            $startTime = microtime(true);
            $this->logMessage("$counter $queueType->value request start {$pressArticleElement->id} {$pressArticleElement->title}", 0);

            try {
                $processElementCallback($pressArticleElement, $counter);
                $this->queueService->updateStatus($elementId, $queueType, QueueStatus::STATUS_SUCCESS);
            } catch (Exception $e) {
                $this->logMessage("$counter $queueType->value request failed. {$e->getMessage()}", 0);
                $this->queueService->updateStatus($elementId, $queueType, QueueStatus::STATUS_FAIL);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            $totalExecution += $executionTime;
            exit;
            $this->logMessage("$counter $queueType->value request completed in " . round($executionTime) . " seconds", 0);
        }

        $this->logMessage("$queueType->value processing completed with total execution time $totalExecution seconds", 0);
    }

    private function queryAiPressParser(): void
    {
        $mergedContent = [
            'shortContent' =>
                [
                    'eng' => 'The article is an overview of the MilleniuM event, focusing on competition results and voting patterns in various categories like graphics and music. The author discusses the fairness and dynamics of the voting process, highlighting notable participants and statistics. It also addresses challenges faced in organizing a PC/Amiga segment for the event.',
                    'spa' => 'El artículo es una revisión del evento MilleniuM, centrándose en los resultados de competencias y patrones de votación en categorías como gráficos y música. El autor discute la equidad y dinámica del proceso de votación, destacando participantes notables y estadísticas. También aborda los desafíos enfrentados en la organización de un segmento de PC/Amiga para el evento.',
                    'rus' => 'Статья - это обзор события MilleniuM, сосредоточенный на результатах соревнований и тенденциях голосования в различных категориях, таких как графика и музыка. Автор обсуждает справедливость и динамику процесса голосования, выделяя заметных участников и статистику. Также рассматриваются проблемы, возникшие при попытке организовать сегмент PC/Amiga для мероприятия.',
                ],
            'articleAuthors' =>
                [
                    0 =>
                        [
                            'nickName' => 'Diver',
                            'groups' =>
                                [
                                    0 =>
                                        [
                                            'name' => '4th Dimension',
                                        ],
                                ],
                        ],
                ],
            'groups' =>
                [
                    0 =>
                        [
                            'name' => 'Concern Chaos',
                            'city' => 'Sydney',
                            'country' => 'Australia',
                            'type' => 'scene',
                        ],
                    1 =>
                        [
                            'name' => 'Raww Arse',
                            'city' => 'Oxford',
                            'country' => 'UK',
                            'type' => 'scene',
                        ],
                    2 =>
                        [
                            'name' => 'WoMo-Team',
                            'city' => 'Cologne',
                            'country' => 'Germany',
                            'type' => 'scene',
                        ],
                    3 =>
                        [
                            'name' => 'Phantasy',
                            'city' => 'Vienna',
                            'country' => 'Austria',
                            'type' => 'scene',
                        ],
                    4 =>
                        [
                            'name' => 'K3L Corp.',
                            'city' => 'Prague',
                            'country' => 'Czech Republic',
                            'type' => 'scene',
                        ],
                    5 =>
                        [
                            'name' => 'Euphoria',
                            'city' => 'Veseli n. M.',
                            'country' => 'Czech Republic',
                            'type' => 'scene',
                        ],
                    6 =>
                        [
                            'name' => 'Claw/Exodus',
                            'city' => 'Sulejowek',
                            'country' => 'Poland',
                            'type' => 'scene',
                        ],
                    7 =>
                        [
                            'name' => 'Hooy-Program',
                            'city' => 'Krakow',
                            'country' => 'Poland',
                            'type' => 'scene',
                        ],
                    8 =>
                        [
                            'name' => 'eTc group/Scene',
                            'city' => 'Черкассы',
                            'country' => 'Ukraine',
                            'type' => 'scene',
                        ],
                    9 =>
                        [
                            'name' => 'Ascendancy CL',
                            'city' => 'Гродно',
                            'country' => 'Беларусь',
                            'type' => 'scene',
                        ],
                    10 =>
                        [
                            'name' => 'Accept Corp.',
                            'city' => 'Армавир',
                            'country' => 'Russia',
                            'type' => 'scene',
                        ],
                    11 =>
                        [
                            'name' => 'Light Future Group',
                            'city' => 'Глазов',
                            'country' => 'Russia',
                            'type' => 'scene',
                        ],
                    12 =>
                        [
                            'name' => 'Brainwave/XPJ',
                            'city' => 'Йошкар-Ола',
                            'country' => 'Russia',
                            'type' => 'scene',
                        ],
                    13 =>
                        [
                            'name' => 'Eternity Industry',
                            'city' => 'Ковров',
                            'country' => 'Russia',
                            'type' => 'scene',
                        ],
                    14 =>
                        [
                            'name' => 'Techno Lab',
                            'city' => 'Краснодар',
                            'country' => 'Russia',
                            'type' => 'scene',
                        ],
                    15 =>
                        [
                            'name' => 'Antares',
                            'city' => 'Москва',
                            'country' => 'Russia',
                            'type' => 'scene',
                        ],
                    16 =>
                        [
                            'name' => 'Diamond group',
                            'city' => 'Москва',
                            'country' => 'Russia',
                            'type' => 'scene',
                        ],
                    17 =>
                        [
                            'name' => 'Digital Reality',
                            'city' => 'Новгород Великий',
                            'country' => 'Russia',
                            'type' => 'scene',
                        ],
                    18 =>
                        [
                            'name' => 'Razzlers',
                            'city' => 'Москва',
                            'country' => 'Russia',
                            'type' => 'scene',
                        ],
                    19 =>
                        [
                            'name' => 'Int. Hackers Group',
                            'city' => 'Новомосковск',
                            'country' => 'Russia',
                            'type' => 'scene',
                        ],
                    20 =>
                        [
                            'name' => '4th Dimension',
                            'city' => 'Москва',
                            'country' => 'Russia',
                            'type' => 'scene',
                        ],
                ],
            'people' =>
                [
                    0 =>
                        [
                            'nickName' => 'Diver',
                            'groups' =>
                                [
                                    0 =>
                                        [
                                            'name' => '4th Dimension',
                                        ],
                                ],
                            'prodRoles' =>
                                [
                                    0 => 'text',
                                ],
                            'groupRoles' =>
                                [
                                    0 => 'organizer',
                                ],
                        ],
                ],
            'parties' =>
                [
                    0 =>
                        [
                            'name' => 'Complex Compo',
                            'year' => '2000',
                        ],
                    1 =>
                        [
                            'name' => 'Forever2E3',
                            'year' => '2000',
                        ],
                ],
            'tunes' =>
                [
                    0 =>
                        [
                            'name' => 'Take My Soul & Oni',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Neuronus',
                                        ],
                                    1 =>
                                        [
                                            'nickName' => 'Baby',
                                        ],
                                ],
                        ],
                    1 =>
                        [
                            'name' => 'Frozen',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Mast',
                                        ],
                                ],
                        ],
                    2 =>
                        [
                            'name' => 'My Heart Will Go On',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Andy Fer',
                                        ],
                                ],
                        ],
                    3 =>
                        [
                            'name' => 'Chronos',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'JoeDassin',
                                        ],
                                ],
                        ],
                    4 =>
                        [
                            'name' => 'Love\'n\'Gun',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'DNK',
                                        ],
                                ],
                        ],
                    5 =>
                        [
                            'name' => 'WhiteRoad remix',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'DNK',
                                        ],
                                ],
                        ],
                    6 =>
                        [
                            'name' => 'Heart (amiga mod)',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Mm<M',
                                        ],
                                ],
                        ],
                    7 =>
                        [
                            'name' => 'He Plays at Funerals and Dances',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Mm<M',
                                        ],
                                ],
                        ],
                    8 =>
                        [
                            'name' => 'Phoney Phrase',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Deep Green',
                                        ],
                                ],
                        ],
                    9 =>
                        [
                            'name' => 'Sexy Potion',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Deep Green',
                                        ],
                                ],
                        ],
                    10 =>
                        [
                            'name' => 'Suggest!ve',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Mm<M',
                                        ],
                                ],
                        ],
                ],
            'pictures' =>
                [
                    0 =>
                        [
                            'name' => 'The Rage To Overcome',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Fil',
                                        ],
                                ],
                        ],
                    1 =>
                        [
                            'name' => 'Titanic',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Fil',
                                        ],
                                ],
                        ],
                    2 =>
                        [
                            'name' => 'Eruption!',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Flying',
                                        ],
                                ],
                        ],
                    3 =>
                        [
                            'name' => 'Paracels',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Paracels',
                                        ],
                                ],
                        ],
                    4 =>
                        [
                            'name' => 'Hazard',
                            'authors' =>
                                [
                                    0 =>
                                        [
                                            'nickName' => 'Hazard',
                                        ],
                                ],
                        ],
                ],
            'tags' =>
                [
                    0 => 'MilleniuM',
                    1 => 'Demoparty',
                    2 => 'ZX Spectrum',
                    3 => 'Graphics',
                    4 => 'Music',
                    5 => 'Voting',
                    6 => 'Competitions',
                    7 => 'Scene',
                    8 => 'Statistics',
                    9 => 'Organization',
                ],
            'title' =>
                [
                    'eng' => 'MilleniuM: Analysis of Competitions',
                    'spa' => 'MilleniuM: Análisis de Competencias',
                    'rus' => 'MilleniuM: разбор полетов',
                ],
            'h1' =>
                [
                    'eng' => 'Detailed Overview of MilleniuM Event',
                    'spa' => 'Visión Detallada del Evento MilleniuM',
                    'rus' => 'Подробный обзор события MilleniuM',
                ],
            'metaDescription' =>
                [
                    'eng' => 'Explore the in-depth results and voting dynamics of the MilleniuM event, covering competitions in graphics and music categories.',
                    'spa' => 'Explora los resultados detallados y la dinámica de votación del evento MilleniuM, abarcando competencias en categorías de gráficos y música.',
                    'rus' => 'Углубленный анализ результатов и динамики голосования на событии MilleniuM, охватывающего конкурсы в категориях графики и музыки.',
                ],
            'pageTitle' =>
                [
                    'eng' => 'MilleniuM Event Results and Analysis',
                    'spa' => 'Resultados y Análisis del Evento MilleniuM',
                    'rus' => 'Анализ результатов мероприятия MilleniuM',
                ],
        ];

        $pressArticleElement = $this->structureManager->getElementById(477718);
        $this->pressDataUpdater->updatePressArticleData($pressArticleElement, $mergedContent);
        return;
        $this->processQueue(QueueType::AI_PRESS_PARSE, function (pressArticleElement $pressArticleElement, $counter) {
            $updatedContent = $this->pressParser->getParsedData($pressArticleElement->getTextContent());
            if ($updatedContent) {
                $mergedContent = $this->mergeArrays($updatedContent);
                $this->pressDataUpdater->updatePressArticleData($pressArticleElement, $mergedContent);
                $this->logMessage("$counter AI Fix content updated for article {$pressArticleElement->id}", 0);
            }
        });
    }

    private function mergeArrays(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            foreach ($item as $key => $value) {
                if (!isset($result[$key])) {
                    $result[$key] = $value;
                    continue;
                }

                if (is_array($value)) {
                    $result[$key] = array_merge($result[$key], $value);
                } elseif (is_string($value)) {
                    $result[$key] .= ' ' . $value;
                } elseif (is_int($value) && $value !== 0) {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    private function queryAiPressBeautifier(): void
    {
        $this->processQueue(QueueType::AI_PRESS_FIX, function (pressArticleElement $pressArticleElement, $counter) {
            $updatedContent = $this->textBeautifier->beautify($pressArticleElement->getTextContent());

            if ($updatedContent) {
                $destLanguageId = $this->languagesManager->getLanguageId('rus');
                $pressArticleElement->setValue('content', $updatedContent, $destLanguageId);
                $pressArticleElement->persistElementData();
                $this->logMessage("$counter AI Fix content updated for article {$pressArticleElement->id}", 0);
            }
        });
    }

    private function queryAiPressTranslation(): void
    {
        $allLanguageCodes = [
            'rus' => ['eng', 'spa'],
            'eng' => ['rus', 'spa'],
            'spa' => ['eng', 'rus'],
        ];

        $this->processQueue(QueueType::AI_PRESS_TRANSLATE, function (pressArticleElement $pressArticleElement, $counter) use ($allLanguageCodes) {
            $content = $pressArticleElement->getTextContent();
            $contentLanguageCode = $this->languageDetector->detectLanguage($content);
            $languageCodes = $allLanguageCodes[$contentLanguageCode] ?? $allLanguageCodes['rus'];

            if (!isset($allLanguageCodes[$contentLanguageCode])) {
                $this->logMessage("$counter AI Translation {$pressArticleElement->id}: Failed to detect language, defaulting to 'rus'", 0);
            }

            foreach ($languageCodes as $languageCode) {
                $this->logMessage("$counter AI Translation request start for {$pressArticleElement->id} to $languageCode", 0);

                try {
                    $translation = $this->translatorService->translate($content, $contentLanguageCode, $languageCode);

                    if ($translation) {
                        $destLanguageId = $this->languagesManager->getLanguageId($languageCode);
                        $pressArticleElement->setValue('content', $translation, $destLanguageId);
                        $pressArticleElement->persistElementData();
                        $this->logMessage("$counter AI Translation successful for {$pressArticleElement->id} to $languageCode", 0);
                    }
                } catch (Exception $e) {
                    $this->logMessage("$counter AI Translation failed for {$pressArticleElement->id} to $languageCode. {$e->getMessage()}", 0);
                    throw $e; // Re-throw to update status to FAIL in the main method
                }
            }
        });
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

        return $this->languages = [
            'spa' => $this->languagesManager->getLanguageId('spa'),
            'eng' => $this->languagesManager->getLanguageId('eng'),
            'rus' => $this->languagesManager->getLanguageId('rus'),
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
            $metaData = $this->prodQueryService->querySeoForProd($prodElement);
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
            $metaData = $this->prodQueryService->queryIntroForProd($prodElement);
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
        $this->languagesManager = $this->getService('LanguagesManager');
        $this->languagesManager->setCurrentLanguageCode('eng');

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
                $metaData = $this->prodQueryService->queryCategoriesAndTagsForProd($prodElement, $queryCategoriesEnabled);
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

