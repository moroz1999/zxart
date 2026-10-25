<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use authorAliasElement;
use authorElement;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ProdPrivileges;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Adds the author to the production's authors, or makes their roles these.
 *
 * @psalm-api
 */
final readonly class SetProdMemberHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ProdRepository $prodRepository,
        private ProdPrivileges $prodPrivileges,
        private structureManager $structureManager,
        private AuthorshipRepository $authorshipRepository,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function handle(SetProdMemberCommand $command): ProdDataResultDto
    {
        $prod = $this->prodRepository->get($command->prodId);
        $this->prodPrivileges->assert($command->prodId, self::PRIVILEGE);
        $member = $command->authorId > 0 ? $this->structureManager->getElementById($command->authorId) : null;
        if (!$member instanceof authorElement && !$member instanceof authorAliasElement) {
            throw new ProdDataException('A member must be an author or an author alias', 400);
        }
        $unknownRoles = array_diff($command->roles, AuthorshipRoles::LIST);
        if ($unknownRoles !== []) {
            throw new ProdDataException('Unknown role: ' . implode(', ', $unknownRoles), 400);
        }

        $this->transactionRunner->run(function () use ($prod, $command): void {
            $roles = $command->roles === [] ? ['unknown'] : $command->roles;
            $this->authorshipRepository->setAuthorship($command->prodId, $command->authorId, EntityType::Prod, $roles, null, null);
            $this->prodRepository->touch($prod);
            $this->actionsLogService->log($prod, StructureType::ZxProd, self::PRIVILEGE);
        });

        return new ProdDataResultDto(id: $command->prodId);
    }
}
