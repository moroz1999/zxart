<?php
declare(strict_types=1);

use App\Paths\PathsManager;
use App\Users\CurrentUserService;
use CountriesManager;
use Illuminate\Database\Connection;
use LanguagesManager;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use mp3ConversionManager;
use PicturesModesManager;
use Psr\Container\ContainerInterface;
use RzxArchiveManager;
use S4eManager;
use SectionLogics;
use SpeccyMapsManager;
use tagsManager;
use TslabsManager;
use votesManager;
use ZxaaaManager;
use ZxArt\Ai\ChunkProcessor;
use ZxArt\Ai\Service\PressArticleParser;
use ZxArt\Ai\Service\PressArticleSeo;
use ZxArt\Ai\Service\ProdQueryService;
use ZxArt\Ai\Service\PromptSender;
use ZxArt\Ai\Service\TextBeautifier;
use ZxArt\Ai\Service\Translator;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Comments\CommentsService;
use ZxArt\Controllers\Rss;
use ZxArt\Controllers\Socialpost;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Logs\Log;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Ratings\RatingsService;
use ZxArt\Social\SocialPostsService;
use ZxArt\Telegram\PostService;
use function DI\autowire;
use function DI\factory;

return [
    CommentsService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    AuthorshipRepository::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    RatingsService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),

    // Controllers with publicStructureManager
    Rss::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    Socialpost::class => autowire()
        ->constructorParameter('logger', DI\get('social_posts_logger')),

    // Core services
    Logger::class => autowire(Logger::class)->constructor('log'),
    'openai_key' => factory(fn(ConfigManager $cm) => $cm->getConfig('main')->get('ai_key')),
    'telegram_token' => factory(fn(ConfigManager $cm) => $cm->getConfig('telegram')->get('token')),
    'telegram_channel_id' => factory(fn(ConfigManager $cm) => $cm->getConfig('telegram')->get('channel_id')),

    // Factory functions
    'create_log' => factory(function (ContainerInterface $c, PathsManager $pm) {
        return fn(string $logPath) => new Log(
            logger: $c->make(Logger::class),
            logPath: $pm->getPath($logPath)
        );
    }),

    'social_posts_logger' => factory(static function (PathsManager $pathsManager) {
        $logger = new Logger('social_posts');

        $todayDate = date('Y-m-d');
        $logFilePath = $pathsManager->getPath('logs') . 'social_posts' . $todayDate . '.log';
        $streamHandler = new StreamHandler($logFilePath, Logger::DEBUG);
        $formatter = new LineFormatter(null, null, true, true);
        $streamHandler->setFormatter($formatter);
        $logger->pushHandler($streamHandler);

        return $logger;
    }
    ),

    'create_prompt_sender' => factory(static function (ContainerInterface $c) {
        return static fn(string $logPath) => new PromptSender(
            apiKey: $c->get('openai_key'),
            log: $c->get('create_log')($logPath)
        );
    }),

    // PromptSenders
    'parser_prompt_sender' => factory(static fn(ContainerInterface $c) => $c->get('create_prompt_sender')('press_parser_logs')),
    'text_beautifier_prompt_sender' => factory(static fn(ContainerInterface $c) => $c->get('create_prompt_sender')('text_beautifier_logs')),
    'prod_query_prompt_sender' => factory(static fn(ContainerInterface $c) => $c->get('create_prompt_sender')('prod_query_logs')),
    'translator_prompt_sender' => factory(static fn(ContainerInterface $c) => $c->get('create_prompt_sender')('translator_logs')),
    'press_article_seo_prompt_sender' => factory(static fn(ContainerInterface $c) => $c->get('create_prompt_sender')('press_article_seo_logs')),

    // ChunkProcessors
    'parser_chunk_processor' => factory(static fn(ContainerInterface $c) => new ChunkProcessor($c->get('parser_prompt_sender'))),
    'beautifier_chunk_processor' => factory(static fn(ContainerInterface $c) => new ChunkProcessor($c->get('text_beautifier_prompt_sender'))),
    'translator_chunk_processor' => factory(static fn(ContainerInterface $c) => new ChunkProcessor($c->get('translator_prompt_sender'))),

    // Main services
    ProdQueryService::class => autowire()->constructor(DI\get('prod_query_prompt_sender')),
    PressArticleParser::class => autowire()->constructor(DI\get('parser_chunk_processor')),
    TextBeautifier::class => autowire()->constructor(DI\get('beautifier_chunk_processor')),
    Translator::class => autowire()->constructor(DI\get('translator_chunk_processor')),
    PressArticleSeo::class => autowire()->constructor(DI\get('press_article_seo_prompt_sender')),
    SocialPostsService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager'))
        ->constructorParameter('logger', DI\get('social_posts_logger')),
    PostService::class => autowire()
        ->constructorParameter('token', DI\get('telegram_token'))
        ->constructorParameter('channelId', DI\get('telegram_channel_id')),

    // Legacy services migrated from project/services/
    CountriesManager::class => autowire(),
    mp3ConversionManager::class => autowire(),
    votesManager::class => autowire(),
    tagsManager::class => autowire(),
    PicturesModesManager::class => factory(static function (CurrentUserService $currentUserService) {
        $instance = new PicturesModesManager();
        $instance->setUser($currentUserService->getCurrentUser());
        return $instance;
    }),
    RzxArchiveManager::class => autowire()
        ->method('setProdsService', DI\get(ProdsService::class)),
    SpeccyMapsManager::class => autowire()
        ->method('setProdsService', DI\get(ProdsService::class))
        ->method('setDb', DI\get(Connection::class)),
    SectionLogics::class => autowire()
        ->method('setStructureManager', DI\get('publicStructureManager'))
        ->method('setLanguagesManager', DI\get(LanguagesManager::class)),
    S4eManager::class => autowire()
        ->method('setProdsService', DI\get(ProdsService::class))
        ->method('setAuthorsManager', DI\get(AuthorsService::class))
        ->method('setGroupsService', DI\get(GroupsService::class))
        ->method('setCountriesManager', DI\get(CountriesManager::class)),
    TslabsManager::class => autowire()
        ->method('setProdsService', DI\get(ProdsService::class))
        ->method('setAuthorsManager', DI\get(AuthorsService::class))
        ->method('setGroupsService', DI\get(GroupsService::class))
        ->method('setCountriesManager', DI\get(CountriesManager::class)),
    ZxaaaManager::class => autowire()
        ->method('setProdsService', DI\get(ProdsService::class))
        ->method('setAuthorsManager', DI\get(AuthorsService::class))
        ->method('setGroupsService', DI\get(GroupsService::class))
        ->method('setCountriesManager', DI\get(CountriesManager::class)),
];