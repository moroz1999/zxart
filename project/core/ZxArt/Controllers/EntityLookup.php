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
use ZxArt\Search\Dto\EntityLookupItemDto;
use ZxArt\Search\Rest\EntityLookupItemRestDto;
use ZxArt\Search\Services\EntityLookupService;
use ZxArt\Search\Services\PhraseSearchType;

/**
 * What a relation picker offers: GET `/entity-lookup/?types=&query=`, the
 * types comma-separated structure types (unknown ones are skipped). Answers
 * `{items: [{id, title}]}`.
 *
 * @psalm-api
 */
final class EntityLookup extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly EntityLookupService $entityLookupService,
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
            $items = $this->entityLookupService->lookup(
                (string)($this->getParameter('query') ?? ''),
                $this->parseTypes((string)($this->getParameter('types') ?? '')),
                (int)$this->languagesManager->getCurrentLanguageId(),
            );
            $this->renderer->assign('body', [
                'items' => array_map(
                    fn(EntityLookupItemDto $item): EntityLookupItemRestDto => $this->objectMapper->map($item, EntityLookupItemRestDto::class),
                    $items,
                ),
            ]);
        } catch (Throwable $throwable) {
            $this->logThrowable('EntityLookup::execute', $throwable);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }

        $this->renderer->display();
    }

    /**
     * @return PhraseSearchType[]
     */
    private function parseTypes(string $types): array
    {
        $parsed = [];
        foreach (explode(',', $types) as $type) {
            $parsedType = PhraseSearchType::tryFrom(trim($type));
            if ($parsedType !== null) {
                $parsed[] = $parsedType;
            }
        }

        return $parsed;
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
