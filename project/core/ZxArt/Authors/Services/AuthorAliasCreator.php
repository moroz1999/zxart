<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;

/**
 * Creates an author alias on behalf of the current user: the element in the letter its name starts with,
 * its fields, and the privileges that let its creator keep editing it. Checks no
 * privilege of its own — the caller does, and refreshes the user's privileges
 * once it is done creating.
 *
 * @psalm-api
 */
final readonly class AuthorAliasCreator
{
    /** @var list<array{string, string}> structure type, action */
    private const array CREATOR_PRIVILEGES = [
        ['authorAlias', 'showPublicForm'],
        ['authorAlias', 'publicReceive'],
        ['authorAlias', 'publicDelete'],
    ];

    public function __construct(
        private AuthorAliasRepository $authorAliasRepository,
        private AuthorAliasFieldsWriter $authorAliasFieldsWriter,
        private CreatorPrivilegesGranter $creatorPrivilegesGranter,
    ) {
    }

    public function create(AuthorAliasFieldsDto $fields): authorAliasElement
    {
        $name = $fields->title ?? '';
        if ($name === '') {
            throw new AuthorAliasDataException('An alias needs a name', 400);
        }
        if ($fields->authorId === null || $fields->authorId <= 0) {
            throw new AuthorAliasDataException('An alias needs its author', 400);
        }
        $element = $this->authorAliasRepository->create($name);
        $this->completeCreation($element, $fields);

        return $element;
    }

    /**
     * Fills and grants an element that was created but not yet written — by
     * {@see create()}, or by the legacy form, which creates its own.
     */
    public function completeCreation(authorAliasElement $element, AuthorAliasFieldsDto $fields): void
    {
        $elementId = $element->getPersistedId();
        $this->authorAliasFieldsWriter->apply($element, $fields);
        $this->creatorPrivilegesGranter->grant($elementId, self::CREATOR_PRIVILEGES);
    }
}
