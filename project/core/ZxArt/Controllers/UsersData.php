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
use ZxArt\Shared\SortDirection;
use ZxArt\Users\Dto\UserFieldsDto;
use ZxArt\Users\Dto\UserListItemDto;
use ZxArt\Users\Dto\UsersListQueryDto;
use ZxArt\Users\Exception\UserDataException;
use ZxArt\Users\Rest\UserDetailsRestDto;
use ZxArt\Users\Rest\UserListItemRestDto;
use ZxArt\Users\Services\UserManagementPrivileges;
use ZxArt\Users\UseCases\BanUserCommand;
use ZxArt\Users\UseCases\BanUserHandler;
use ZxArt\Users\UseCases\UpdateUserCommand;
use ZxArt\Users\UseCases\UpdateUserHandler;
use ZxArt\Users\UserDetailsService;
use ZxArt\Users\UsersListService;
use ZxArt\Users\UsersListSort;
use ZxArt\Users\UserStatusFilter;

/**
 * The users of the management screen (`/users-data/`).
 *
 * GET without an id answers one page of the list, searched by `search`
 * (part of the name or the email), filtered by `groupId` and `status`, sorted
 * by `sort` and `direction`, paged by `page`. GET `?id=` answers one user's
 * details. POST `?id=&action=update` saves the form, POST `?id=&action=ban`
 * bans; both answer the user's details afresh.
 *
 * Everything asks for `root.manageUsers` on the public root, banning asks for
 * `user.ban` instead. Named *UsersData* so `/users` stays free.
 *
 * @psalm-api
 */
final class UsersData extends LoggedControllerApplication
{
    private const int PAGE_SIZE = 50;

    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly UsersListService $usersListService,
        private readonly UserDetailsService $userDetailsService,
        private readonly UpdateUserHandler $updateUserHandler,
        private readonly BanUserHandler $banUserHandler,
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
            $userId = (int)$this->getParameter('id');
            match ($action) {
                '' => $this->read($userId),
                'update' => $this->update($userId),
                'ban' => $this->ban($userId),
                default => throw new UserDataException('Unsupported user action', 400),
            };
        } catch (UserDataException $exception) {
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('UsersData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    /**
     * @throws UserDataException
     */
    private function read(int $userId): void
    {
        $this->privileges->assertMayManageUsers();
        if ($userId > 0) {
            $this->assignDetails($userId);
            return;
        }

        $query = $this->readListQuery();
        $list = $this->usersListService->getList($query);
        $this->renderer->assign('body', [
            'total' => $list->total,
            'pageSize' => $query->pageSize,
            'users' => array_map(
                fn(UserListItemDto $user): UserListItemRestDto => $this->objectMapper->map($user, UserListItemRestDto::class),
                $list->users,
            ),
        ]);
    }

    /**
     * @throws UserDataException
     */
    private function update(int $userId): void
    {
        $this->updateUserHandler->handle(new UpdateUserCommand($userId, $this->readFields()));
        $this->assignDetails($userId);
    }

    /**
     * @throws UserDataException
     */
    private function ban(int $userId): void
    {
        $this->banUserHandler->handle(new BanUserCommand($userId));
        $this->assignDetails($userId);
    }

    /**
     * @throws UserDataException
     */
    private function assignDetails(int $userId): void
    {
        $details = $this->userDetailsService->getDetails($userId);
        $this->renderer->assign('body', $this->objectMapper->map($details, UserDetailsRestDto::class));
    }

    private function readListQuery(): UsersListQueryDto
    {
        return new UsersListQueryDto(
            search: (string)$this->getParameter('search'),
            groupId: max(0, (int)$this->getParameter('groupId')),
            status: UserStatusFilter::tryFrom((string)$this->getParameter('status')) ?? UserStatusFilter::All,
            sort: UsersListSort::tryFrom((string)$this->getParameter('sort')) ?? UsersListSort::DateCreated,
            direction: SortDirection::fromString((string)$this->getParameter('direction'), SortDirection::DESC),
            page: max(1, (int)$this->getParameter('page')),
            pageSize: self::PAGE_SIZE,
        );
    }

    /**
     * A body that does not fit the fields DTO is a bad request; the
     * serializer's message names the offending field.
     *
     * @throws UserDataException
     */
    private function readFields(): UserFieldsDto
    {
        $body = file_get_contents('php://input');
        if (!is_string($body)) {
            throw new UserDataException('Request body must be a JSON object', 400);
        }

        try {
            return $this->serializer->deserialize($body, UserFieldsDto::class, 'json');
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
