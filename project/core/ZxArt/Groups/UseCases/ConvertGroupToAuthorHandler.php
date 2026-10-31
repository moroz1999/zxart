<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use groupElement;

/**
 * Turns the group into a new author; the group itself is deleted.
 *
 * @psalm-api
 */
final readonly class ConvertGroupToAuthorHandler
{
    private const string PRIVILEGE = 'convertToAuthor';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupRepository $groupRepository,
        private GroupPrivileges $groupPrivileges,
        private AuthorsService $authorsService,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupDataException
     */
    public function handle(ConvertGroupToAuthorCommand $command): GroupDataResultDto
    {
        $group = $this->check($command);

        $resultId = $this->transactionRunner->run(function () use ($group): int {
            $author = $this->authorsService->convertGroupToAuthor($group);
            if ($author === null) {
                throw new GroupDataException('The author could not be created', 500);
            }
            $this->actionsLogService->log($group, StructureType::Group, self::PRIVILEGE);
            return $author->getId();
        });

        return new GroupDataResultDto(id: $resultId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws GroupDataException
     */
    public function check(ConvertGroupToAuthorCommand $command): groupElement
    {
        $group = $this->groupRepository->get($command->groupId);
        $this->groupPrivileges->assert($command->groupId, self::PRIVILEGE);

        return $group;
    }
}
