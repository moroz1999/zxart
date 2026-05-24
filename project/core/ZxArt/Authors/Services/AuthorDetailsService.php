<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use structureManager;
use userElement;
use ZxArt\Authors\Dto\AuthorAliasRefDto;
use ZxArt\Authors\Dto\AuthorCoreDto;
use ZxArt\Authors\Dto\AuthorCountersDto;
use ZxArt\Authors\Dto\AuthorGroupDto;
use ZxArt\Authors\Dto\AuthorLinkDto;
use ZxArt\Authors\Dto\AuthorLocationItemDto;
use ZxArt\Authors\Dto\AuthorRatingsDto;
use ZxArt\Authors\Dto\AuthorTabsDto;
use ZxArt\Authors\Dto\AuthorTechDto;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Shared\EntityType;

readonly class AuthorDetailsService
{
    public function __construct(
        private structureManager $structureManager,
        private AuthorshipRepository $authorshipRepository,
    ) {
    }

    public function getDetails(int $authorId): AuthorCoreDto
    {
        $author = $this->structureManager->getElementById($authorId);
        if (!$author instanceof authorElement) {
            throw new ProdDetailsException('Author not found', 404);
        }

        $userElement = $this->resolveUserElement($author);
        $location = $this->buildLocation($author);
        $groups = $this->buildGroups($author);
        $aliases = $this->buildAliases($author);
        $links = $this->buildLinks($author);
        $roles = $this->buildRoles($author);
        $badges = $this->buildBadges($userElement);
        $tech = $this->buildTech($author);
        $counters = $this->buildCounters($author);
        $ratings = $this->buildRatings($author);
        $tabs = $this->buildTabs($counters);
        [$parentUrl, $parentTitle] = $this->resolveParent($author);

        return new AuthorCoreDto(
            id: $authorId,
            title: $author->getTitle(),
            realName: (string)$author->realName,
            url: (string)$author->getUrl(),
            parentUrl: $parentUrl,
            parentTitle: $parentTitle,
            siteUser: $userElement instanceof userElement ? (string)$userElement->userName : null,
            joined: $userElement instanceof userElement ? $this->formatDate((int)$userElement->dateCreated) : null,
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
        );
    }

    private function resolveUserElement(authorElement $author): ?userElement
    {
        $userId = $author->getUserId();
        if (!$userId) {
            return null;
        }
        $element = $this->structureManager->getElementById($userId);
        return $element instanceof userElement ? $element : null;
    }

    /** @return AuthorLocationItemDto[] */
    private function buildLocation(authorElement $author): array
    {
        $items = [];
        if ($countryElement = $author->getCountryElement()) {
            $items[] = new AuthorLocationItemDto(
                title: (string)$countryElement->title,
                url: (string)$countryElement->getUrl(),
            );
        }
        if ($cityElement = $author->getCityElement()) {
            $items[] = new AuthorLocationItemDto(
                title: (string)$cityElement->title,
                url: (string)$cityElement->getUrl(),
            );
        }
        return $items;
    }

    /** @return AuthorGroupDto[] */
    private function buildGroups(authorElement $author): array
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
    private function buildAliases(authorElement $author): array
    {
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
    private function buildLinks(authorElement $author): array
    {
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
    private function buildRoles(authorElement $author): array
    {
        $roles = [];
        if ((int)$author->displayInGraphics > 0) {
            $roles[] = 'artist';
        }
        if ((int)$author->displayInMusic > 0) {
            $roles[] = 'musician';
        }
        if ($author->getProdsAmount() > 0) {
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

    private function buildTech(authorElement $author): AuthorTechDto
    {
        return new AuthorTechDto(
            palette: $author->getPalette(),
            ayChip: $author->getChipType(),
            ayChannels: $author->getChannelsType(),
            ayClock: (string)$author->getFrequency(),
            intFreq: (string)$author->getIntFrequency(),
        );
    }

    private function buildCounters(authorElement $author): AuthorCountersDto
    {
        return new AuthorCountersDto(
            pictures: (int)$author->picturesQuantity,
            tunes: (int)$author->tunesQuantity,
            prods: $author->getProdsAmount(),
            comments: (int)$author->getCommentsAmount(),
        );
    }

    private function buildRatings(authorElement $author): AuthorRatingsDto
    {
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
    private function resolveParent(authorElement $author): array
    {
        $parent = $author->getFirstParentElement();
        if ($parent === null) {
            return [null, null];
        }
        return [(string)$parent->getUrl(), html_entity_decode((string)$parent->getTitle(), ENT_QUOTES)];
    }

    private function formatDate(int $timestamp): string
    {
        if ($timestamp <= 0) {
            return '';
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
}
