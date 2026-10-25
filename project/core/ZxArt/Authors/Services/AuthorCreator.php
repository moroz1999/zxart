<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorElement;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;

/**
 * Creates an author on behalf of the current user: the element in the letter its name starts with,
 * its fields, and the privileges that let its creator keep editing it. Checks no
 * privilege of its own — the caller does, and refreshes the user's privileges
 * once it is done creating.
 *
 * @psalm-api
 */
final readonly class AuthorCreator
{
    /** @var list<array{string, string}> structure type, action */
    private const array CREATOR_PRIVILEGES = [
        ['author', 'showPublicForm'],
        ['author', 'publicReceive'],
        ['author', 'publicDelete'],
        ['author', 'deleteFile'],
    ];

    public function __construct(
        private AuthorRepository $authorRepository,
        private AuthorFieldsWriter $authorFieldsWriter,
        private CreatorPrivilegesGranter $creatorPrivilegesGranter,
    ) {
    }

    public function create(AuthorFieldsDto $fields): authorElement
    {
        $name = $fields->title !== null && $fields->title !== '' ? $fields->title : ($fields->realName ?? '');
        if ($name === '') {
            throw new AuthorDataException('An author needs a name or a real name', 400);
        }
        $element = $this->authorRepository->create($name);
        $this->completeCreation($element, $fields);

        return $element;
    }

    /**
     * Fills and grants an element that was created but not yet written — by
     * {@see create()}, or by the legacy form, which creates its own.
     */
    public function completeCreation(authorElement $element, AuthorFieldsDto $fields): void
    {
        $elementId = $element->getPersistedId();
        $this->authorFieldsWriter->apply($element, $fields);
        $this->creatorPrivilegesGranter->grant($elementId, self::CREATOR_PRIVILEGES);
    }
}
