<?php

use App\Paths\PathsManager;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use ZxArt\Ai\ChunkProcessor;
use ZxArt\Ai\Service\PressArticleParser;
use ZxArt\Ai\Service\PressArticleSeo;
use ZxArt\Ai\Service\ProdQueryService;
use ZxArt\Ai\Service\PromptSender;
use ZxArt\Ai\Service\TextBeautifier;
use ZxArt\Ai\Service\Translator;
use ZxArt\Comments\CommentsService;
use ZxArt\Logs\Log;
use ZxArt\Social\SocialPostsService;
use ZxArt\Telegram\PostService;
use function DI\autowire;
use function DI\factory;

return [
    // CMS services — delegate to legacy registry instead of autowiring
    structureManager::class => factory(static function () {
        return controller::getInstance()->getRegistry()->getService('structureManager');
    }),

    'publicStructureManager' => factory(static function () {
        $controller = controller::getInstance();
        $configManager = $controller->getConfigManager();
        $sm = $controller->getRegistry()->getService('structureManager', [
            'rootUrl' => $controller->baseURL,
            'rootMarker' => $configManager->get('main.rootMarkerPublic'),
        ], true);
        $languagesManager = $controller->getRegistry()->getService('LanguagesManager');
        $sm->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
        return $sm;
    }),

    CommentsService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),

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
    SocialPostsService::class => autowire()->constructorParameter('logger', DI\get('social_posts_logger')),
    PostService::class => autowire()
        ->constructorParameter('token', DI\get('telegram_token'))
        ->constructorParameter('channelId', DI\get('telegram_channel_id')),
];