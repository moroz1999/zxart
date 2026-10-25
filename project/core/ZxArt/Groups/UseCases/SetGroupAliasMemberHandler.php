<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use authorAliasElement;
use authorElement;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\GroupMemberRoles;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Groups\Services\GroupAliasPrivileges;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Adds the author to the group alias's members, or makes their roles these.
 *
 * @psalm-api
 */
final readonly class SetGroupAliasMemberHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupAliasRepository $groupAliasRepository,
        private GroupAliasPrivileges $groupAliasPrivileges,
        private structureManager $structureManager,
        private AuthorshipDbal $authorshipDbal,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupAliasDataException
     */
    public function handle(SetGroupAliasMemberCommand $command): GroupAliasDataResultDto
    {
        $alias = $this->groupAliasRepository->get($command->aliasId);
        $this->groupAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);
        $member = $command->authorId > 0 ? $this->structureManager->getElementById($command->authorId) : null;
        if (!$member instanceof authorElement && !$member instanceof authorAliasElement) {
            throw new GroupAliasDataException('A member must be an author or an author alias', 400);
        }
        $unknownRoles = array_diff($command->roles, GroupMemberRoles::LIST);
        if ($unknownRoles !== []) {
            throw new GroupAliasDataException('Unknown role: ' . implode(', ', $unknownRoles), 400);
        }

        $this->transactionRunner->run(function () use ($alias, $command): void {
            $roles = $command->roles === [] ? ['unknown'] : $command->roles;
            $this->authorshipDbal->setAuthorship($command->aliasId, $command->authorId, EntityType::Group, $roles, $command->startDate, $command->endDate);
            $this->groupAliasRepository->touch($alias);
            $this->actionsLogService->log($alias, StructureType::GroupAlias, self::PRIVILEGE);
        });

        return new GroupAliasDataResultDto(id: $command->aliasId);
    }
}
