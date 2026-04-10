<?php

declare(strict_types=1);

namespace ZxArt\AuthorList;

use authorAliasElement;
use authorElement;
use ZxArt\AuthorList\Dto\AuthorListItemDto;

readonly class AuthorListTransformer
{
    public function authorToDto(authorElement $element): AuthorListItemDto
    {
        $countryElement = $element->getCountryElement();
        $cityElement = $element->getCityElement();

        return new AuthorListItemDto(
            id: (int)$element->id,
            url: $element->getUrl(),
            entityType: 'author',
            title: html_entity_decode($element->title, ENT_QUOTES),
            realName: html_entity_decode($element->realName, ENT_QUOTES),
            realNameUrl: null,
            groups: $this->buildGroupsInfo($element->getGroupsList()),
            countryId: $countryElement !== null ? (int)$countryElement->id : null,
            countryTitle: $countryElement !== null ? html_entity_decode($countryElement->title, ENT_QUOTES) : null,
            countryUrl: $countryElement?->getUrl('author'),
            cityId: $cityElement !== null ? (int)$cityElement->id : null,
            cityTitle: $cityElement !== null ? html_entity_decode($cityElement->title, ENT_QUOTES) : null,
            cityUrl: $cityElement?->getUrl('author'),
            musicRating: (float)$element->musicRating,
            graphicsRating: (float)$element->graphicsRating,
        );
    }

    public function aliasToDto(authorAliasElement $alias): AuthorListItemDto
    {
        $parentAuthor = $alias->getAuthorElement();

        $countryElement = $parentAuthor?->getCountryElement();
        $cityElement = $parentAuthor?->getCityElement();
        $groups = $parentAuthor !== null ? $this->buildGroupsInfo($parentAuthor->getGroupsList()) : [];

        return new AuthorListItemDto(
            id: (int)$alias->id,
            url: $alias->getUrl(),
            entityType: 'authorAlias',
            title: html_entity_decode($alias->title, ENT_QUOTES),
            realName: $parentAuthor !== null ? html_entity_decode($parentAuthor->title, ENT_QUOTES) : '',
            realNameUrl: $parentAuthor?->getUrl(),
            groups: $groups,
            countryId: $countryElement !== null ? (int)$countryElement->id : null,
            countryTitle: $countryElement !== null ? html_entity_decode($countryElement->title, ENT_QUOTES) : null,
            countryUrl: $countryElement?->getUrl('author'),
            cityId: $cityElement !== null ? (int)$cityElement->id : null,
            cityTitle: $cityElement !== null ? html_entity_decode($cityElement->title, ENT_QUOTES) : null,
            cityUrl: $cityElement?->getUrl('author'),
            musicRating: $parentAuthor !== null ? (float)$parentAuthor->musicRating : 0.0,
            graphicsRating: $parentAuthor !== null ? (float)$parentAuthor->graphicsRating : 0.0,
        );
    }

    /**
     * @param array $groupElements
     * @return array<array{id: int, title: string, url: string}>
     */
    private function buildGroupsInfo(array $groupElements): array
    {
        $groups = [];
        foreach ($groupElements as $group) {
            $groups[] = [
                'id' => (int)$group->id,
                'title' => html_entity_decode($group->title, ENT_QUOTES),
                'url' => $group->getUrl(),
            ];
        }
        return $groups;
    }
}
