<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use InvalidArgumentException;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Dto\AuthorAliasSaveRequestDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Rest\AuthorAliasDataResultRestDto;
use ZxArt\Authors\UseCases\ConvertAuthorAliasToAuthorCommand;
use ZxArt\Authors\UseCases\ConvertAuthorAliasToAuthorHandler;
use ZxArt\Authors\UseCases\CreateAuthorAliasCommand;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Authors\UseCases\DeleteAuthorAliasCommand;
use ZxArt\Authors\UseCases\DeleteAuthorAliasHandler;
use ZxArt\Authors\UseCases\UpdateAuthorAliasCommand;
use ZxArt\Authors\UseCases\UpdateAuthorAliasHandler;
use ZxArt\Shared\Http\MultipartDataReader;
use ZxArt\Shared\Http\UploadedFilesReader;

/**
 * Changes to one author alias: POST `/author-alias-data/?id=&action=` with the
 * action one of `create`, `update`, `delete`, `convertToAuthor`. Answers the
 * id of the element the change produced. `create` and `update` take a
 * multipart body with the JSON `data` part (`AuthorAliasSaveRequestDto`).
 *
 * @psalm-api
 */
final class AuthorAliasData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly DeleteAuthorAliasHandler $deleteAuthorAliasHandler,
        private readonly ConvertAuthorAliasToAuthorHandler $convertAuthorAliasToAuthorHandler,
        private readonly CreateAuthorAliasHandler $createAuthorAliasHandler,
        private readonly UpdateAuthorAliasHandler $updateAuthorAliasHandler,
        private readonly ObjectMapper $objectMapper,
        private readonly MultipartDataReader $multipartDataReader,
        private readonly UploadedFilesReader $uploadedFilesReader,
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
                throw new AuthorAliasDataException('Method not allowed', 405);
            }
            $aliasId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'create' => $this->create(),
                'update' => $this->update($aliasId),
                'delete' => $this->deleteAuthorAliasHandler->handle(new DeleteAuthorAliasCommand($aliasId)),
                'convertToAuthor' => $this->convertAuthorAliasToAuthorHandler->handle(new ConvertAuthorAliasToAuthorCommand($aliasId)),
                default => throw new AuthorAliasDataException('Unsupported author alias action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, AuthorAliasDataResultRestDto::class));
        } catch (InvalidArgumentException $exception) {
            $this->logThrowable('AuthorAliasData::execute', $exception);
            $this->assignError($exception->getMessage(), 400);
        } catch (AuthorAliasDataException $exception) {
            $this->logThrowable('AuthorAliasData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('AuthorAliasData::execute', $throwable);
            $this->assignError('Internal server error', 500);
        }

        $this->renderer->display();
    }

    /**
     * @throws AuthorAliasDataException
     * @throws InvalidArgumentException
     */
    private function create(): AuthorAliasDataResultDto
    {
        $request = $this->multipartDataReader->read($_POST, AuthorAliasSaveRequestDto::class);

        return $this->createAuthorAliasHandler->handle(new CreateAuthorAliasCommand($request->fields, $request->groups, $request->prods, $request->importOrigins));
    }

    /**
     * @throws AuthorAliasDataException
     * @throws InvalidArgumentException
     */
    private function update(int $aliasId): AuthorAliasDataResultDto
    {
        $request = $this->multipartDataReader->read($_POST, AuthorAliasSaveRequestDto::class);

        return $this->updateAuthorAliasHandler->handle(new UpdateAuthorAliasCommand($aliasId, $request->fields, $request->groups, $request->prods, $request->importOrigins));
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
