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
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Dto\GroupSaveRequestDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Rest\GroupDataResultRestDto;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Shared\Http\MultipartDataReader;
use ZxArt\Shared\Http\UploadedFilesReader;

/**
 * Changes to one group: POST `/group-data/?id=&action=` with the action one of
 * `create`, `update`, `delete`, `convertToAuthor`, `deleteMember`. Answers the
 * id of the element the change produced. `create` and `update` take a
 * multipart body: the JSON `data` part (`GroupSaveRequestDto`) and the image
 * under `image`.
 *
 * @psalm-api
 */
final class GroupData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly GroupDataService $groupDataService,
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
                throw new GroupDataException('Method not allowed', 405);
            }
            $groupId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'create' => $this->create(),
                'update' => $this->update($groupId),
                'delete' => $this->groupDataService->delete($groupId),
                'deleteMember' => $this->groupDataService->deleteMember($groupId, (int)$this->getParameter('authorId')),
                'convertToAuthor' => $this->groupDataService->convertToAuthor($groupId),
                default => throw new GroupDataException('Unsupported group action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, GroupDataResultRestDto::class));
        } catch (InvalidArgumentException $exception) {
            $this->logThrowable('GroupData::execute', $exception);
            $this->assignError($exception->getMessage(), 400);
        } catch (GroupDataException $exception) {
            $this->logThrowable('GroupData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('GroupData::execute', $throwable);
            $this->assignError('Internal server error', 500);
        }

        $this->renderer->display();
    }

    /**
     * @throws GroupDataException
     * @throws InvalidArgumentException
     */
    private function create(): GroupDataResultDto
    {
        $request = $this->multipartDataReader->read($_POST, GroupSaveRequestDto::class);

        return $this->groupDataService->create($request->fields, $request->members, $request->importOrigins, $this->uploadedFilesReader->one($_FILES, 'image'));
    }

    /**
     * @throws GroupDataException
     * @throws InvalidArgumentException
     */
    private function update(int $groupId): GroupDataResultDto
    {
        $request = $this->multipartDataReader->read($_POST, GroupSaveRequestDto::class);

        return $this->groupDataService->update($groupId, $request->fields, $request->members, $request->importOrigins, $this->uploadedFilesReader->one($_FILES, 'image'), $request->removeImage);
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
