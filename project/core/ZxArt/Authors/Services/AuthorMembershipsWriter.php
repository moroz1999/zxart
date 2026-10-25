<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use groupAliasElement;
use groupElement;
use InvalidArgumentException;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Dto\MembershipInputDto;
use ZxArt\Groups\GroupMemberRoles;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Shared\References\InvalidReferenceException;
use zxProdElement;

/**
 * Makes what an author — or an author alias — takes part in, of one type,
 * exactly the given list: the groups (and group aliases) they belong to, or
 * the productions they worked on. Records of other types are untouched. No
 * roles are stored as `unknown`.
 *
 * Every element and role is checked before anything is written. Checks no
 * privilege and writes no log — the caller does.
 *
 * @psalm-api
 */
final readonly class AuthorMembershipsWriter
{
    public function __construct(
        private AuthorshipDbal $authorshipDbal,
        private EntityReferenceResolver $entityReferenceResolver,
    ) {
    }

    /**
     * @param list<MembershipInputDto> $memberships
     * @throws InvalidReferenceException
     */
    public function replace(int $authorId, EntityType $type, array $memberships): void
    {
        [$elementTypes, $allowedRoles] = match ($type) {
            EntityType::Group => [[groupElement::class, groupAliasElement::class], GroupMemberRoles::LIST],
            EntityType::Prod => [[zxProdElement::class], AuthorshipRoles::LIST],
            default => throw new InvalidArgumentException("An author takes no part in a {$type->value}"),
        };
        $listed = [];
        foreach ($memberships as $membership) {
            $unknownRoles = array_diff($membership->roles, $allowedRoles);
            if ($unknownRoles !== []) {
                throw new InvalidReferenceException('Unknown role ' . implode(', ', $unknownRoles));
            }
            $this->entityReferenceResolver->element($membership->elementId, $elementTypes, $type->value);
            $listed[$membership->elementId] = $membership;
        }

        foreach ($listed as $membership) {
            $this->authorshipDbal->setAuthorship(
                $membership->elementId,
                $authorId,
                $type,
                $membership->roles !== [] ? $membership->roles : ['unknown'],
                $membership->startDate,
                $membership->endDate,
            );
        }
        foreach ($this->authorshipDbal->findByAuthor($authorId, $type) as $record) {
            if (!isset($listed[$record->elementId])) {
                $this->authorshipDbal->deleteAuthorship($record->elementId, $authorId, $type);
            }
        }
    }
}
