<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Authors\Dto\AuthorCollaboratorGroupDto;
use ZxArt\Authors\Dto\AuthorCollaboratorPersonDto;
use ZxArt\Authors\Rest\AuthorCollaboratorGroupRestDto;
use ZxArt\Authors\Rest\AuthorCollaboratorPersonRestDto;
use ZxArt\Authors\Services\AuthorCollaboratorsService;
use ZxArt\Prods\Exception\ProdDetailsException;

final class AuthorCollaborators extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly AuthorCollaboratorsService $collaboratorsService,
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
            $authorOrAliasId = $this->getAuthorOrAliasId();
            $result = $this->collaboratorsService->getCollaborators($authorOrAliasId);
            $this->renderer->assign('body', [
                'people' => array_map(
                    fn(AuthorCollaboratorPersonDto $dto) => $this->objectMapper->map($dto, AuthorCollaboratorPersonRestDto::class),
                    $result['people'],
                ),
                'groups' => array_map(
                    fn(AuthorCollaboratorGroupDto $dto) => $this->objectMapper->map($dto, AuthorCollaboratorGroupRestDto::class),
                    $result['groups'],
                ),
            ]);
        } catch (ProdDetailsException $e) {
            $this->logThrowable('AuthorCollaborators::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('AuthorCollaborators::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getAuthorOrAliasId(): int
    {
        $authorOrAliasId = (int)($this->getParameter('id') ?? 0);
        if ($authorOrAliasId <= 0) {
            throw new ProdDetailsException('Missing required parameter: id', 400);
        }
        return $authorOrAliasId;
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
