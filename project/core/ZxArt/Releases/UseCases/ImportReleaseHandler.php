<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use RuntimeException;
use structureElement;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ImportedReleaseMerger;
use ZxArt\Releases\Services\ReleaseByFilesFinder;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\UseCases\TransactionRunner;
use zxReleaseElement;

/**
 * Imports one release of a production: the release the source's id or the
 * release's files lead to, or a new one under the production. An existing
 * release changes only when the policy lets existing releases be updated.
 *
 * @psalm-api
 */
final readonly class ImportReleaseHandler
{
    public function __construct(
        private TransactionRunner $transactionRunner,
        private ImportIdOperator $importIdOperator,
        private ReleaseByFilesFinder $releaseByFilesFinder,
        private ReleaseRepository $releaseRepository,
        private ImportedReleaseMerger $importedReleaseMerger,
    ) {
    }

    /**
     * @return zxReleaseElement|null null when the production was not imported
     * @throws ReleaseDataException 500 when the release cannot be created
     * @throws RuntimeException for an import id leading to another kind of
     *     element, or a credit of an author the structure cannot load
     */
    public function handle(ImportReleaseCommand $command): ?zxReleaseElement
    {
        return $this->transactionRunner->run(function () use ($command): ?zxReleaseElement {
            $release = $this->locate($command);
            if ($release === null) {
                return $this->create($command);
            }
            if ($command->policy->updateExistingReleases) {
                $this->importedReleaseMerger->merge($release, $command->release, $command->origin, $command->policy, false);
            }

            return $release;
        });
    }

    private function locate(ImportReleaseCommand $command): ?zxReleaseElement
    {
        $importId = $command->release->id;
        $element = $this->importIdOperator->getElementByImportId($importId, $command->origin, EntityType::Release);
        if ($element !== null) {
            return $this->asRelease($element, $importId);
        }
        $release = $this->releaseByFilesFinder->find($command->release, $command->prodImportId);
        if ($release !== null) {
            $this->importIdOperator->saveImportId($release->getId(), $importId, $command->origin, EntityType::Release);
        }

        return $release;
    }

    private function create(ImportReleaseCommand $command): ?zxReleaseElement
    {
        $prod = $this->importIdOperator->getElementByImportId($command->prodImportId, $command->origin, EntityType::Prod);
        if ($prod === null) {
            return null;
        }
        $release = $this->releaseRepository->create($prod->getId());
        $this->importIdOperator->saveImportId($release->getPersistedId(), $command->release->id, $command->origin, EntityType::Release);
        $this->importedReleaseMerger->merge($release, $command->release, $command->origin, $command->policy, true);

        return $release;
    }

    private function asRelease(structureElement $element, string $importId): zxReleaseElement
    {
        if (!$element instanceof zxReleaseElement) {
            throw new RuntimeException("Release import id $importId leads to the {$element->structureType} element {$element->getId()}");
        }

        return $element;
    }
}
