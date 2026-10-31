<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ProdPrivileges;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use zxProdElement;

/**
 * Moves the releases and materials of the joined production into this one and
 * deletes it. Without `releasesOnly` its authors, groups, categories, privileges
 * and the fields this one lacks move too.
 *
 * @psalm-api
 */
final readonly class JoinProdsHandler
{
    private const string PRIVILEGE = 'join';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ProdRepository $prodRepository,
        private ProdPrivileges $prodPrivileges,
        private ProdsService $prodsService,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function handle(JoinProdsCommand $command): ProdDataResultDto
    {
        $prod = $this->check($command);

        $this->transactionRunner->run(function () use ($prod, $command): void {
            $this->actionsLogService->log($prod, StructureType::ZxProd, self::PRIVILEGE);
            $this->prodsService->joinDeleteZxProd($command->prodId, $command->joinedId, $command->releasesOnly);
        });

        return new ProdDataResultDto(id: $command->prodId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws ProdDataException
     */
    public function check(JoinProdsCommand $command): zxProdElement
    {
        if ($command->joinedId === $command->prodId) {
            throw new ProdDataException('A production cannot be joined into itself', 400);
        }
        $prod = $this->prodRepository->get($command->prodId);
        $this->prodPrivileges->assert($command->prodId, self::PRIVILEGE);
        $this->prodRepository->get($command->joinedId);
        $this->prodPrivileges->assert($command->joinedId, self::PRIVILEGE);

        return $prod;
    }
}
