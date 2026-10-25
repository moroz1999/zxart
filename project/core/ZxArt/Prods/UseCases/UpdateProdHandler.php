<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ProdFieldsWriter;
use ZxArt\Prods\Services\ProdPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * @psalm-api
 */
final readonly class UpdateProdHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ProdRepository $prodRepository,
        private ProdPrivileges $prodPrivileges,
        private ProdFieldsWriter $prodFieldsWriter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function handle(UpdateProdCommand $command): ProdDataResultDto
    {
        $prod = $this->prodRepository->get($command->prodId);
        $this->prodPrivileges->assert($command->prodId, self::PRIVILEGE);
        if ($command->fields->title !== null && trim($command->fields->title) === '') {
            throw new ProdDataException('A production needs a title', 400);
        }

        $this->transactionRunner->run(function () use ($prod, $command): void {
            $this->prodFieldsWriter->apply($prod, $command->fields);
            $this->actionsLogService->log($prod, StructureType::ZxProd, self::PRIVILEGE);
        });

        return new ProdDataResultDto(id: $command->prodId);
    }
}
