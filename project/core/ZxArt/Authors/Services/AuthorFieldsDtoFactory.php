<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use structureElement;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Forms\SubmittedFormValues;

/**
 * Reads the fields the author edit and add forms submitted. A field the form
 * submits is passed on even when it is empty and a field it does not submit
 * stays untouched — see {@see SubmittedFormValues}. The real name, which the
 * form submits per language, is left on the element.
 *
 * @psalm-api
 */
final readonly class AuthorFieldsDtoFactory
{
    /**
     * @param string[] $submittedFields the action's expected fields
     */
    public function fromSubmittedForm(structureElement $form, array $submittedFields): AuthorFieldsDto
    {
        $values = new SubmittedFormValues($form, $submittedFields);

        return new AuthorFieldsDto(
            title: $values->text('title'),
            // per language: the form has already put each language's value on
            // the element, which persists it
            realName: null,
            countryId: $values->number('country'),
            cityId: $values->number('city'),
            wikiLink: $values->text('wikiLink'),
            denyVoting: $values->flag('denyVoting'),
            denyComments: $values->flag('denyComments'),
            deny3a: $values->flag('deny3a'),
            displayInMusic: $values->flag('displayInMusic'),
            displayInGraphics: $values->flag('displayInGraphics'),
            chipType: $values->text('chipType'),
            channelsType: $values->text('channelsType'),
            frequency: $values->text('frequency'),
            intFrequency: $values->text('intFrequency'),
            palette: $values->text('palette'),
            zxTunesId: $values->number('zxTunesId'),
        );
    }
}
