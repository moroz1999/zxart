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
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Dto\GroupAliasSaveRequestDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Rest\GroupAliasDataResultRestDto;
use ZxArt\Groups\Services\GroupAliasDataService;
use ZxArt\Shared\Http\MultipartDataReader;
use ZxArt\Shared\Http\UploadedFilesReader;

/**
 * Changes to one group alias: POST `/group-alias-data/?id=&action=` with the
 * action one of `create`, `update`, `delete`, `convertToGroup`. Answers the id
 * of the element the change produced. `create` and `update` take a multipart
 * body with the JSON `data` part (`GroupAliasSaveRequestDto`).
 *
 * @psalm-api
 */
final class GroupAliasData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly GroupAliasDataService $groupAliasDataService,
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
                throw new GroupAliasDataException('Method not allowed', 405);
            }
            $aliasId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'create' => $this->create(),
                'update' => $this->update($aliasId),
                'delete' => $this->groupAliasDataService->delete($aliasId),
                'convertToGroup' => $this->groupAliasDataService->convertToGroup($aliasId),
                default => throw new GroupAliasDataException('Unsupported group alias action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, GroupAliasDataResultRestDto::class));
        } catch (InvalidArgumentException $exception) {
            $this->logThrowable('GroupAliasData::execute', $exception);
            $this->assignError($exception->getMessage(), 400);
        } catch (GroupAliasDataException $exception) {
            $this->logThrowable('GroupAliasData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('GroupAliasData::execute', $throwable);
            $this->assignError('Internal server error', 500);
        }

        $this->renderer->display();
    }

    /**
     * @throws GroupAliasDataException
     * @throws InvalidArgumentException
     */
    private function create(): GroupAliasDataResultDto
    {
        $request = $this->multipartDataReader->read($_POST, GroupAliasSaveRequestDto::class);

        return $this->groupAliasDataService->create($request->fields, $request->members, $request->importOrigins);
    }

    /**
     * @throws GroupAliasDataException
     * @throws InvalidArgumentException
     */
    private function update(int $aliasId): GroupAliasDataResultDto
    {
        $request = $this->multipartDataReader->read($_POST, GroupAliasSaveRequestDto::class);

        return $this->groupAliasDataService->update($aliasId, $request->fields, $request->members, $request->importOrigins);
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
