<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use authorAliasElement;
use authorElement;
use ZxArt\Authors\Services\ImportedAuthorAliasMerger;
use ZxArt\Authors\Services\ImportedAuthorMerger;
use ZxArt\Authors\Services\ImportedAuthorsFinder;
use ZxArt\Import\Labels\LabelResolver;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Imports a label that may stand for an author or an author alias: the one it
 * matches gets what the label adds; with none, it is imported as an author.
 * One transaction: an import that stops halfway leaves nothing behind.
 *
 * @psalm-api
 */
final readonly class ImportAuthorLabelHandler
{
    public function __construct(
        private TransactionRunner $transactionRunner,
        private ImportedAuthorsFinder $importedAuthorsFinder,
        private LabelResolver $labelResolver,
        private ImportIdOperator $importIdOperator,
        private ImportedAuthorMerger $importedAuthorMerger,
        private ImportedAuthorAliasMerger $importedAuthorAliasMerger,
        private ImportAuthorHandler $importAuthorHandler,
    ) {
    }

    public function handle(ImportAuthorLabelCommand $command): authorElement|authorAliasElement|null
    {
        $label = $command->label;
        $origin = $command->origin;
        $policy = $command->policy;
        $authorId = $label->id;
        if ($authorId === null) {
            return null;
        }

        return $this->transactionRunner->run(function () use ($label, $origin, $policy, $authorId) {
            $element = $this->importedAuthorsFinder->getAuthorByLabel($label, $origin);

            if ($element === null) {
                $element = $this->importedAuthorsFinder->getAuthorAliasByLabel($label, $origin);
            }
            if ($element === null) {
                $resolved = $this->labelResolver->resolve($label);
                if ($resolved && $resolved->structureType === 'author') {
                    $this->importedAuthorMerger->merge($resolved, $label, $origin, $policy);
                    $this->importIdOperator->saveImportId($resolved->getId(), $authorId, $origin, EntityType::Author);
                    return $resolved;
                }
                if ($resolved && $resolved->structureType === 'authorAlias') {
                    $this->importedAuthorAliasMerger->merge($resolved, $label, $origin);
                    $this->importIdOperator->saveImportId($resolved->getId(), $authorId, $origin, EntityType::Author);
                    return $resolved;
                }
            }
            return $this->importAuthorHandler->handle(new ImportAuthorCommand($label, $origin, $policy));
        });
    }
}
