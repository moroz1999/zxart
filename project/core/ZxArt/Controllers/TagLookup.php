<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use Override;
use structureManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Tags\Dto\TagLookupItemDto;
use ZxArt\Tags\Rest\TagLookupItemRestDto;
use ZxArt\Tags\Services\TagLookupService;

/**
 * What a tag picker offers: GET `/tag-lookup/?query=`. Answers
 * `{items: [{id, title, value, synonym, description, url}]}`.
 *
 * @psalm-api
 */
final class TagLookup extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly TagLookupService $tagLookupService,
        private readonly ObjectMapper $objectMapper,
    ) {
        parent::__construct($controller, $logger);
    }

    #[Override]
    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
        $this->structureManager->setRequestedPath([$this->languagesManager->getCurrentLanguageCode()]);
    }

    #[Override]
    public function execute($controller): void
    {
        try {
            $items = $this->tagLookupService->lookup((string)($this->getParameter('query') ?? ''));
            $this->renderer->assign('body', [
                'items' => array_map(
                    fn(TagLookupItemDto $item): TagLookupItemRestDto => $this->objectMapper->map($item, TagLookupItemRestDto::class),
                    $items,
                ),
            ]);
        } catch (Throwable $throwable) {
            $this->logThrowable('TagLookup::execute', $throwable);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }

        $this->renderer->display();
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
