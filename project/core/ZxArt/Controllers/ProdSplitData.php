<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\ElementPrivileges\ElementPrivilegesService;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\Rest\ProdSplitDataRestDto;
use ZxArt\Prods\Services\ProdSplitService;

/**
 * Everything of a production the split form offers for splitting off
 * (`/prod-split-data/?id=`), behind the same privilege as the form itself.
 */
class ProdSplitData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    private const string PRIVILEGE = 'showSplitForm';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly ProdSplitService $prodSplitService,
        private readonly ElementPrivilegesService $elementPrivilegesService,
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
            $elementId = (int)($this->getParameter('id') ?? 0);
            if ($elementId <= 0) {
                $this->assignError('Missing required parameter: id', 400);
            } elseif (!$this->hasPrivilege($elementId)) {
                $this->assignError('Forbidden', 403);
            } else {
                $dto = $this->prodSplitService->getSplitData($elementId);
                $this->renderer->assign('body', $this->objectMapper->map($dto, ProdSplitDataRestDto::class));
            }
        } catch (ProdDetailsException $e) {
            $this->logThrowable('ProdSplitData::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('ProdSplitData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function hasPrivilege(int $elementId): bool
    {
        return $this->elementPrivilegesService->getPrivileges($elementId, [self::PRIVILEGE])
            ->privileges[self::PRIVILEGE] ?? false;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
