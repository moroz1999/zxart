<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Groups\Dto\GroupCollaboratorGroupDto;
use ZxArt\Groups\Dto\GroupCollaboratorPersonDto;
use ZxArt\Groups\Rest\GroupCollaboratorGroupRestDto;
use ZxArt\Groups\Rest\GroupCollaboratorPersonRestDto;
use ZxArt\Groups\Services\GroupCollaboratorsService;
use ZxArt\Prods\Exception\ProdDetailsException;

final class GroupCollaborators extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly GroupCollaboratorsService $collaboratorsService,
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
            $result = $this->collaboratorsService->getCollaborators($groupId);
            $this->renderer->assign('body', [
                'people' => array_map(
                    fn(GroupCollaboratorPersonDto $dto) => $this->objectMapper->map($dto, GroupCollaboratorPersonRestDto::class),
                    $result['people'],
                ),
                'publishedGroups' => array_map(
                    fn(GroupCollaboratorGroupDto $dto) => $this->objectMapper->map($dto, GroupCollaboratorGroupRestDto::class),
                    $result['publishedGroups'],
                ),
            ]);
        } catch (ProdDetailsException $e) {
            $this->logThrowable('GroupCollaborators::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('GroupCollaborators::execute', $e);
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
