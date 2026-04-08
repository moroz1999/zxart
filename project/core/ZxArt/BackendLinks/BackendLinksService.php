<?php

declare(strict_types=1);

namespace ZxArt\BackendLinks;

use Cache;
use LanguagesManager;
use structureManager;
use ZxArt\BackendLinks\Rest\BackendLinksRestDto;

readonly class BackendLinksService
{
    private const int CACHE_TTL = 86400;

    public function __construct(
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
        private Cache $cache,
    ) {
        $this->cache->enable(true, true, true);
    }

    public function getLinks(string $languageCode, bool $isAuthenticated): ?BackendLinksRestDto
    {
        $language = $this->languagesManager->checkLanguageCode($languageCode, null);
        if ($language === false) {
            return null;
        }

        $cacheKey = 'backend_links_' . $languageCode . ($isAuthenticated ? '_auth' : '_anon');
        /** @var BackendLinksRestDto|null $cached */
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $languageId = (int)$language->id;

        $dto = new BackendLinksRestDto(
            homeUrl: '/' . $language->iso6393 . '/',
            commentsUrl: $this->getCommentsUrl($languageId),
            supportUrl: $this->getSupportUrl($languageId),
            searchUrl: $this->getSearchUrl($languageId),
            registrationUrl: $this->getRegistrationUrl($languageId),
            passwordReminderUrl: $this->getPasswordReminderUrl($languageId),
            profileUrl: $this->getProfileUrl($languageId),
            playlistsUrl: $this->getPlaylistsUrl($languageId),
            prodCatalogueBaseUrl: $this->getProdCatalogueBaseUrl($languageId),
            graphicsBaseUrl: $this->getCatalogueSearchBaseUrl('graphics', $languageId),
            musicBaseUrl: $this->getCatalogueSearchBaseUrl('music', $languageId),
        );

        $this->cache->set($cacheKey, $dto, self::CACHE_TTL);

        return $dto;
    }

    private function getCommentsUrl(int $languageId): ?string
    {
        $elements = $this->structureManager->getElementsByType('commentsList', $languageId);
        return !empty($elements) ? $elements[0]->getParentUrl() : null;
    }

    private function getSupportUrl(int $languageId): ?string
    {
        $element = $this->structureManager->getElementByMarker('support', $languageId);
        return $element !== null ? (string)$element->getUrl() : null;
    }

    private function getSearchUrl(int $languageId): ?string
    {
        $elements = $this->structureManager->getElementsByType('search', $languageId);
        if (empty($elements)) {
            return null;
        }
        $element = reset($elements);
        return $element->getUrl() . 'action:perform/id:' . $element->id . '/';
    }

    private function getRegistrationUrl(int $languageId): ?string
    {
        $elements = $this->structureManager->getElementsByType('registration', $languageId);
        foreach ($elements as $element) {
            if ($element->type === 'registration') {
                return (string)$element->URL;
            }
        }
        return null;
    }

    private function getPasswordReminderUrl(int $languageId): ?string
    {
        $elements = $this->structureManager->getElementsByType('passwordReminder', $languageId);
        if (!empty($elements)) {
            $element = reset($elements);
            return (string)$element->URL;
        }
        return null;
    }

    private function getProfileUrl(int $languageId): ?string
    {
        $elements = $this->structureManager->getElementsByType('registration', $languageId);
        foreach ($elements as $element) {
            if ($element->type === 'userdata') {
                $parent = $this->structureManager->getElementsFirstParent($element->id);
                return $parent !== null ? (string)$parent->URL : null;
            }
        }
        return null;
    }

    private function getPlaylistsUrl(int $languageId): ?string
    {
        $elements = $this->structureManager->getElementsByType('userPlaylists', $languageId);
        if (!empty($elements)) {
            $element = reset($elements);
            return (string)$element->URL;
        }
        return null;
    }

    private function getProdCatalogueBaseUrl(int $languageId): ?string
    {
        $elements = $this->structureManager->getElementsByType('zxprodcategoriescatalogue', $languageId);
        if (empty($elements)) {
            return null;
        }
        $element = reset($elements);
        if ($element === false) {
            return null;
        }
        $parent = $element->getFirstParentElement();
        return $parent?->getUrl();
    }

    private function getCatalogueSearchBaseUrl(string $items, int $languageId): ?string
    {
        $elements = $this->structureManager->getElementsByType('detailedSearch', $languageId);
        foreach ($elements as $element) {
            if ($element->items === $items) {
                return $element->getUrl();
            }
        }
        return null;
    }
}
