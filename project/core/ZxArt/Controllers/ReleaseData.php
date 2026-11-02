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
use ZxArt\Releases\Dto\ReleaseCreateFromFilesRequestDto;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Dto\ReleaseFileUploadDto;
use ZxArt\Releases\Dto\ReleaseSaveRequestDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\ReleaseFileKind;
use ZxArt\Releases\Rest\ReleaseDataResultRestDto;
use ZxArt\Releases\UseCases\CloneReleaseCommand;
use ZxArt\Releases\UseCases\CloneReleaseHandler;
use ZxArt\Releases\UseCases\CreateReleasesFromFilesCommand;
use ZxArt\Releases\UseCases\CreateReleasesFromFilesHandler;
use ZxArt\Releases\UseCases\DeleteReleaseCommand;
use ZxArt\Releases\UseCases\DeleteReleaseFileCommand;
use ZxArt\Releases\UseCases\DeleteReleaseFileHandler;
use ZxArt\Releases\UseCases\DeleteReleaseHandler;
use ZxArt\Releases\UseCases\DeleteReleaseMemberCommand;
use ZxArt\Releases\UseCases\DeleteReleaseMemberHandler;
use ZxArt\Releases\UseCases\UpdateReleaseCommand;
use ZxArt\Releases\UseCases\UpdateReleaseHandler;
use ZxArt\Shared\Dto\CreatedIdsDto;
use ZxArt\Shared\Http\MultipartDataReader;
use ZxArt\Shared\Http\UploadedFilesReader;
use ZxArt\Shared\Rest\CreatedIdsRestDto;

/**
 * Changes to one release: POST `/release-data/?id=&action=` with the action
 * one of `update`, `createFromFiles`, `delete`, `clone`, `deleteMember`,
 * `deleteFile`. Answers the id of the element the change produced (the copy
 * for `clone`), and `{ids}` for `createFromFiles`. `update` and
 * `createFromFiles` take a multipart body: the JSON `data` part
 * (`ReleaseSaveRequestDto`, `ReleaseCreateFromFilesRequestDto`), the release
 * file under `file` or the files to make releases of under `releaseFiles[]`,
 * and the files of each list under `files[<kind>][]`.
 *
 * @psalm-api
 */
final class ReleaseData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly DeleteReleaseHandler $deleteReleaseHandler,
        private readonly CloneReleaseHandler $cloneReleaseHandler,
        private readonly DeleteReleaseMemberHandler $deleteReleaseMemberHandler,
        private readonly DeleteReleaseFileHandler $deleteReleaseFileHandler,
        private readonly UpdateReleaseHandler $updateReleaseHandler,
        private readonly CreateReleasesFromFilesHandler $createReleasesFromFilesHandler,
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
                throw new ReleaseDataException('Method not allowed', 405);
            }
            $releaseId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'update' => $this->update($releaseId),
                'createFromFiles' => $this->createFromFiles(),
                'delete' => $this->deleteReleaseHandler->handle(new DeleteReleaseCommand($releaseId)),
                'clone' => $this->cloneReleaseHandler->handle(new CloneReleaseCommand($releaseId)),
                'deleteMember' => $this->deleteReleaseMemberHandler->handle(new DeleteReleaseMemberCommand($releaseId, (int)$this->getParameter('authorId'))),
                'deleteFile' => $this->deleteReleaseFileHandler->handle(new DeleteReleaseFileCommand($releaseId, (int)$this->getParameter('fileId'))),
                default => throw new ReleaseDataException('Unsupported release action', 400),
            };
            $this->renderer->assign('body', $result instanceof CreatedIdsDto
                ? $this->objectMapper->map($result, CreatedIdsRestDto::class)
                : $this->objectMapper->map($result, ReleaseDataResultRestDto::class));
        } catch (InvalidArgumentException $exception) {
            $this->logThrowable('ReleaseData::execute', $exception);
            $this->assignError($exception->getMessage(), 400);
        } catch (ReleaseDataException $exception) {
            $this->logThrowable('ReleaseData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('ReleaseData::execute', $throwable);
            $this->assignError('Internal server error', 500);
        }

        $this->renderer->display();
    }

    /**
     * @throws ReleaseDataException
     * @throws InvalidArgumentException
     */
    private function update(int $releaseId): ReleaseDataResultDto
    {
        $request = $this->multipartDataReader->read($_POST, ReleaseSaveRequestDto::class);

        return $this->updateReleaseHandler->handle(new UpdateReleaseCommand(
            $releaseId,
            $request->fields,
            $request->authors,
            $request->importOrigins,
            $this->uploadedFiles(),
            $this->uploadedFilesReader->one($_FILES, 'file'),
            $request->removeFile,
        ));
    }

    /**
     * @throws ReleaseDataException
     * @throws InvalidArgumentException
     */
    private function createFromFiles(): CreatedIdsDto
    {
        $request = $this->multipartDataReader->read($_POST, ReleaseCreateFromFilesRequestDto::class);

        return $this->createReleasesFromFilesHandler->handle(new CreateReleasesFromFilesCommand(
            $request->fields,
            $this->uploadedFilesReader->many($_FILES, 'releaseFiles'),
            $request->authors,
            $this->uploadedFiles(),
        ));
    }

    /**
     * @return list<ReleaseFileUploadDto>
     * @throws InvalidArgumentException for a kind a release has no list of
     */
    private function uploadedFiles(): array
    {
        $uploads = [];
        foreach ($this->uploadedFilesReader->byKind($_FILES, 'files') as $kindName => $files) {
            $kind = ReleaseFileKind::tryFrom($kindName) ?? throw new InvalidArgumentException("A release has no $kindName files");
            foreach ($files as $file) {
                $uploads[] = new ReleaseFileUploadDto($kind, $file);
            }
        }

        return $uploads;
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
