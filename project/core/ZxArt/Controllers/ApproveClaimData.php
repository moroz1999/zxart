<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Authors\Exception\ClaimApprovalException;
use ZxArt\Authors\Rest\ClaimApprovalRestDto;
use ZxArt\Authors\Services\ClaimApprovalService;

/**
 * Backs the `/approve-claim` page: GET describes the claim and whether the
 * visitor may approve it, POST approves it. Named *ApproveClaimData* so
 * `/approve-claim` stays free for the SPA page the mailed link points at.
 */
class ApproveClaimData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly ClaimApprovalService $claimApprovalService,
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
            $authorId = (int)$this->getParameter('authorId');
            $userId = (int)$this->getParameter('userId');

            $claim = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
                ? $this->claimApprovalService->approve($authorId, $userId)
                : $this->claimApprovalService->getClaim($authorId, $userId);

            $this->renderer->assign('body', $this->objectMapper->map($claim, ClaimApprovalRestDto::class));
        } catch (ClaimApprovalException $exception) {
            $this->logThrowable('ApproveClaimData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('ApproveClaimData::execute', $throwable);
            $this->assignError('Internal server error', 500);
        }

        $this->renderer->display();
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
