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
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Dto\AuthorSaveRequestDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Rest\AuthorDataResultRestDto;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Shared\Http\MultipartDataReader;
use ZxArt\Shared\Http\UploadedFilesReader;

/**
 * Changes to one author: POST `/author-data/?id=&action=` with the action one
 * of `create`, `update`, `delete`, `convertToGroup`. Answers the id of the
 * element the change produced. `create` and `update` take a multipart body:
 * the JSON `data` part (`AuthorSaveRequestDto`) and the image under `image`.
 *
 * @psalm-api
 */
final class AuthorData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly AuthorDataService $authorDataService,
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
                throw new AuthorDataException('Method not allowed', 405);
            }
            $authorId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'create' => $this->create(),
                'update' => $this->update($authorId),
                'delete' => $this->authorDataService->delete($authorId),
                'convertToGroup' => $this->authorDataService->convertToGroup($authorId),
                default => throw new AuthorDataException('Unsupported author action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, AuthorDataResultRestDto::class));
        } catch (InvalidArgumentException $exception) {
            $this->logThrowable('AuthorData::execute', $exception);
            $this->assignError($exception->getMessage(), 400);
        } catch (AuthorDataException $exception) {
            $this->logThrowable('AuthorData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('AuthorData::execute', $throwable);
            $this->assignError('Internal server error', 500);
        }

        $this->renderer->display();
    }

    /**
     * @throws AuthorDataException
     * @throws InvalidArgumentException
     */
    private function create(): AuthorDataResultDto
    {
        $request = $this->multipartDataReader->read($_POST, AuthorSaveRequestDto::class);

        return $this->authorDataService->create($request->fields, $request->realNames, $request->groups, $request->prods, $request->importOrigins, $this->uploadedFilesReader->one($_FILES, 'image'));
    }

    /**
     * @throws AuthorDataException
     * @throws InvalidArgumentException
     */
    private function update(int $authorId): AuthorDataResultDto
    {
        $request = $this->multipartDataReader->read($_POST, AuthorSaveRequestDto::class);

        return $this->authorDataService->update($authorId, $request->fields, $request->realNames, $request->groups, $request->prods, $request->importOrigins, $this->uploadedFilesReader->one($_FILES, 'image'), $request->removeImage);
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
