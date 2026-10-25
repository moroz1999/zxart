<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\PersonLabel;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Shared\EntityType;

/**
 * Finds the author or author alias an import label stands for. What this run
 * has imported already is remembered, under the origin and the import id.
 *
 * @psalm-api
 */
final class ImportedAuthorsFinder
{
    /** @var array<string, array<string, mixed>> origin => import id => author */
    private array $authors = [];
    /** @var array<string, array<string, mixed>> origin => import id => author alias */
    private array $aliases = [];

    public function __construct(
        private readonly ImportIdOperator $importIdOperator,
    ) {
    }

    public function getAuthorByLabel(PersonLabel $label, ImportOrigin $origin): authorElement|null
    {
        $authorId = $label->id;
        if (isset($this->authors[$origin->value][$authorId])) {
            return $this->authors[$origin->value][$authorId];
        }
        $author = $this->importIdOperator->getElementByImportId($authorId, $origin, EntityType::Author);
        if ($author?->structureType === 'author') {
            return $author;
        }
        return null;
    }

    public function getAuthorAliasByLabel(PersonLabel $personLabel, ImportOrigin $origin): authorAliasElement|null
    {
        $importId = (string)$personLabel->id;
        if (isset($this->aliases[$origin->value][$importId])) {
            return $this->aliases[$origin->value][$importId];
        }

        return $this->importIdOperator->getElementByImportId($importId, $origin, EntityType::AuthorAlias);
    }

    public function rememberAuthor(ImportOrigin $origin, string $importId, mixed $element): void
    {
        $this->authors[$origin->value][$importId] = $element;
    }

    public function rememberAlias(ImportOrigin $origin, ?string $importId, mixed $element): void
    {
        $this->aliases[$origin->value][(string)$importId] = $element;
    }
}
