<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ProdPrivileges;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Removes the author from the production's authors.
 *
 * @psalm-api
 */
final readonly class DeleteProdMemberHandler
{
    private const string PRIVILEGE = 'deleteAuthor';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ProdRepository $prodRepository,
        private ProdPrivileges $prodPrivileges,
        private AuthorshipDbal $authorshipDbal,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function handle(DeleteProdMemberCommand $command): ProdDataResultDto
    {
        $prod = $this->prodRepository->get($command->prodId);
        $this->prodPrivileges->assert($command->prodId, self::PRIVILEGE);
        if ($command->authorId <= 0) {
            throw new ProdDataException('Missing required author id', 400);
        }
        $credits = array_filter(
            $this->authorshipDbal->findByElement($command->prodId, EntityType::Prod),
            static fn(AuthorshipDto $authorship): bool => $authorship->authorId === $command->authorId,
        );
        if ($credits === []) {
            throw new ProdDataException('The author is not a member of this production', 404);
        }

        $this->transactionRunner->run(function () use ($prod, $command): void {
            $this->authorshipDbal->deleteAuthorship($command->prodId, $command->authorId, EntityType::Prod);
            $this->prodRepository->touch($prod);
            $this->actionsLogService->log($prod, StructureType::ZxProd, self::PRIVILEGE);
        });

        return new ProdDataResultDto(id: $command->prodId);
    }
}
