<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use structureElement;
use ZxArt\Forms\SubmittedFormValues;
use ZxArt\Groups\Dto\GroupFieldsDto;

/**
 * Reads the fields the group edit and add forms submitted. A field the form
 * submits is passed on even when it is empty and a field it does not submit
 * stays untouched — see {@see SubmittedFormValues}. Dates arrive as the
 * timestamps the form stored.
 *
 * @psalm-api
 */
final readonly class GroupFieldsDtoFactory
{
    /**
     * @param string[] $submittedFields the action's expected fields
     */
    public function fromSubmittedForm(structureElement $form, array $submittedFields): GroupFieldsDto
    {
        $values = new SubmittedFormValues($form, $submittedFields);

        return new GroupFieldsDto(
            title: $values->text('title'),
            abbreviation: $values->text('abbreviation'),
            type: $values->text('type'),
            countryId: $values->number('country'),
            cityId: $values->number('city'),
            website: $values->text('website'),
            wikiLink: $values->text('wikiLink'),
            slogan: $values->text('slogan'),
            startDate: $values->number('startDate'),
            endDate: $values->number('endDate'),
            subGroupIds: $values->ids('subGroupsSelector'),
        );
    }
}
