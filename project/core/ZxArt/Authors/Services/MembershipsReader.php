<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use structureElement;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Shared\Dto\AliasDto;
use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * The group memberships of an author or a group, from either side, in the
 * order they were added, and the aliases of either.
 *
 * @psalm-api
 */
final readonly class MembershipsReader
{
    public function __construct(
        private AuthorshipDbal $authorshipDbal,
        private EntityRefsReader $entityRefsReader,
    ) {
    }

    /**
     * The groups (or group aliases) the author or author alias is a member of.
     *
     * @return list<MembershipDto>
     */
    public function groupsOf(int $authorId): array
    {
        $memberships = [];
        foreach ($this->authorshipDbal->findByAuthor($authorId, EntityType::Group) as $record) {
            $group = $this->entityRefsReader->read([$record->elementId])[0] ?? null;
            if ($group !== null) {
                $memberships[] = new MembershipDto($group, $record->roles, $record->startDate, $record->endDate);
            }
        }

        return $memberships;
    }

    /**
     * The members (authors or author aliases) of the group or group alias.
     *
     * @return list<MembershipDto>
     */
    public function membersOf(int $groupId): array
    {
        $memberships = [];
        foreach ($this->authorshipDbal->findByElement($groupId, EntityType::Group) as $record) {
            $member = $this->entityRefsReader->read([$record->authorId])[0] ?? null;
            if ($member !== null) {
                $memberships[] = new MembershipDto($member, $record->roles, $record->startDate, $record->endDate);
            }
        }

        return $memberships;
    }

    /**
     * @param iterable<structureElement> $aliases
     * @return list<AliasDto>
     */
    public function aliases(iterable $aliases): array
    {
        $found = [];
        foreach ($aliases as $alias) {
            $found[] = new AliasDto(
                $alias->getId(),
                html_entity_decode(ElementFields::text($alias, 'title'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                ElementFields::timestamp($alias, 'startDate'),
                ElementFields::timestamp($alias, 'endDate'),
            );
        }

        return $found;
    }
}
