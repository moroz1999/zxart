<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Catalogues;

/**
 * @psalm-api
 */
final readonly class OptionsDto
{
    /**
     * @param list<string> $legalStatuses of a production
     * @param list<string> $releaseTypes
     * @param list<string> $releaseFormats
     * @param list<string> $languages language codes of productions and releases
     * @param list<string> $groupTypes
     * @param list<string> $authorRoles the roles of an author credited on a production or release
     * @param list<string> $memberRoles the roles of a member of a group
     */
    public function __construct(
        public array $legalStatuses,
        public array $releaseTypes,
        public array $releaseFormats,
        public array $languages,
        public array $groupTypes,
        public array $authorRoles,
        public array $memberRoles,
    ) {
    }
}
