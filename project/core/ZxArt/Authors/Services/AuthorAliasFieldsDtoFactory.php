<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use structureElement;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Forms\SubmittedFormValues;

/**
 * Reads the fields the author alias edit and add forms submitted. A field the
 * form submits is passed on even when it is empty and a field it does not submit
 * stays untouched — see {@see SubmittedFormValues}. Dates arrive as the
 * timestamps the form stored.
 *
 * @psalm-api
 */
final readonly class AuthorAliasFieldsDtoFactory
{
    /**
     * @param string[] $submittedFields the action's expected fields
     */
    public function fromSubmittedForm(structureElement $form, array $submittedFields): AuthorAliasFieldsDto
    {
        $values = new SubmittedFormValues($form, $submittedFields);

        return new AuthorAliasFieldsDto(
            title: $values->text('title'),
            authorId: $values->number('authorId'),
            startDate: $values->number('startDate'),
            endDate: $values->number('endDate'),
            displayInMusic: $values->flag('displayInMusic'),
            displayInGraphics: $values->flag('displayInGraphics'),
        );
    }
}
