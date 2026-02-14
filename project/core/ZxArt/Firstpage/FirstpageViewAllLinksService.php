<?php
declare(strict_types=1);

namespace ZxArt\Firstpage;

use Cache;
use LanguagesManager;
use structureManager;

final readonly class FirstpageViewAllLinksService
{
    private const int CACHE_TTL = 86400;

    public function __construct(
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
        private Cache $cache,
    )
    {
        $this->cache->enable(true, true, true);
    }

    /**
     * @return array{prodCatalogueBaseUrl: string|null, graphicsBaseUrl: string|null, musicBaseUrl: string|null}
     */
    public function getCatalogueBaseUrls(): array
    {
        /** @var string $lang */
        $lang = $this->languagesManager->getCurrentLanguageCode();
        $cacheKey = 'firstpage_base_urls_' . $lang;

        /** @var array{prodCatalogueBaseUrl: string|null, graphicsBaseUrl: string|null, musicBaseUrl: string|null}|null $cached */
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $result = [
            'prodCatalogueBaseUrl' => $this->getProdCatalogueBaseUrl(),
            'graphicsBaseUrl' => $this->getDetailedSearchBaseUrl('graphics'),
            'musicBaseUrl' => $this->getDetailedSearchBaseUrl('music'),
        ];

        $this->cache->set($cacheKey, $result, self::CACHE_TTL);

        return $result;
    }

    private function getProdCatalogueBaseUrl(): ?string
    {
        $currentLanguageId = $this->languagesManager->getCurrentLanguageId();
        $catalogueElements = $this->structureManager->getElementsByType('zxprodcategoriescatalogue', $currentLanguageId);
        if ($catalogueElements === []) {
            return null;
        }
        $catalogueElement = reset($catalogueElements);
        if ($catalogueElement === false) {
            return null;
        }
        $parent = $catalogueElement->getFirstParentElement();
        if ($parent === null) {
            return null;
        }
        return $parent->getUrl();
    }

    private function getDetailedSearchBaseUrl(string $items): ?string
    {
        $currentLanguageId = $this->languagesManager->getCurrentLanguageId();
        $searchElements = $this->structureManager->getElementsByType('detailedSearch', $currentLanguageId);
        if ($searchElements === []) {
            return null;
        }
        foreach ($searchElements as $searchElement) {
            if ($searchElement->items === $items) {
                return $searchElement->getUrl();
            }
        }
        return null;
    }
}
