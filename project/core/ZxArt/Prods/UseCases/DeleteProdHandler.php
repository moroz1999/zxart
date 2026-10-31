<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ProdPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use zxProdElement;

/**
 * @psalm-api
 */
final readonly class DeleteProdHandler
{
    private const string PRIVILEGE = 'publicDelete';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ProdRepository $prodRepository,
        private ProdPrivileges $prodPrivileges,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function handle(DeleteProdCommand $command): ProdDataResultDto
    {
        $prod = $this->check($command);

        $this->transactionRunner->run(function () use ($prod): void {
            // logged first: the element data is gone after the deletion
            $this->actionsLogService->log($prod, StructureType::ZxProd, self::PRIVILEGE);
            $this->prodRepository->delete($prod);
        });

        return new ProdDataResultDto(id: $command->prodId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws ProdDataException
     */
    public function check(DeleteProdCommand $command): zxProdElement
    {
        $prod = $this->prodRepository->get($command->prodId);
        $this->prodPrivileges->assert($command->prodId, self::PRIVILEGE);

        return $prod;
    }
}
