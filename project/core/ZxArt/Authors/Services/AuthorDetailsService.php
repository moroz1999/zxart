<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use breadcrumbsManager;
use groupAliasElement;
use groupElement;
use structureManager;
use userElement;
use ZxArt\Authors\Dto\AuthorAliasRefDto;
use ZxArt\Authors\Dto\AuthorBreadcrumbDto;
use ZxArt\Authors\Dto\AuthorCoreDto;
use ZxArt\Authors\Dto\AuthorCountersDto;
use ZxArt\Authors\Dto\AuthorGroupDto;
use ZxArt\Authors\Dto\AuthorLinkDto;
use ZxArt\Authors\Dto\AuthorLocationDto;
use ZxArt\Authors\Dto\AuthorLocationItemDto;
use ZxArt\Authors\Dto\AuthorRatingsDto;
use ZxArt\Authors\Dto\AuthorTabsDto;
use ZxArt\Authors\Dto\AuthorTechDto;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\LinkTypes;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Shared\EntityType;

readonly class AuthorDetailsService
{
    public function __construct(
        private structureManager $structureManager,
        private breadcrumbsManager $breadcrumbsManager,
        private AuthorshipRepository $authorshipRepository,
    ) {
    }

    public function getDetails(int $authorId): AuthorCoreDto
    {
        $author = $this->structureManager->getElementById($authorId);
        if (!($author instanceof authorElement) && !($author instanceof authorAliasElement)) {
            throw new ProdDetailsException('Author or alias not found', 404);
        }

        $profileAuthor = $this->resolveProfileAuthor($author);
        $userElement = $this->resolveUserElement($profileAuthor);
        $location = $this->buildLocation($profileAuthor);
        $groups = $this->buildGroups($author);
        $aliases = $this->buildAliases($profileAuthor);
        $links = $this->buildLinks($profileAuthor);
        $badges = $this->buildBadges($userElement);
        $counters = $this->buildCounters($author);
        $roles = $this->buildRoles($author, $counters);
        $tech = $this->buildTech($profileAuthor);
        $ratings = $this->buildRatings($profileAuthor);
        $tabs = $this->buildTabs($counters);
        [$parentUrl, $parentTitle] = $this->resolveParent($author);
        $breadcrumbs = $this->buildBreadcrumbs($author);

        return new AuthorCoreDto(
            id: $authorId,
            entityType: ($author instanceof authorElement ? EntityType::Author : EntityType::AuthorAlias)->value,
            title: $this->resolveTitle($author),
            realName: $profileAuthor instanceof authorElement ? (string)$profileAuthor->realName : '',
            url: (string)$author->getUrl(),
            parentUrl: $parentUrl,
            parentTitle: $parentTitle,
            primaryAuthor: $this->buildPrimaryAuthor($author, $profileAuthor),
            siteUser: $userElement instanceof userElement ? (string)$userElement->userName : null,
            joined: $userElement instanceof userElement ? $this->formatDate($userElement->getCreatedTimestamp()) : null,
            location: $location,
            roles: $roles,
            badges: $badges,
            groups: $groups,
            aliases: $aliases,
            links: $links,
            tech: $tech,
            counters: $counters,
            ratings: $ratings,
            tabs: $tabs,
            breadcrumbs: $breadcrumbs,
        );
    }

    private function resolveProfileAuthor(authorElement|authorAliasElement $author): ?authorElement
    {
        return $author instanceof authorElement ? $author : $author->getAuthorElement();
    }

    private function resolveTitle(authorElement|authorAliasElement $author): string
    {
        $title = $author instanceof authorElement ? (string)$author->getTitle() : (string)$author->title;

        return html_entity_decode($title, ENT_QUOTES);
    }

    private function resolveUserElement(?authorElement $author): ?userElement
    {
        if (!$author instanceof authorElement) {
            return null;
        }
        $userId = $author->getUserId();
        if (!$userId) {
            return null;
        }
        $element = $this->structureManager->getElementById($userId, null, true);
        return $element instanceof userElement ? $element : null;
    }

    private function buildPrimaryAuthor(
        authorElement|authorAliasElement $author,
        ?authorElement $profileAuthor,
    ): ?AuthorAliasRefDto
    {
        if ($author instanceof authorElement || !($profileAuthor instanceof authorElement)) {
            return null;
        }

        return new AuthorAliasRefDto(
            id: (int)$profileAuthor->id,
            title: html_entity_decode((string)$profileAuthor->getTitle(), ENT_QUOTES),
            url: (string)$profileAuthor->getUrl(),
        );
    }

    private function buildLocation(?authorElement $author): AuthorLocationDto
    {
        if (!$author instanceof authorElement) {
            return new AuthorLocationDto(city: null, country: null);
        }
        $city = null;
        if ($cityElement = $author->getCityElement()) {
            $city = new AuthorLocationItemDto(
                title: (string)$cityElement->title,
                url: (string)$cityElement->getUrl(),
            );
        }
        $country = null;
        if ($countryElement = $author->getCountryElement()) {
            $country = new AuthorLocationItemDto(
                title: (string)$countryElement->title,
                url: (string)$countryElement->getUrl(),
            );
        }
        return new AuthorLocationDto(city: $city, country: $country);
    }

    /** @return AuthorGroupDto[] */
    private function buildGroups(authorElement|authorAliasElement $author): array
    {
        $records = $this->authorshipRepository->getAuthorshipRecords($author->getId(), EntityType::Group);
        if (empty($records)) {
            return [];
        }

        $groups = [];
        foreach ($records as $record) {
            $element = $this->structureManager->getElementById($record['elementId']);
            if (!($element instanceof groupElement) && !($element instanceof groupAliasElement)) {
                continue;
            }
            $years = $this->formatYearsRange($record['startDate'], $record['endDate']);
            $groups[] = new AuthorGroupDto(
                id: (int)$element->id,
                title: html_entity_decode((string)$element->title, ENT_QUOTES),
                url: (string)$element->getUrl(),
                years: $years,
            );
        }
        return $groups;
    }

    /** @return AuthorAliasRefDto[] */
    private function buildAliases(?authorElement $author): array
    {
        if (!$author instanceof authorElement) {
            return [];
        }
        $aliases = [];
        foreach ($author->getAliasElements() as $alias) {
            if (!$alias instanceof authorAliasElement) {
                continue;
            }
            $aliases[] = new AuthorAliasRefDto(
                id: (int)$alias->id,
                title: html_entity_decode((string)$alias->title, ENT_QUOTES),
                url: (string)$alias->getUrl(),
            );
        }
        return $aliases;
    }

    /** @return AuthorLinkDto[] */
    private function buildLinks(?authorElement $author): array
    {
        if (!$author instanceof authorElement) {
            return [];
        }
        $links = [];
        $site = (string)$author->site;
        if ($site !== '') {
            $links[] = new AuthorLinkDto(url: $site, label: $this->extractDomain($site));
        }
        $wikiSlug = (string)$author->wikiLink;
        if ($wikiSlug !== '') {
            $links[] = new AuthorLinkDto(url: 'https://speccy.info/' . $wikiSlug, label: 'Speccy.info');
        }
        $zxTunesId = (int)$author->zxTunesId;
        if ($zxTunesId > 0) {
            $links[] = new AuthorLinkDto(
                url: 'https://zxtunes.com/author.php?id=' . $zxTunesId,
                label: 'ZXTunes',
            );
        }
        return $links;
    }

    /** @return string[] */
    private function buildRoles(authorElement|authorAliasElement $author, AuthorCountersDto $counters): array
    {
        $roles = [];
        $hasPictures = $author instanceof authorElement ? (int)$author->displayInGraphics > 0 : $counters->pictures > 0;
        if ($hasPictures) {
            $roles[] = 'artist';
        }
        $hasTunes = $author instanceof authorElement ? (int)$author->displayInMusic > 0 : $counters->tunes > 0;
        if ($hasTunes) {
            $roles[] = 'musician';
        }
        if ($counters->prods > 0) {
            $roles[] = 'coder';
        }
        return $roles;
    }

    /** @return string[] */
    private function buildBadges(?userElement $userElement): array
    {
        if (!$userElement instanceof userElement) {
            return [];
        }
        $badges = [];
        if ((bool)$userElement->vip) {
            $badges[] = 'VIP';
        }
        if ((bool)$userElement->volunteer) {
            $badges[] = 'Volunteer';
        }
        return $badges;
    }

    private function buildTech(?authorElement $author): AuthorTechDto
    {
        if (!$author instanceof authorElement) {
            return new AuthorTechDto(
                palette: '',
                ayChip: '',
                ayChannels: '',
                ayClock: '',
                intFreq: '',
            );
        }
        return new AuthorTechDto(
            palette: $author->getPalette(),
            ayChip: $author->getChipType(),
            ayChannels: $author->getChannelsType(),
            ayClock: (string)$author->getFrequency(),
            intFreq: (string)$author->getIntFrequency(),
        );
    }

    private function buildCounters(authorElement|authorAliasElement $author): AuthorCountersDto
    {
        if ($author instanceof authorElement) {
            return new AuthorCountersDto(
                pictures: (int)$author->picturesQuantity,
                tunes: (int)$author->tunesQuantity,
                prods: $author->getProdsAmount(),
                comments: (int)$author->getCommentsAmount(),
            );
        }

        return new AuthorCountersDto(
            pictures: count($author->getWorksList([LinkTypes::AUTHOR_PICTURE->value])),
            tunes: count($author->getWorksList([LinkTypes::AUTHOR_MUSIC->value])),
            prods: $author->getProdsAmount(),
            comments: (int)$author->getCommentsAmount(),
        );
    }

    private function buildRatings(?authorElement $author): AuthorRatingsDto
    {
        if (!$author instanceof authorElement) {
            return new AuthorRatingsDto(artist: 0.0, musician: 0.0);
        }
        return new AuthorRatingsDto(
            artist: (float)$author->graphicsRating,
            musician: (float)$author->musicRating,
        );
    }

    private function buildTabs(AuthorCountersDto $counters): AuthorTabsDto
    {
        return new AuthorTabsDto(
            hasPictures: $counters->pictures > 0,
            hasTunes: $counters->tunes > 0,
            hasProds: $counters->prods > 0,
        );
    }

    /** @return array{0: ?string, 1: ?string} */
    private function resolveParent(authorElement|authorAliasElement $author): array
    {
        $parent = $author->getFirstParentElement();
        if ($parent === null) {
            return [null, null];
        }
        return [(string)$parent->getUrl(), html_entity_decode((string)$parent->getTitle(), ENT_QUOTES)];
    }

    private function formatDate(int $timestamp): ?string
    {
        if ($timestamp <= 0) {
            return null;
        }
        return date('Y-m-d', $timestamp);
    }

    private function formatYearsRange(string $startDate, string $endDate): ?string
    {
        if ($startDate === '' && $endDate === '') {
            return null;
        }
        $startYear = $startDate !== '' ? substr($startDate, 6, 4) : '';
        $endYear = $endDate !== '' ? substr($endDate, 6, 4) : '';
        if ($startYear === $endYear) {
            return $startYear;
        }
        if ($startYear !== '' && $endYear !== '') {
            return $startYear . '–' . $endYear;
        }
        return $startYear !== '' ? $startYear . '–' : '–' . $endYear;
    }

    private function extractDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        return $host !== false && $host !== null ? (string)$host : $url;
    }

    /** @return AuthorBreadcrumbDto[] */
    private function buildBreadcrumbs(authorElement|authorAliasElement $author): array
    {
        $authorUrl = (string)$author->getUrl();
        $path = trim(parse_url($authorUrl, PHP_URL_PATH) ?? '', '/');
        if ($path === '') {
            return [];
        }
        $segments = array_values(array_filter(explode('/', $path)));
        $raw = $this->breadcrumbsManager->getBreadcrumbsForPath($segments);

        // Drop first item (language element = home, handled by the home link in zx-breadcrumbs)
        // and last item (the author itself, shown as currentTitle in zx-breadcrumbs)
        $ancestors = array_slice($raw, 1, -1);

        $breadcrumbs = [];
        foreach ($ancestors as $item) {
            $breadcrumbs[] = new AuthorBreadcrumbDto(
                title: html_entity_decode((string)$item['title'], ENT_QUOTES),
                url: (string)$item['URL'],
            );
        }
        return $breadcrumbs;
    }
}
