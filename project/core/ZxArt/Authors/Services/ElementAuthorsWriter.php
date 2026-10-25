<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Groups\GroupMemberRoles;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Shared\References\InvalidReferenceException;

/**
 * Makes the authors of an element — the members of a group — exactly the given
 * list: listed ones are stored with their roles and dates, the others of that
 * type are removed. An author listed together with one of their aliases is
 * credited as the alias, with the author's roles; of several aliases of one
 * author only the first is credited. No roles are stored as `unknown`.
 *
 * Every author and role is checked before anything is written. Checks no
 * privilege and writes no log — the caller does.
 *
 * @psalm-api
 */
final readonly class ElementAuthorsWriter
{
    public function __construct(
        private AuthorshipDbal $authorshipDbal,
        private EntityReferenceResolver $entityReferenceResolver,
    ) {
    }

    /**
     * @param list<AuthorshipInputDto> $authors
     * @throws InvalidReferenceException
     */
    public function replace(int $elementId, EntityType $type, array $authors): void
    {
        $credited = $this->credited($type, $authors);

        foreach ($credited as $author) {
            $this->authorshipDbal->setAuthorship(
                $elementId,
                $author->authorId,
                $type,
                $author->roles !== [] ? $author->roles : ['unknown'],
                $author->startDate,
                $author->endDate,
            );
        }
        foreach ($this->authorshipDbal->findByElement($elementId, $type) as $record) {
            if (!isset($credited[$record->authorId])) {
                $this->authorshipDbal->deleteAuthorship($elementId, $record->authorId, $type);
            }
        }
    }

    /**
     * @param list<AuthorshipInputDto> $authors
     * @return array<int, AuthorshipInputDto> author id => the record to store
     */
    private function credited(EntityType $type, array $authors): array
    {
        $allowedRoles = $type === EntityType::Group ? GroupMemberRoles::LIST : AuthorshipRoles::LIST;
        /** @var array<int, int> $ownerOf alias id => its author */
        $ownerOf = [];
        foreach ($authors as $author) {
            $unknownRoles = array_diff($author->roles, $allowedRoles);
            if ($unknownRoles !== []) {
                throw new InvalidReferenceException('Unknown role ' . implode(', ', $unknownRoles));
            }
            $element = $this->entityReferenceResolver->element(
                $author->authorId,
                [authorElement::class, authorAliasElement::class],
                'author',
            );
            if ($element instanceof authorAliasElement) {
                $ownerOf[$author->authorId] = (int)$element->authorId;
            }
        }

        $listed = [];
        foreach ($authors as $author) {
            $listed[$author->authorId] = $author;
        }
        $credited = [];
        $creditedOwners = [];
        foreach ($authors as $author) {
            $ownerId = $ownerOf[$author->authorId] ?? null;
            if ($ownerId === null) {
                if (!in_array($author->authorId, $ownerOf, true)) {
                    $credited[$author->authorId] = $author;
                }
                continue;
            }
            if (isset($creditedOwners[$ownerId])) {
                continue;
            }
            $creditedOwners[$ownerId] = true;
            $owner = $listed[$ownerId] ?? null;
            $credited[$author->authorId] = $owner !== null && $owner->roles !== []
                ? new AuthorshipInputDto($author->authorId, $owner->roles, $author->startDate, $author->endDate)
                : $author;
        }

        return $credited;
    }
}
