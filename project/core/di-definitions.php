<?php
declare(strict_types=1);

use App\Paths\PathsManager;
use DI\Container;
use Illuminate\Database\Connection;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ZxArt\Shared\Http\DnsHostResolver;
use ZxArt\Shared\Http\HostResolver;
use ZxArt\Shared\Http\PublicAddressGuard;
use ZxArt\Shared\Http\UrlGuard;
use ZxArt\Shared\Serializer\RequestDenormalizerFactory;
use ZxArt\Ai\ChunkProcessor;
use ZxArt\Assistant\Tools\Catalogues\FindTagsTool;
use ZxArt\Assistant\Tools\Catalogues\ListCategoriesTool;
use ZxArt\Assistant\Tools\Catalogues\ListHardwareTool;
use ZxArt\Assistant\Tools\Catalogues\ListOptionsTool;
use ZxArt\Assistant\Tools\Entities\GetAuthorTool;
use ZxArt\Assistant\Tools\Entities\GetAuthorAliasTool;
use ZxArt\Assistant\Tools\Entities\GetGroupTool;
use ZxArt\Assistant\Tools\Entities\GetGroupAliasTool;
use ZxArt\Assistant\Tools\Prods\CreateProdTool;
use ZxArt\Assistant\Tools\Prods\UpdateProdTool;
use ZxArt\Assistant\Tools\Prods\DeleteProdTool;
use ZxArt\Assistant\Tools\Prods\JoinProdsTool;
use ZxArt\Assistant\Tools\Prods\SetProdAuthorTool;
use ZxArt\Assistant\Tools\Prods\RemoveProdAuthorTool;
use ZxArt\Assistant\Tools\Releases\AddReleaseFilesTool;
use ZxArt\Assistant\Tools\Releases\CreateReleaseTool;
use ZxArt\Assistant\Tools\Releases\DeleteReleaseFileTool;
use ZxArt\Assistant\Tools\Releases\DeleteReleaseTool;
use ZxArt\Assistant\Tools\Releases\RemoveReleaseAuthorTool;
use ZxArt\Assistant\Tools\Releases\SetReleaseAuthorTool;
use ZxArt\Assistant\Tools\Releases\SetReleaseFileTool;
use ZxArt\Assistant\Tools\Releases\UpdateReleaseTool;
use ZxArt\Assistant\Tools\Prods\AddProdFilesTool;
use ZxArt\Assistant\Tools\Prods\DeleteProdFileTool;
use ZxArt\Assistant\Tools\Authors\ConvertAuthorToGroupTool;
use ZxArt\Assistant\Tools\Authors\CreateAuthorTool;
use ZxArt\Assistant\Tools\Authors\DeleteAuthorTool;
use ZxArt\Assistant\Tools\Authors\JoinAuthorsTool;
use ZxArt\Assistant\Tools\Authors\UpdateAuthorTool;
use ZxArt\Assistant\Tools\AuthorAliases\ConvertAuthorAliasToAuthorTool;
use ZxArt\Assistant\Tools\AuthorAliases\CreateAuthorAliasTool;
use ZxArt\Assistant\Tools\AuthorAliases\DeleteAuthorAliasTool;
use ZxArt\Assistant\Tools\AuthorAliases\JoinAuthorAliasesTool;
use ZxArt\Assistant\Tools\AuthorAliases\UpdateAuthorAliasTool;
use ZxArt\Assistant\Tools\Groups\ConvertGroupToAuthorTool;
use ZxArt\Assistant\Tools\Groups\CreateGroupTool;
use ZxArt\Assistant\Tools\Groups\DeleteGroupTool;
use ZxArt\Assistant\Tools\Groups\JoinGroupsTool;
use ZxArt\Assistant\Tools\Groups\RemoveGroupMemberTool;
use ZxArt\Assistant\Tools\Groups\SetGroupMemberTool;
use ZxArt\Assistant\Tools\Groups\UpdateGroupTool;
use ZxArt\Assistant\Tools\GroupAliases\ConvertGroupAliasToGroupTool;
use ZxArt\Assistant\Tools\GroupAliases\CreateGroupAliasTool;
use ZxArt\Assistant\Tools\GroupAliases\DeleteGroupAliasTool;
use ZxArt\Assistant\Tools\GroupAliases\JoinGroupAliasesTool;
use ZxArt\Assistant\Tools\GroupAliases\RemoveGroupAliasMemberTool;
use ZxArt\Assistant\Tools\GroupAliases\SetGroupAliasMemberTool;
use ZxArt\Assistant\Tools\GroupAliases\UpdateGroupAliasTool;
use ZxArt\Assistant\Tools\Web\FetchUrlTool;
use ZxArt\Assistant\Tools\Web\ViewImageTool;
use ZxArt\Assistant\Tools\Entities\GetProdTool;
use ZxArt\Assistant\Tools\Entities\GetReleaseTool;
use ZxArt\Assistant\Tools\Search\FindLocationsTool;
use ZxArt\Assistant\Tools\Search\FindPartiesTool;
use ZxArt\Assistant\Tools\Search\SearchAuthorsTool;
use ZxArt\Assistant\Tools\Search\SearchGroupsTool;
use ZxArt\Assistant\Tools\Search\SearchProdsTool;
use ZxArt\Assistant\Tools\Search\SearchReleasesTool;
use ZxArt\Assistant\Tools\Search\SearchTool;
use ZxArt\Assistant\Tools\ToolRegistry;
use ZxArt\Assistant\Tools\Skills\LoadSkillTool;
use ZxArt\Assistant\Skills\SkillCatalogue;
use ZxArt\Ai\Service\PressArticleParser;
use ZxArt\Ai\Service\PressArticleSeo;
use ZxArt\Ai\Service\ProdQueryService;
use ZxArt\Ai\Service\PromptSender;
use ZxArt\Assistant\Llm\LlmClient;
use ZxArt\Assistant\Llm\OpenAiLlmClient;
use ZxArt\Ai\Service\TextBeautifier;
use ZxArt\Ai\Service\Translator;
use ZxArt\Comments\CommentTranslationAiService;
use ZxArt\Comments\CommentTranslationService;
use ZxArt\AuthorList\AuthorListService;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Services\AuthorAliasFormService;
use ZxArt\Authors\Services\AuthorAliasYearNormalizer;
use ZxArt\Authors\Services\AuthorImportService;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Comments\CommentsService;
use ZxArt\Controllers\Crontab;
use ZxArt\Controllers\Pouet;
use ZxArt\Controllers\Rss;
use ZxArt\Controllers\Sam;
use ZxArt\Controllers\Socialpost;
use ZxArt\Controllers\Zxdb;
use ZxArt\GroupList\GroupListService;
use ZxArt\Geo\GeoService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Logs\Log;
use ZxArt\Prods\ProdArticlesService;
use ZxArt\Prods\ProdCoreService;
use ZxArt\Prods\ProdElementService;
use ZxArt\Prods\ProdMediaService;
use ZxArt\Prods\ProdRelatedProdsService;
use ZxArt\Prods\ProdReleasesService;
use ZxArt\Prods\ScreenshotMoveService;
use ZxArt\Screenshots\ScreenshotUploadService;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Ratings\RatingsService;
use ZxArt\Social\SocialPostsService;
use ZxArt\Stats\Services\StatsService;
use ZxArt\Telegram\PostService;
use function DI\autowire;
use function DI\factory;

