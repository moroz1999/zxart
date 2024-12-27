<?php

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use ZxArt\Ai\ChunkProcessor;
use ZxArt\Ai\Service\PressArticleParser;
use ZxArt\Ai\Service\PressArticleSeo;
use ZxArt\Ai\Service\ProdQueryService;
use ZxArt\Ai\Service\PromptSender;
use ZxArt\Ai\Service\TextBeautifier;
use ZxArt\Ai\Service\Translator;
use ZxArt\Logs\Log;
use function DI\autowire;
use function DI\factory;

return [
    // Core services
    Logger::class => autowire(Logger::class)->constructor('log'),
    'openai_key' => factory(fn(ConfigManager $cm) => $cm->getConfig('main')->get('ai_key')),

    // Factory functions
    'create_log' => factory(function (ContainerInterface $c, PathsManager $pm) {
        return fn(string $logPath) => new Log(
            logger: $c->make(Logger::class),
            logPath: $pm->getPath($logPath)
        );
    }),

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
    'press_article_seo_chunk_processor' => factory(static fn(ContainerInterface $c) => new ChunkProcessor($c->get('press_article_seo_prompt_sender'))),

    // Main services
    ProdQueryService::class => autowire()->constructor(DI\get('prod_query_prompt_sender')),
    PressArticleParser::class => autowire()->constructor(DI\get('parser_chunk_processor')),
    TextBeautifier::class => autowire()->constructor(DI\get('beautifier_chunk_processor')),
    Translator::class => autowire()->constructor(DI\get('translator_chunk_processor')),
    PressArticleSeo::class => autowire()->constructor(DI\get('press_article_seo_chunk_processor')),
];