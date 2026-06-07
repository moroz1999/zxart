<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Groups\Rest\GroupCoreRestDto;
use ZxArt\Groups\Services\GroupDetailsService;
use ZxArt\Prods\Exception\ProdDetailsException;

class GroupDetails extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly GroupDetailsService $groupDetailsService,
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
            $groupId = $this->getGroupId();
            $dto = $this->groupDetailsService->getDetails($groupId);
            $this->renderer->assign('body', $this->objectMapper->map($dto, GroupCoreRestDto::class));
        } catch (ProdDetailsException $e) {
            $this->logThrowable('GroupDetails::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('GroupDetails::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getGroupId(): int
    {
        $groupId = (int)($this->getParameter('id') ?? 0);
        if ($groupId <= 0) {
            throw new ProdDetailsException('Missing required parameter: id', 400);
        }
        return $groupId;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
