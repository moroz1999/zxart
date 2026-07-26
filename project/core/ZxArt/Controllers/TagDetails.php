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
use ZxArt\Tags\Rest\TagPageRestDto;
use ZxArt\Tags\TagPageService;
use ZxArt\Tags\TagSection;

class TagDetails extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly TagPageService $tagPageService,
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
            $tagId = (int)($this->getParameter('id') ?? 0);
            $section = TagSection::tryFrom((string)($this->getParameter('section') ?: ''));
            if ($tagId <= 0 || $section === null) {
                $this->assignError('Valid id and section are required', 400);
            } else {
                $tag = $this->tagPageService->get($tagId, $section);
                if ($tag === null) {
                    $this->assignError('Tag not found', 404);
                } else {
                    $this->renderer->assign('body', $this->objectMapper->map($tag, TagPageRestDto::class));
                }
            }
        } catch (Throwable $e) {
            $this->logThrowable('TagDetails::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
