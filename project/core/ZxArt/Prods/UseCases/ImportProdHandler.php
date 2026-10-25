<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use RuntimeException;
use structureElement;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\ProdResolver;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ImportedProdMerger;
use ZxArt\Releases\Services\ReleaseByFilesFinder;
use ZxArt\Releases\UseCases\ImportReleaseCommand;
use ZxArt\Releases\UseCases\ImportReleaseHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\UseCases\TransactionRunner;
use ZxArt\ZxProdCategories\CategoryIds;
use zxProdElement;

/**
 * Imports a production with its releases, all in one transaction. The
 * production is the one the source's id leads to, or its id in another source,
 * or the one holding a release of the same files, or one of its title and
 * year; the source's id is stored for any of them. None found, a new one is
 * created under its first category. An existing production changes only when
 * the policy lets existing productions be updated; its releases are imported
 * either way.
 *
 * @psalm-api
 */
final readonly class ImportProdHandler
{
    private const int DEFAULT_CATEGORY_ID = CategoryIds::MISC->value;

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ImportIdOperator $importIdOperator,
        private ReleaseByFilesFinder $releaseByFilesFinder,
        private ProdResolver $prodResolver,
        private ProdRepository $prodRepository,
        private ImportedProdMerger $importedProdMerger,
        private ImportReleaseHandler $importReleaseHandler,
    ) {
    }

    /**
     * @throws ProdDataException 500 when the production cannot be created
     * @throws RuntimeException for an import id leading to another kind of
     *     element, or a credit of an author the structure cannot load
     */
    public function handle(ImportProdCommand $command): zxProdElement
    {
        return $this->transactionRunner->run(function () use ($command): zxProdElement {
            $dto = $command->prod;
            $prod = $this->locate($command);
            if ($prod === null) {
                $prod = $this->create($command);
            } elseif ($command->policy->updateExistingProds) {
                $this->importedProdMerger->merge($prod, $dto, $command->origin, $command->policy, false);
            }

            foreach ($dto->releases ?? [] as $release) {
                $this->importReleaseHandler->handle(
                    new ImportReleaseCommand($release, $dto->id, $command->origin, $command->policy),
                );
            }

            return $prod;
        });
    }

    private function locate(ImportProdCommand $command): ?zxProdElement
    {
        $dto = $command->prod;
        $origin = $command->origin;
        $element = $this->importIdOperator->getElementByImportId($dto->id, $origin, EntityType::Prod);
        if ($element !== null) {
            return $this->asProd($element, $dto->id);
        }

        $prod = $this->findByOtherOrigins($dto)
            ?? $this->findByReleaseFiles($dto)
            ?? $this->prodResolver->resolve($dto, $command->policy->matchProdsWithoutYear);
        if ($prod !== null) {
            $this->importIdOperator->saveImportId($prod->getId(), $dto->id, $origin, EntityType::Prod);
        }

        return $prod;
    }

    private function findByOtherOrigins(ProdImportDTO $dto): ?zxProdElement
    {
        foreach ($dto->ids ?? [] as $idOrigin => $id) {
            $knownOrigin = ImportOrigin::tryFrom($idOrigin);
            if ($knownOrigin === null) {
                continue;
            }
            $importId = (string)$id;
            $element = $this->importIdOperator->getElementByImportId($importId, $knownOrigin, EntityType::Prod);
            if ($element !== null) {
                return $this->asProd($element, $importId);
            }
        }

        return null;
    }

    private function findByReleaseFiles(ProdImportDTO $dto): ?zxProdElement
    {
        foreach ($dto->releases ?? [] as $release) {
            $releaseElement = $this->releaseByFilesFinder->find($release, $dto->id);
            if ($releaseElement !== null) {
                return $releaseElement->getProd();
            }
        }

        return null;
    }

    private function create(ImportProdCommand $command): zxProdElement
    {
        $dto = $command->prod;
        $category = $dto->directCategories[0] ?? 0;
        if ($category === 0) {
            $category = self::DEFAULT_CATEGORY_ID;
        }

        $prod = $this->prodRepository->create($category, 'zxProdCategory');
        $prod->dateAdded = time();
        $this->importIdOperator->saveImportId($prod->getPersistedId(), $dto->id, $command->origin, EntityType::Prod);
        $this->importedProdMerger->merge($prod, $dto, $command->origin, $command->policy, true);

        return $prod;
    }

    private function asProd(structureElement $element, string $importId): zxProdElement
    {
        if (!$element instanceof zxProdElement) {
            throw new RuntimeException("Production import id $importId leads to the {$element->structureType} element {$element->getId()}");
        }

        return $element;
    }
}
