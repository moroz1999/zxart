<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use structureElement;
use ZxArt\Forms\SubmittedFormValues;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\ReleaseTypes;

/**
 * Reads the release fields the legacy release form submitted. A field the form
 * submits is passed on even when it is empty and a field it does not submit
 * stays untouched — see {@see SubmittedFormValues}.
 *
 * @psalm-api
 */
final readonly class ReleaseFieldsDtoFactory
{
    /**
     * @param string[] $submittedFields the action's expected fields
     */
    public function fromSubmittedForm(structureElement $form, array $submittedFields): ReleaseFieldsDto
    {
        $values = new SubmittedFormValues($form, $submittedFields);
        $releaseType = $values->text('releaseType');
        $prodIds = $values->ids('zxProd');

        return new ReleaseFieldsDto(
            title: $values->text('title'),
            year: $values->number('year'),
            version: $values->text('version'),
            description: $values->text('description'),
            releaseType: $releaseType !== null ? ReleaseTypes::tryFrom($releaseType) : null,
            releaseFormats: $values->codes('releaseFormat'),
            languages: $values->codes('language'),
            hardware: $values->codes('hardwareRequired'),
            publisherIds: $values->ids('publishers'),
            prodId: $prodIds[0] ?? null,
            denyVoting: $values->flag('denyVoting'),
            denyComments: $values->flag('denyComments'),
        );
    }
}
