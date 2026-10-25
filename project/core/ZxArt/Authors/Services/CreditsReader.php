<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use structureElement;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Shared\Dto\CreditDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefs;

/**
 * The authors credited on an element, by title, with their roles.
 *
 * @psalm-api
 */
final readonly class CreditsReader
{
    public function __construct(
        private AuthorshipDbal $authorshipDbal,
    ) {
    }

    /**
     * @return list<CreditDto>
     */
    public function read(int $elementId, EntityType $type): array
    {
        $credits = [];
        /** @var array{authorElement: structureElement, roles: list<string>} $record */
        foreach ($this->authorshipDbal->getAuthorsInfo($elementId, $type) as $record) {
            $credits[] = new CreditDto(EntityRefs::of($record['authorElement']), $record['roles']);
        }

        return $credits;
    }
}
