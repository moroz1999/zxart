<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Authors\Services\ImportedAuthorAliasMerger;
use ZxArt\Authors\Services\ImportedAuthorsFinder;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\LabelResolver;
use ZxArt\Import\Labels\PersonLabel;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Imports an author alias from a label: the alias it stands for, by import id
 * or else by name, gets what the label adds; with none, a new one is created.
 * One transaction: an import that stops halfway leaves nothing behind.
 *
 * @psalm-api
 */
final readonly class ImportAuthorAliasHandler
{
    public function __construct(
        private TransactionRunner $transactionRunner,
        private ImportedAuthorsFinder $importedAuthorsFinder,
        private LabelResolver $labelResolver,
        private ImportIdOperator $importIdOperator,
        private ImportedAuthorAliasMerger $importedAuthorAliasMerger,
        private AuthorsService $authorsService,
    ) {
    }

    public function handle(ImportAuthorAliasCommand $command): authorElement|authorAliasElement|groupElement|groupAliasElement|null
    {
        $personLabel = $command->label;
        $origin = $command->origin;

        return $this->transactionRunner->run(function () use ($command, $personLabel, $origin): authorElement|authorAliasElement|groupElement|groupAliasElement|null {
            $importId = $personLabel->id;
            $element = $this->importedAuthorsFinder->getAuthorAliasByLabel($personLabel, $origin);
            if (!$element) {
                if ($element = $this->labelResolver->resolve($personLabel)) {
                    $this->importIdOperator->saveImportId($element->getId(), $importId, $origin, EntityType::Author);
                    if ($element->structureType === 'authorAlias') {
                        $this->importedAuthorAliasMerger->merge($element, $personLabel, $origin);
                    }
                } elseif ($command->createNew) {
                    $element = $this->createAuthorAlias($personLabel, $origin);
                }
            } elseif ($element->structureType === 'authorAlias') {
                $this->importedAuthorAliasMerger->merge($element, $personLabel, $origin);
            }

            $this->importedAuthorsFinder->rememberAlias($origin, $importId, $element);
            return $element;
        });
    }

    private function createAuthorAlias(PersonLabel $authorAlias, ImportOrigin $origin): ?authorAliasElement
    {
        $title = trim($authorAlias->name ?? '');
        if ($title === '') {
            return null;
        }

        /** @var authorAliasElement|null $element */
        $element = $this->authorsService->manufactureAliasElement($title);
        if (!$element) {
            return null;
        }

        $this->importedAuthorAliasMerger->merge($element, $authorAlias, $origin);

        if ($authorAlias->id !== null) {
            $this->importIdOperator->saveImportId($element->getId(), (string)$authorAlias->id, $origin, EntityType::Author);
        }

        return $element;
    }
}
