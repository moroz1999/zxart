<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use ZxArt\Users\Dto\UserGroupFieldsDto;
use ZxArt\Users\Dto\UserGroupItemDto;
use ZxArt\Users\Exception\UserDataException;
use ZxArt\Users\Rest\UserGroupItemRestDto;
use ZxArt\Users\Services\UserManagementPrivileges;
use ZxArt\Users\UseCases\CreateUserGroupCommand;
use ZxArt\Users\UseCases\CreateUserGroupHandler;
use ZxArt\Users\UseCases\DeleteUserGroupCommand;
use ZxArt\Users\UseCases\DeleteUserGroupHandler;
use ZxArt\Users\UseCases\UpdateUserGroupCommand;
use ZxArt\Users\UseCases\UpdateUserGroupHandler;
use ZxArt\Users\UserGroupsService;

/**
 * The user groups of the management screen (`/user-groups-data/`).
 *
 * GET answers every group with its member count — a few dozen rows. POST
 * `?action=create`, `?id=&action=update` and `?id=&action=delete` manage
 * them and answer with the refreshed list.
 *
 * Reading asks for `root.manageUsers` or `userGroup.receive`; saving asks for
 * `userGroup.receive`, deleting for `userGroup.delete`, all on the public root.
 *
 * @psalm-api
 */
final class UserGroupsData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly UserGroupsService $userGroupsService,
        private readonly CreateUserGroupHandler $createHandler,
        private readonly UpdateUserGroupHandler $updateHandler,
        private readonly DeleteUserGroupHandler $deleteHandler,
        private readonly UserManagementPrivileges $privileges,
        private readonly ObjectMapper $objectMapper,
        private readonly SerializerInterface $serializer,
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
            $action = (string)$this->getParameter('action');
            $groupId = (int)$this->getParameter('id');
            match ($action) {
                '' => $this->privileges->assertMayReadGroups(),
                'create' => $this->createHandler->handle(new CreateUserGroupCommand($this->readFields())),
                'update' => $this->updateHandler->handle(new UpdateUserGroupCommand($groupId, $this->readFields())),
                'delete' => $this->deleteHandler->handle(new DeleteUserGroupCommand($groupId)),
                default => throw new UserDataException('Unsupported user group action', 400),
            };

            $this->renderer->assign('body', array_map(
                fn(UserGroupItemDto $group): UserGroupItemRestDto => $this->objectMapper->map($group, UserGroupItemRestDto::class),
                $this->userGroupsService->getGroups(),
            ));
        } catch (UserDataException $exception) {
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('UserGroupsData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    /**
     * @throws UserDataException
     */
    private function readFields(): UserGroupFieldsDto
    {
        $body = file_get_contents('php://input');
        if (!is_string($body)) {
            throw new UserDataException('Request body must be a JSON object', 400);
        }

        try {
            return $this->serializer->deserialize($body, UserGroupFieldsDto::class, 'json');
        } catch (SerializerException $exception) {
            throw new UserDataException($exception->getMessage(), 400);
        }
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
