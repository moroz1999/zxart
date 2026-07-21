<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use structureManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\TagsList\Dto\TagListItemDto;
use ZxArt\TagsList\Rest\TagListItemRestDto;
use ZxArt\TagsList\TagsListService;

class TagsListData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly TagsListService $tagsListService,
        private readonly ObjectMapper $objectMapper,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        $this->structureManager->setRequestedPath([$this->languagesManager->getCurrentLanguageCode()]);
    }

    public function execute($controller): void
    {
        try {
            $section = (string)($this->getParameter('section') ?: '');
            $minimumAmountParameter = $this->getParameter('minimumAmount');
            $minimumAmount = $minimumAmountParameter === false
                ? TagsListService::DEFAULT_MINIMUM_AMOUNT
                : max(TagsListService::MINIMUM_ALLOWED_AMOUNT, (int)$minimumAmountParameter);
            $tags = $this->tagsListService->getSectionTags($section, $minimumAmount);
            $this->assignSuccess([
                'items' => array_map(
                    fn(TagListItemDto $dto) => $this->objectMapper->map($dto, TagListItemRestDto::class),
                    $tags
                ),
            ]);
        } catch (Throwable $e) {
            $this->logThrowable('TagsListData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function assignSuccess(mixed $data): void
    {
        $this->renderer->assign('body', $data);
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    public function getUrlName(): string
    {
        return '';
    }
}
