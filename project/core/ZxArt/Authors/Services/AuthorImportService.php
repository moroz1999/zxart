<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use ZxArt\Authors\UseCases\ImportAuthorAliasCommand;
use ZxArt\Authors\UseCases\ImportAuthorAliasHandler;
use ZxArt\Authors\UseCases\ImportAuthorCommand;
use ZxArt\Authors\UseCases\ImportAuthorHandler;
use ZxArt\Authors\UseCases\ImportAuthorLabelCommand;
use ZxArt\Authors\UseCases\ImportAuthorLabelHandler;
use ZxArt\Import\Authors\AuthorImportPolicy;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\PersonLabel;

/**
 * The author imports, each one a use case in `ZxArt\Authors\UseCases`. An
 * import source sets what its imports may overwrite once, through the setters;
 * every import after it runs with that policy.
 *
 * @psalm-api
 */
final class AuthorImportService
{
    private bool $forceUpdateRealName = false;
    private bool $forceUpdateCountry = false;
    private bool $forceUpdateCity = false;
    private bool $forceUpdateGroups = false;

    public function __construct(
        private readonly ImportAuthorHandler $importAuthorHandler,
        private readonly ImportAuthorLabelHandler $importAuthorLabelHandler,
        private readonly ImportAuthorAliasHandler $importAuthorAliasHandler,
    ) {
    }

    public function setForceUpdateRealName(bool $forceUpdateRealName): void
    {
        $this->forceUpdateRealName = $forceUpdateRealName;
    }

    public function setForceUpdateGroups(bool $forceUpdateGroups): void
    {
        $this->forceUpdateGroups = $forceUpdateGroups;
    }

    public function setForceUpdateCountry(bool $forceUpdateCountry): void
    {
        $this->forceUpdateCountry = $forceUpdateCountry;
    }

    public function setForceUpdateCity(bool $forceUpdateCity): void
    {
        $this->forceUpdateCity = $forceUpdateCity;
    }

    public function importAuthor(PersonLabel $label, ImportOrigin $origin, bool $createNew = true): ?authorElement
    {
        return $this->importAuthorHandler->handle(new ImportAuthorCommand($label, $origin, $this->policy(), $createNew));
    }

    public function importLabel(PersonLabel $label, ImportOrigin $origin): authorElement|authorAliasElement|null
    {
        return $this->importAuthorLabelHandler->handle(new ImportAuthorLabelCommand($label, $origin, $this->policy()));
    }

    public function importAuthorAlias(PersonLabel $personLabel, ImportOrigin $origin, bool $createNew = true): authorElement|authorAliasElement|groupElement|groupAliasElement|null
    {
        return $this->importAuthorAliasHandler->handle(new ImportAuthorAliasCommand($personLabel, $origin, $createNew));
    }

    private function policy(): AuthorImportPolicy
    {
        return new AuthorImportPolicy(
            forceUpdateRealName: $this->forceUpdateRealName,
            forceUpdateCountry: $this->forceUpdateCountry,
            forceUpdateCity: $this->forceUpdateCity,
            forceUpdateGroups: $this->forceUpdateGroups,
        );
    }
}
