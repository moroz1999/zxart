<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use structureElement;
use ZxArt\Forms\SubmittedFormValues;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\LegalStatus;

/**
 * Reads the production fields a legacy form submitted: the production's own edit
 * form and the batch upload form, which share the field names. A field the form
 * submits is passed on even when it is empty and a field it does not submit
 * stays untouched — see {@see SubmittedFormValues}.
 *
 * @psalm-api
 */
final readonly class ProdFieldsDtoFactory
{
    /**
     * @param string[] $submittedFields the action's expected fields
     */
    public function fromSubmittedForm(
        structureElement $form,
        array $submittedFields,
        string $title,
        string $altTitleField = 'altTitle',
    ): ProdFieldsDto {
        $values = new SubmittedFormValues($form, $submittedFields);
        $legalStatus = $values->text('legalStatus');

        return new ProdFieldsDto(
            title: $title,
            altTitle: $values->text($altTitleField),
            year: $values->number('year'),
            description: $values->text('description'),
            htmlDescription: $values->flag('htmlDescription'),
            instructions: $values->text('instructions'),
            htmlInstructions: $values->flag('htmlInstructions'),
            legalStatus: $legalStatus !== null ? LegalStatus::tryFrom($legalStatus) : null,
            externalLink: $values->text('externalLink'),
            youtubeId: $values->text('youtubeId'),
            partyId: $values->number('party'),
            partyPlace: $values->number('partyplace'),
            compo: $values->text('compo'),
            denyVoting: $values->flag('denyVoting'),
            denyComments: $values->flag('denyComments'),
            languages: $values->codes('language'),
            hardware: $values->codes('hardwareRequired'),
            categoryIds: $values->ids('categories'),
            groupIds: $values->ids('groups'),
            publisherIds: $values->ids('publishers'),
            tags: $values->codes('tags'),
            compilationItemIds: $values->ids('compilationItems'),
            seriesProdIds: $values->ids('seriesProds'),
        );
    }
}
