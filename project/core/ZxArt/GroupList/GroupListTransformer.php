<?php

declare(strict_types=1);

namespace ZxArt\GroupList;

use groupAliasElement;
use groupElement;
use ZxArt\GroupList\Dto\GroupListItemDto;
use ZxArt\Shared\EntityType;

readonly class GroupListTransformer
{
    public function __construct(
        private \ZxArt\Urls\EntityUrlResolver $entityUrlResolver,
    ) {
    }

    private function resolveGroupType(?groupElement $group): string
    {
        if ($group === null) {
            return 'unknown';
        }
        return $group->type ?: 'unknown';
    }

    public function groupToDto(groupElement $element): GroupListItemDto
    {
        $countryElement = $element->getCountryElement();
        $cityElement = $element->getCityElement();

        return new GroupListItemDto(
            id: (int)$element->id,
            url: $this->entityUrlResolver->urlFor($element),
            entityType: EntityType::Group,
            title: html_entity_decode($element->title, ENT_QUOTES),
            groupType: $element->type ?: 'unknown',
            realGroupTitle: null,
            realGroupUrl: null,
            countryId: $countryElement !== null ? (int)$countryElement->id : null,
            countryTitle: $countryElement !== null ? html_entity_decode($countryElement->title, ENT_QUOTES) : null,
            countryUrl: $countryElement !== null ? $this->entityUrlResolver->urlFor($countryElement) : null,
            cityId: $cityElement !== null ? (int)$cityElement->id : null,
            cityTitle: $cityElement !== null ? html_entity_decode($cityElement->title, ENT_QUOTES) : null,
            cityUrl: $cityElement !== null ? $this->entityUrlResolver->urlFor($cityElement) : null,
        );
    }

    public function aliasToDto(groupAliasElement $alias): GroupListItemDto
    {
        /** @var groupElement|null $parentGroup */
        $parentGroup = $alias->getGroupElement();

        $countryElement = $parentGroup?->getCountryElement();
        $cityElement = $parentGroup?->getCityElement();

        return new GroupListItemDto(
            id: (int)$alias->id,
            url: $this->entityUrlResolver->urlFor($alias),
            entityType: EntityType::GroupAlias,
            title: html_entity_decode($alias->title, ENT_QUOTES),
            groupType: $this->resolveGroupType($parentGroup),
            realGroupTitle: $parentGroup !== null ? html_entity_decode($parentGroup->title, ENT_QUOTES) : null,
            realGroupUrl: $parentGroup !== null ? $this->entityUrlResolver->urlFor($parentGroup) : null,
            countryId: $countryElement !== null ? (int)$countryElement->id : null,
            countryTitle: $countryElement !== null ? html_entity_decode($countryElement->title, ENT_QUOTES) : null,
            countryUrl: $countryElement !== null ? $this->entityUrlResolver->urlFor($countryElement) : null,
            cityId: $cityElement !== null ? (int)$cityElement->id : null,
            cityTitle: $cityElement !== null ? html_entity_decode($cityElement->title, ENT_QUOTES) : null,
            cityUrl: $cityElement !== null ? $this->entityUrlResolver->urlFor($cityElement) : null,
        );
    }
}
