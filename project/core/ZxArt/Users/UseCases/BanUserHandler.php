<?php

declare(strict_types=1);

namespace ZxArt\Users\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use ZxArt\Users\Dto\UserDataResultDto;
use ZxArt\Users\Exception\UserDataException;
use ZxArt\Users\Repositories\UserRepository;
use ZxArt\Users\Services\UserManagementPrivileges;

/**
 * Bans a user the way the admin panel always has: their comments and votes
 * are deleted and they can no longer sign in.
 *
 * @psalm-api
 */
final readonly class BanUserHandler
{
    public function __construct(
        private TransactionRunner $transactionRunner,
        private UserRepository $userRepository,
        private UserManagementPrivileges $privileges,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws UserDataException
     */
    public function handle(BanUserCommand $command): UserDataResultDto
    {
        $user = $this->userRepository->get($command->userId);
        $this->privileges->assertMayBan();

        $this->transactionRunner->run(function () use ($user): void {
            $user->ban();
            $this->actionsLogService->log($user, StructureType::User, UserManagementPrivileges::BAN);
        });

        return new UserDataResultDto(id: $command->userId);
    }
}
