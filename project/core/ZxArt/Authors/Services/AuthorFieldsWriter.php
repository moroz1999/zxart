<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorElement;
use cityElement;
use countryElement;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Shared\References\InvalidReferenceException;

/**
 * Writes the editable fields of an author and what follows from them: a blank
 * title is the real name, a renamed author moves to the letter its name starts
 * with, the ratings are recalculated, and changed conversion defaults reconvert
 * the author's tunes. Only the fields the DTO carries are touched, and every
 * reference is checked before anything is written.
 *
 * Checks no privilege and writes no log — the caller does.
 *
 * @psalm-api
 */
final readonly class AuthorFieldsWriter
{
    public function __construct(
        private EntityReferenceResolver $entityReferenceResolver,
        private AuthorRepository $authorRepository,
    ) {
    }

    /**
     * @param array<int, string>|null $realNames language id => the real name in
     *        it, for a real name that differs by language; it replaces
     *        `realName` in the languages it names
     */
    public function apply(authorElement $author, AuthorFieldsDto $fields, ?array $realNames = null): void
    {
        $this->checkReferences($fields);
        $title = $fields->title === '' ? ($fields->realName ?? $author->realName) : $fields->title;

        ElementFields::assign($author, [
            'title' => $title,
            'structureName' => $title,
            'realName' => $fields->realName,
            'country' => $fields->countryId,
            'city' => $fields->cityId,
            'wikiLink' => $fields->wikiLink,
            'denyVoting' => $fields->denyVoting,
            'denyComments' => $fields->denyComments,
            'deny3a' => $fields->deny3a,
            'displayInMusic' => $fields->displayInMusic,
            'displayInGraphics' => $fields->displayInGraphics,
            'chipType' => $fields->chipType,
            'channelsType' => $fields->channelsType,
            'frequency' => $fields->frequency,
            'intFrequency' => $fields->intFrequency,
            'palette' => $fields->palette,
            'zxTunesId' => $fields->zxTunesId,
        ]);
        foreach ($realNames ?? [] as $languageId => $realName) {
            $author->setValue('realName', $realName, $languageId);
        }
        $this->authorRepository->save($author);
        if ($title !== null) {
            $author->checkParentLetter();
        }
        $author->recalculate();

        $isConversionChanged = $fields->chipType !== null
            || $fields->channelsType !== null
            || $fields->frequency !== null
            || $fields->intFrequency !== null;
        if ($isConversionChanged) {
            $author->reconvertMusic();
        }
    }

    private function checkReferences(AuthorFieldsDto $fields): void
    {
        try {
            if ($fields->countryId !== null && $fields->countryId > 0) {
                $this->entityReferenceResolver->element($fields->countryId, [countryElement::class], 'country');
            }
            if ($fields->cityId !== null && $fields->cityId > 0) {
                $this->entityReferenceResolver->element($fields->cityId, [cityElement::class], 'city');
            }
        } catch (InvalidReferenceException $exception) {
            throw new AuthorDataException($exception->getMessage(), 400, $exception);
        }
    }
}
