<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use structureManager;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Shared\References\InvalidReferenceException;

/**
 * Writes the editable fields of an author alias; a renamed alias moves to the
 * letter its name starts with. Only the fields the DTO carries are touched, and
 * the author is checked before anything is written.
 *
 * Checks no privilege and writes no log — the caller does.
 *
 * @psalm-api
 */
final readonly class AuthorAliasFieldsWriter
{
    public function __construct(
        private EntityReferenceResolver $entityReferenceResolver,
        private AuthorAliasRepository $authorAliasRepository,
        private structureManager $structureManager,
    ) {
    }

    public function apply(authorAliasElement $alias, AuthorAliasFieldsDto $fields): void
    {
        $previousOwnerId = ElementFields::number($alias, 'authorId');
        if ($fields->authorId !== null) {
            try {
                $this->entityReferenceResolver->element($fields->authorId, [authorElement::class], 'author');
            } catch (InvalidReferenceException $exception) {
                throw new AuthorAliasDataException($exception->getMessage(), 400, $exception);
            }
        }

        ElementFields::assign($alias, [
            'title' => $fields->title,
            'structureName' => $fields->title,
            'authorId' => $fields->authorId,
            'startDate' => $fields->startDate,
            'endDate' => $fields->endDate,
            'displayInMusic' => $fields->displayInMusic,
            'displayInGraphics' => $fields->displayInGraphics,
        ]);
        $this->authorAliasRepository->save($alias);
        if ($fields->authorId !== null) {
            foreach (array_unique([$previousOwnerId, $fields->authorId]) as $ownerId) {
                $this->resetAliases($ownerId);
            }
        }
        if ($fields->title !== null) {
            $alias->checkParentLetter();
        }
    }

    /**
     * The owner of a moved alias reads its aliases again, as an owner that
     * loaded them before would not see the change otherwise.
     */
    private function resetAliases(int $ownerId): void
    {
        $owner = $ownerId > 0 ? $this->structureManager->getElementById($ownerId) : null;
        if ($owner instanceof authorElement) {
            $owner->resetAliasElements();
        }
    }
}
