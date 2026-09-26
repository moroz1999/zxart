<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\EntityConversion\ConversionTarget;
use ZxArt\EntityConversion\Exception\EntityConversionException;
use ZxArt\EntityConversion\Rest\ConvertedEntityRestDto;
use ZxArt\EntityConversion\Services\EntityConversionService;

/**
 * POST converts the element named by `id` into the entity named by `target`
 * and answers the created entity's id.
 */
class EntityConversionData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly EntityConversionService $entityConversionService,
        private readonly ObjectMapper $objectMapper,
    ) {
        parent::__construct($controller, $logger);
    }

    #[Override]
    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    #[Override]
    public function execute($controller): void
    {
        try {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                throw new EntityConversionException('Method not allowed', 405);
            }
            $elementId = (int)$this->getParameter('id');
            $target = $this->readTarget();
            $converted = $this->entityConversionService->convert($elementId, $target);
            $this->renderer->assign('body', $this->objectMapper->map($converted, ConvertedEntityRestDto::class));
        } catch (EntityConversionException $exception) {
            $this->logThrowable('EntityConversionData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('EntityConversionData::execute', $throwable);
            $this->assignError('Internal server error', 500);
        }

        $this->renderer->display();
    }

    private function readTarget(): ConversionTarget
    {
        $target = ConversionTarget::tryFrom((string)$this->getParameter('target'));
        if ($target === null) {
            throw new EntityConversionException('Unknown conversion target', 400);
        }

        return $target;
    }

    private function assignError(string $message, int $statusCode): void
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