return [
    AuthorListService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    GroupListService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    GeoService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    StatsService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    CommentsService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    AuthorshipDbal::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    AuthorAliasFormService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    AuthorAliasYearNormalizer::class => autowire(),
    RatingsService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),

    ProdElementService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    ProdCoreService::class => autowire(),
    ProdReleasesService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    ProdMediaService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    ProdArticlesService::class => autowire(),
    ProdRelatedProdsService::class => autowire(),
    ScreenshotMoveService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),
    ScreenshotUploadService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),

    // Controllers with custom StructureManager bindings
    Rss::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager')),

    Pouet::class => autowire()
        ->constructorParameter('adminStructureManager', DI\get('adminStructureManager')),
    Sam::class => autowire()
        ->constructorParameter('adminStructureManager', DI\get('adminStructureManager')),
    Zxdb::class => autowire()
        ->constructorParameter('adminStructureManager', DI\get('adminStructureManager')),
    Crontab::class => autowire()
        ->constructorParameter('structureManager', DI\get('adminStructureManager')),
    CommentTranslationService::class => autowire()
        ->constructorParameter('structureManager', DI\get('adminStructureManager')),

    Socialpost::class => autowire()
        ->constructorParameter('logger', DI\get('social_posts_logger')),

    // Core services
    Logger::class => factory(static function (PathsManager $pathsManager) {
        $logger = new Logger('log');

        $todayDate = date('Y-m-d');
        $logFilePath = $pathsManager->getPath('logs') . $todayDate . '.log';
        $streamHandler = new StreamHandler($logFilePath, Logger::DEBUG);
        $formatter = new LineFormatter(null, null, true, true);
        $streamHandler->setFormatter($formatter);
        $logger->pushHandler($streamHandler);

        return $logger;
    }),
    // the environment serves a local copy, which has no live config
    'openai_key' => factory(fn(ConfigManager $cm) => $cm->getConfig('main')->get('ai_key') ?: (string)getenv('OPENAI_API_KEY')),
    LlmClient::class => factory(static fn(ContainerInterface $c, ConfigManager $cm): LlmClient => new OpenAiLlmClient(
        OpenAI::client((string)$c->get('openai_key')),
        (string)$cm->getConfig('main')->get('ai_assistant_model'),
        $c->get('create_log')('assistant_logs'),
    )),
    'telegram_token' => factory(fn(ConfigManager $cm) => $cm->getConfig('telegram')->get('token')),
    'telegram_channel_id' => factory(fn(ConfigManager $cm) => $cm->getConfig('telegram')->get('channel_id')),

    // Factory functions
    'create_log' => factory(function (ContainerInterface $c, PathsManager $pm) {
        return fn(string $logPath) => new Log(
            logger: new Logger('log'),
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
    'comment_translation_prompt_sender' => factory(static fn(ContainerInterface $c) => $c->get('create_prompt_sender')('comment_translation_logs')),

    // ChunkProcessors
    'parser_chunk_processor' => factory(static fn(ContainerInterface $c) => new ChunkProcessor($c->get('parser_prompt_sender'))),
    'beautifier_chunk_processor' => factory(static fn(ContainerInterface $c) => new ChunkProcessor($c->get('text_beautifier_prompt_sender'))),
    'translator_chunk_processor' => factory(static fn(ContainerInterface $c) => new ChunkProcessor($c->get('translator_prompt_sender'))),

    // Main services
    ProdQueryService::class => autowire()->constructor(DI\get('prod_query_prompt_sender')),
    PressArticleParser::class => autowire()->constructor(DI\get('parser_chunk_processor')),
    TextBeautifier::class => autowire()->constructor(DI\get('beautifier_chunk_processor')),
    Translator::class => autowire()->constructor(DI\get('translator_chunk_processor')),
    CommentTranslationAiService::class => autowire()->constructor(DI\get('comment_translation_prompt_sender')),
    PressArticleSeo::class => autowire()->constructor(DI\get('press_article_seo_prompt_sender')),
    SocialPostsService::class => autowire()
        ->constructorParameter('structureManager', DI\get('publicStructureManager'))
        ->constructorParameter('logger', DI\get('social_posts_logger')),
    PostService::class => autowire()
        ->constructorParameter('token', DI\get('telegram_token'))
        ->constructorParameter('channelId', DI\get('telegram_channel_id')),

    // Legacy services migrated from project/services/
    PicturesModesManager::class => autowire(),
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
        ->method('setAuthorsManager', DI\get(AuthorImportService::class))
        ->method('setGroupsService', DI\get(GroupsService::class))
        ->method('setCountriesManager', DI\get(CountriesManager::class)),
    ZxaaaManager::class => autowire()
        ->method('setProdsService', DI\get(ProdsService::class))
        ->method('setAuthorsManager', DI\get(AuthorImportService::class))
        ->method('setGroupsService', DI\get(GroupsService::class))
        ->method('setCountriesManager', DI\get(CountriesManager::class)),

    // Turns JSON request bodies into typed request DTOs; see the factory for why
    // it is configured the way it is.
    SerializerInterface::class => factory(static fn(): SerializerInterface => RequestDenormalizerFactory::create()),

    // the assistant's tools; a tool is listed here to be callable
    SkillCatalogue::class => autowire()->constructor(dirname(__DIR__) . '/assistant-skills'),
    ToolRegistry::class => autowire()->constructor([
        DI\get(LoadSkillTool::class),
        DI\get(SearchTool::class),
        DI\get(FindLocationsTool::class),
        DI\get(FindPartiesTool::class),
        DI\get(SearchProdsTool::class),
        DI\get(SearchReleasesTool::class),
        DI\get(SearchAuthorsTool::class),
        DI\get(SearchGroupsTool::class),
        DI\get(FindTagsTool::class),
        DI\get(ListCategoriesTool::class),
        DI\get(ListHardwareTool::class),
        DI\get(ListOptionsTool::class),
        DI\get(GetProdTool::class),
        DI\get(GetReleaseTool::class),
        DI\get(GetAuthorTool::class),
        DI\get(GetAuthorAliasTool::class),
        DI\get(GetGroupTool::class),
        DI\get(GetGroupAliasTool::class),
        DI\get(FetchUrlTool::class),
        DI\get(ViewImageTool::class),
        DI\get(CreateProdTool::class),
        DI\get(UpdateProdTool::class),
        DI\get(DeleteProdTool::class),
        DI\get(JoinProdsTool::class),
        DI\get(SetProdAuthorTool::class),
        DI\get(RemoveProdAuthorTool::class),
        DI\get(AddReleaseFilesTool::class),
        DI\get(CreateReleaseTool::class),
        DI\get(DeleteReleaseFileTool::class),
        DI\get(DeleteReleaseTool::class),
        DI\get(RemoveReleaseAuthorTool::class),
        DI\get(SetReleaseAuthorTool::class),
        DI\get(SetReleaseFileTool::class),
        DI\get(UpdateReleaseTool::class),
        DI\get(AddProdFilesTool::class),
        DI\get(DeleteProdFileTool::class),
        DI\get(ConvertAuthorToGroupTool::class),
        DI\get(CreateAuthorTool::class),
        DI\get(DeleteAuthorTool::class),
        DI\get(JoinAuthorsTool::class),
        DI\get(UpdateAuthorTool::class),
        DI\get(ConvertAuthorAliasToAuthorTool::class),
        DI\get(CreateAuthorAliasTool::class),
        DI\get(DeleteAuthorAliasTool::class),
        DI\get(JoinAuthorAliasesTool::class),
        DI\get(UpdateAuthorAliasTool::class),
        DI\get(ConvertGroupToAuthorTool::class),
        DI\get(CreateGroupTool::class),
        DI\get(DeleteGroupTool::class),
        DI\get(JoinGroupsTool::class),
        DI\get(RemoveGroupMemberTool::class),
        DI\get(SetGroupMemberTool::class),
        DI\get(UpdateGroupTool::class),
        DI\get(ConvertGroupAliasToGroupTool::class),
        DI\get(CreateGroupAliasTool::class),
        DI\get(DeleteGroupAliasTool::class),
        DI\get(JoinGroupAliasesTool::class),
        DI\get(RemoveGroupAliasMemberTool::class),
        DI\get(SetGroupAliasMemberTool::class),
        DI\get(UpdateGroupAliasTool::class),
    ]),

    HostResolver::class => autowire(DnsHostResolver::class),
    UrlGuard::class => autowire(PublicAddressGuard::class),

    // DependencyInjectionContextTrait users — need explicit setContainer
    votesManager::class => autowire()->method('setContainer', DI\get(Container::class)),
    tagsManager::class => autowire()->method('setContainer', DI\get(Container::class)),
    mp3ConversionManager::class => autowire()->method('setContainer', DI\get(Container::class)),
    ApiQueryResultResolver::class => autowire()->method('setContainer', DI\get(Container::class)),
    ApiQueriesManager::class => autowire()->method('setContainer', DI\get(Container::class)),
];
