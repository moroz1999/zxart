<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Groups\Services\GroupAliasPrivileges;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * @psalm-api
 */
final readonly class DeleteGroupAliasMemberHandler
{
    private const string PRIVILEGE = 'deleteAuthor';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupAliasRepository $groupAliasRepository,
        private GroupAliasPrivileges $groupAliasPrivileges,
        private AuthorshipDbal $authorshipDbal,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupAliasDataException
     */
    public function handle(DeleteGroupAliasMemberCommand $command): GroupAliasDataResultDto
    {
        $alias = $this->groupAliasRepository->get($command->aliasId);
        $this->groupAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);
        if ($command->authorId <= 0) {
            throw new GroupAliasDataException('Missing required author id', 400);
        }
        $credits = array_filter(
            $this->authorshipDbal->findByElement($command->aliasId, EntityType::Group),
            static fn(AuthorshipDto $authorship): bool => $authorship->authorId === $command->authorId,
        );
        if ($credits === []) {
            throw new GroupAliasDataException('The author is not a member of this group alias', 404);
        }

        $this->transactionRunner->run(function () use ($alias, $command): void {
            $this->authorshipDbal->deleteAuthorship($command->aliasId, $command->authorId, EntityType::Group);
            $this->groupAliasRepository->touch($alias);
            $this->actionsLogService->log($alias, StructureType::GroupAlias, self::PRIVILEGE);
        });

        return new GroupAliasDataResultDto(id: $command->aliasId);
    }
}
