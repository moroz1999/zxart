<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Shared\EntityType;
use zxProdElement;

readonly class ProdsTransformer
{
    public function __construct(
        private HardwareCatalogService $hardwareCatalogService,
    ) {
    }

    public function toDto(zxProdElement $element): ProdDto
    {
        $partyInfo = null;
        $partyPlace = 0;
        $partyElement = $element->getPartyElement();
        if ($partyElement) {
            $partyInfo = [
                'id' => (int)$partyElement->id,
                'structureType' => $partyElement->structureType,
                'title' => html_entity_decode((string)$partyElement->getTitle(), ENT_QUOTES),
            ];
            $partyPlace = (int)$element->partyplace;
        }

        $imageUrls = $element->getImagesUrls('prodListImage');
        if (empty($imageUrls)) {
            $fallback = $element->getImageUrl(0, 'prodListImage');
            if ($fallback) {
                $imageUrls = [(string)$fallback];
            }
        }

        $userVote = $element->getUserVote();

        return new ProdDto(
            id: (int)$element->id,
            structureType: 'zxProd',
            dateCreated: (int)$element->dateAdded,
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            year: $element->year ? (int)$element->year : null,
            listImagesUrls: $imageUrls,
            votes: (float)$element->votes,
            votesAmount: (int)$element->votesAmount,
            userVote: $userVote !== null && $userVote !== false ? (int)$userVote : null,
            denyVoting: $element->isVotingDenied(),
            hardwareInfo: $this->buildHardwareInfo($element),
            authorsInfoShort: $element->getShortAuthorship(EntityType::Prod->value),
            categoriesInfo: $element->getCategoriesInfo(),
            partyInfo: $partyInfo,
            partyPlace: $partyPlace,
            legalStatus: $element->legalStatus ? (string)$element->legalStatus : null,
            languagesInfo: $element->getLanguagesInfo(),
            groupsInfo: $element->getGroupsInfo(),
            publishersInfo: $element->getPublishersInfo(),
            youtubeId: $element->youtubeId ? (string)$element->youtubeId : null,
        );
    }

    /**
     * The production's **own** set, not the aggregate of its releases: a card
     * describes what the production is, which in practice is the set its first
     * release established and the rest share. Aggregating would spell out every
     * variant any release ever needed — a GS soundtrack, a microdrive edition —
     * on every card, which says less about the production rather than more.
     *
     * The aggregate is still what the catalogue *filter* matches on, so a search
     * for a code some release needs finds the production; it just is not printed
     * on it. Same rule on the detail page.
     *
     * Hardware names come from the editable catalog in the request language; the
     * SPA does not own them any more.
     *
     * @return array<array{id: string, name: string, shortName: string, category: string}>
     */
    private function buildHardwareInfo(zxProdElement $element): array
    {
        $labels = $this->hardwareCatalogService->getLabels();

        return array_map(
            function (string $hardwareCode) use ($labels): array {
                $label = $labels[$hardwareCode] ?? null;

                return [
                    'id' => $hardwareCode,
                    'name' => $label['name'] ?? $hardwareCode,
                    'shortName' => $label['shortName'] ?? $hardwareCode,
                    'category' => $this->hardwareCatalogService->getCategoryOf($hardwareCode)?->value ?? '',
                ];
            },
            $element->getHardwareCodes(),
        );
    }
}
