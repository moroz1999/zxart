<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use App\Users\CurrentUserService;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Services\ProdCreator;
use ZxArt\Prods\Services\ProdPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * @psalm-api
 */
final readonly class CreateProdHandler
{
    /** Held on the root: the privilege that opens the production upload form. */
    private const string PRIVILEGE = 'batchUpload';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private structureManager $structureManager,
        private ProdPrivileges $prodPrivileges,
        private ProdCreator $prodCreator,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function handle(CreateProdCommand $command): ProdDataResultDto
    {
        $this->check($command);

        $prodId = $this->transactionRunner->run(function () use ($command): int {
            $prod = $this->prodCreator->create($command->fields);
            $this->actionsLogService->log($prod, StructureType::ZxProd, self::PRIVILEGE);
            return $prod->getId();
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new ProdDataResultDto(id: $prodId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws ProdDataException
     */
    public function check(CreateProdCommand $command): void
    {
        if (trim($command->fields->title ?? '') === '') {
            throw new ProdDataException('A production needs a title', 400);
        }
        // the privileges of an element are worked out as it loads
        $this->structureManager->getRootElement();
        $this->prodPrivileges->assert($this->structureManager->getRootElementId(), self::PRIVILEGE, StructureType::ZxProdsUploadForm);
    }
}
