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
use ZxArt\Assistant\Catalogues\FindTagsAction;
use ZxArt\Assistant\Catalogues\ListCategoriesAction;
use ZxArt\Assistant\Catalogues\ListHardwareAction;
use ZxArt\Assistant\Catalogues\ListOptionsAction;
use ZxArt\Assistant\Authors\GetAuthorAction;
use ZxArt\Assistant\Authors\GetAuthorAliasAction;
use ZxArt\Assistant\Groups\GetGroupAction;
use ZxArt\Assistant\Groups\GetGroupAliasAction;
use ZxArt\Assistant\Prods\CreateProdAction;
use ZxArt\Assistant\Prods\UpdateProdAction;
use ZxArt\Assistant\Prods\DeleteProdAction;
use ZxArt\Assistant\Prods\JoinProdsAction;
use ZxArt\Assistant\Prods\SetProdAuthorAction;
use ZxArt\Assistant\Prods\RemoveProdAuthorAction;
use ZxArt\Assistant\Releases\AddReleaseFilesAction;
use ZxArt\Assistant\Releases\CreateReleaseAction;
use ZxArt\Assistant\Releases\DeleteReleaseFileAction;
use ZxArt\Assistant\Releases\DeleteReleaseAction;
use ZxArt\Assistant\Releases\RemoveReleaseAuthorAction;
use ZxArt\Assistant\Releases\SetReleaseAuthorAction;
use ZxArt\Assistant\Releases\SetReleaseFileAction;
use ZxArt\Assistant\Releases\SetReleaseItchIoFileAction;
use ZxArt\Assistant\Releases\UpdateReleaseAction;
use ZxArt\Assistant\Prods\AddProdFilesAction;
use ZxArt\Assistant\Prods\DeleteProdFileAction;
use ZxArt\Assistant\Authors\ConvertAuthorToGroupAction;
use ZxArt\Assistant\Authors\CreateAuthorAction;
use ZxArt\Assistant\Authors\DeleteAuthorAction;
use ZxArt\Assistant\Authors\JoinAuthorsAction;
use ZxArt\Assistant\Authors\UpdateAuthorAction;
use ZxArt\Assistant\Authors\ConvertAuthorAliasToAuthorAction;
use ZxArt\Assistant\Authors\CreateAuthorAliasAction;
use ZxArt\Assistant\Authors\DeleteAuthorAliasAction;
use ZxArt\Assistant\Authors\JoinAuthorAliasesAction;
use ZxArt\Assistant\Authors\UpdateAuthorAliasAction;
use ZxArt\Assistant\Groups\ConvertGroupToAuthorAction;
use ZxArt\Assistant\Groups\CreateGroupAction;
use ZxArt\Assistant\Groups\DeleteGroupAction;
use ZxArt\Assistant\Groups\JoinGroupsAction;
use ZxArt\Assistant\Groups\RemoveGroupMemberAction;
use ZxArt\Assistant\Groups\SetGroupMemberAction;
use ZxArt\Assistant\Groups\UpdateGroupAction;
use ZxArt\Assistant\Groups\ConvertGroupAliasToGroupAction;
use ZxArt\Assistant\Groups\CreateGroupAliasAction;
use ZxArt\Assistant\Groups\DeleteGroupAliasAction;
use ZxArt\Assistant\Groups\JoinGroupAliasesAction;
use ZxArt\Assistant\Groups\RemoveGroupAliasMemberAction;
use ZxArt\Assistant\Groups\SetGroupAliasMemberAction;
use ZxArt\Assistant\Groups\UpdateGroupAliasAction;
use ZxArt\Assistant\Web\FetchUrlAction;
use ZxArt\Assistant\Web\ListItchIoFilesAction;
use ZxArt\Assistant\Web\ViewImageAction;
use ZxArt\Assistant\Prods\GetProdAction;
use ZxArt\Assistant\Releases\GetReleaseAction;
use ZxArt\Assistant\Search\FindLocationsAction;
use ZxArt\Assistant\Search\FindPartiesAction;
use ZxArt\Assistant\Authors\SearchAuthorsAction;
use ZxArt\Assistant\Groups\SearchGroupsAction;
use ZxArt\Assistant\Prods\SearchProdsAction;
use ZxArt\Assistant\Releases\SearchReleasesAction;
use ZxArt\Assistant\Search\SearchAction;
use ZxArt\Assistant\Actions\ToolRegistry;
use ZxArt\Assistant\Skills\LoadSkillAction;
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
use ZxArt\Shared\Time\Clock;
use ZxArt\Shared\Time\SystemClock;
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

    Clock::class => autowire(SystemClock::class),

    // Turns JSON request bodies into typed request DTOs; see the factory for why
    // it is configured the way it is.
    SerializerInterface::class => factory(static fn(): SerializerInterface => RequestDenormalizerFactory::create()),

    // the assistant's tools; a tool is listed here to be callable
    SkillCatalogue::class => autowire()->constructor(dirname(__DIR__) . '/assistant-skills'),
    ToolRegistry::class => autowire()->constructor([
        DI\get(LoadSkillAction::class),
        DI\get(SearchAction::class),
        DI\get(FindLocationsAction::class),
        DI\get(FindPartiesAction::class),
        DI\get(SearchProdsAction::class),
        DI\get(SearchReleasesAction::class),
        DI\get(SearchAuthorsAction::class),
        DI\get(SearchGroupsAction::class),
        DI\get(FindTagsAction::class),
        DI\get(ListCategoriesAction::class),
        DI\get(ListHardwareAction::class),
        DI\get(ListOptionsAction::class),
        DI\get(GetProdAction::class),
        DI\get(GetReleaseAction::class),
        DI\get(GetAuthorAction::class),
        DI\get(GetAuthorAliasAction::class),
        DI\get(GetGroupAction::class),
        DI\get(GetGroupAliasAction::class),
        DI\get(FetchUrlAction::class),
        DI\get(ViewImageAction::class),
        DI\get(ListItchIoFilesAction::class),
        DI\get(CreateProdAction::class),
        DI\get(UpdateProdAction::class),
        DI\get(DeleteProdAction::class),
        DI\get(JoinProdsAction::class),
        DI\get(SetProdAuthorAction::class),
        DI\get(RemoveProdAuthorAction::class),
        DI\get(AddReleaseFilesAction::class),
        DI\get(CreateReleaseAction::class),
        DI\get(DeleteReleaseFileAction::class),
        DI\get(DeleteReleaseAction::class),
        DI\get(RemoveReleaseAuthorAction::class),
        DI\get(SetReleaseAuthorAction::class),
        DI\get(SetReleaseFileAction::class),
        DI\get(SetReleaseItchIoFileAction::class),
        DI\get(UpdateReleaseAction::class),
        DI\get(AddProdFilesAction::class),
        DI\get(DeleteProdFileAction::class),
        DI\get(ConvertAuthorToGroupAction::class),
        DI\get(CreateAuthorAction::class),
        DI\get(DeleteAuthorAction::class),
        DI\get(JoinAuthorsAction::class),
        DI\get(UpdateAuthorAction::class),
        DI\get(ConvertAuthorAliasToAuthorAction::class),
        DI\get(CreateAuthorAliasAction::class),
        DI\get(DeleteAuthorAliasAction::class),
        DI\get(JoinAuthorAliasesAction::class),
        DI\get(UpdateAuthorAliasAction::class),
        DI\get(ConvertGroupToAuthorAction::class),
        DI\get(CreateGroupAction::class),
        DI\get(DeleteGroupAction::class),
        DI\get(JoinGroupsAction::class),
        DI\get(RemoveGroupMemberAction::class),
        DI\get(SetGroupMemberAction::class),
        DI\get(UpdateGroupAction::class),
        DI\get(ConvertGroupAliasToGroupAction::class),
        DI\get(CreateGroupAliasAction::class),
        DI\get(DeleteGroupAliasAction::class),
        DI\get(JoinGroupAliasesAction::class),
        DI\get(RemoveGroupAliasMemberAction::class),
        DI\get(SetGroupAliasMemberAction::class),
        DI\get(UpdateGroupAliasAction::class),
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
