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
use ZxArt\Prods\Dto\ProdBatchUploadRequestDto;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Dto\ProdFileUploadDto;
use ZxArt\Prods\Dto\ProdSaveRequestDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\ProdFileKind;
use ZxArt\Prods\Rest\ProdDataResultRestDto;
use ZxArt\Prods\UseCases\BatchUploadProdsCommand;
use ZxArt\Prods\UseCases\BatchUploadProdsHandler;
use ZxArt\Prods\UseCases\DeleteProdCommand;
use ZxArt\Prods\UseCases\DeleteProdFileCommand;
use ZxArt\Prods\UseCases\DeleteProdFileHandler;
use ZxArt\Prods\UseCases\DeleteProdHandler;
use ZxArt\Prods\UseCases\DeleteProdMemberCommand;
use ZxArt\Prods\UseCases\DeleteProdMemberHandler;
use ZxArt\Prods\UseCases\UpdateProdCommand;
use ZxArt\Prods\UseCases\UpdateProdHandler;
use ZxArt\Shared\Dto\CreatedIdsDto;
use ZxArt\Shared\Http\MultipartDataReader;
use ZxArt\Shared\Http\UploadedFilesReader;
use ZxArt\Shared\Rest\CreatedIdsRestDto;

/**
 * Changes to one production: POST `/prod-data/?id=&action=` with the action
 * one of `update`, `batchUpload`, `delete`, `deleteMember`, `deleteFile`.
 * Answers the id of the element the change produced. Answers `{ids}` for
 * `batchUpload`. `update` and `batchUpload` take a multipart body: the JSON
 * `data` part (`ProdSaveRequestDto`, `ProdBatchUploadRequestDto`), the
 * software files under `releaseFiles[]` and the files of each list under
 * `files[<kind>][]`.
 *
 * @psalm-api
 */
final class ProdData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly DeleteProdHandler $deleteProdHandler,
        private readonly DeleteProdMemberHandler $deleteProdMemberHandler,
        private readonly DeleteProdFileHandler $deleteProdFileHandler,
        private readonly UpdateProdHandler $updateProdHandler,
        private readonly BatchUploadProdsHandler $batchUploadProdsHandler,
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
                throw new ProdDataException('Method not allowed', 405);
            }
            $prodId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'update' => $this->update($prodId),
                'batchUpload' => $this->batchUpload(),
                'delete' => $this->deleteProdHandler->handle(new DeleteProdCommand($prodId)),
                'deleteMember' => $this->deleteProdMemberHandler->handle(new DeleteProdMemberCommand($prodId, (int)$this->getParameter('authorId'))),
                'deleteFile' => $this->deleteProdFileHandler->handle(new DeleteProdFileCommand($prodId, (int)$this->getParameter('fileId'))),
                default => throw new ProdDataException('Unsupported production action', 400),
            };
            $this->renderer->assign('body', $result instanceof CreatedIdsDto
                ? $this->objectMapper->map($result, CreatedIdsRestDto::class)
                : $this->objectMapper->map($result, ProdDataResultRestDto::class));
        } catch (InvalidArgumentException $exception) {
            $this->logThrowable('ProdData::execute', $exception);
            $this->assignError($exception->getMessage(), 400);
        } catch (ProdDataException $exception) {
            $this->logThrowable('ProdData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('ProdData::execute', $throwable);
            $this->assignError('Internal server error', 500);
        }

        $this->renderer->display();
    }

    /**
     * @throws ProdDataException
     * @throws InvalidArgumentException
     */
    private function update(int $prodId): ProdDataResultDto
    {
        $request = $this->multipartDataReader->read($_POST, ProdSaveRequestDto::class);

        return $this->updateProdHandler->handle(new UpdateProdCommand($prodId, $request->fields, $request->authors, $request->importOrigins, $this->uploadedFiles()));
    }

    /**
     * @throws ProdDataException
     * @throws InvalidArgumentException
     */
    private function batchUpload(): CreatedIdsDto
    {
        $request = $this->multipartDataReader->read($_POST, ProdBatchUploadRequestDto::class);

        return $this->batchUploadProdsHandler->handle(new BatchUploadProdsCommand(
            $request->fields,
            $this->uploadedFilesReader->many($_FILES, 'releaseFiles'),
            $request->authors,
            $this->uploadedFiles(),
        ));
    }

    /**
     * @return list<ProdFileUploadDto>
     * @throws InvalidArgumentException for a kind a production has no list of
     */
    private function uploadedFiles(): array
    {
        $uploads = [];
        foreach ($this->uploadedFilesReader->byKind($_FILES, 'files') as $kindName => $files) {
            $kind = ProdFileKind::tryFrom($kindName) ?? throw new InvalidArgumentException("A production has no $kindName files");
            foreach ($files as $file) {
                $uploads[] = new ProdFileUploadDto($kind, $file);
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
