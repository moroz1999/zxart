<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use structureElement;
use ZxArt\Forms\SubmittedFormValues;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;

/**
 * Reads the fields the group alias edit and add forms submitted. A field the
 * form submits is passed on even when it is empty and a field it does not submit
 * stays untouched — see {@see SubmittedFormValues}. Dates arrive as the
 * timestamps the form stored.
 *
 * @psalm-api
 */
final readonly class GroupAliasFieldsDtoFactory
{
    /**
     * @param string[] $submittedFields the action's expected fields
     */
    public function fromSubmittedForm(structureElement $form, array $submittedFields): GroupAliasFieldsDto
    {
        $values = new SubmittedFormValues($form, $submittedFields);

        return new GroupAliasFieldsDto(
            title: $values->text('title'),
            groupId: $values->number('groupId'),
            startDate: $values->number('startDate'),
            endDate: $values->number('endDate'),
        );
    }
}
