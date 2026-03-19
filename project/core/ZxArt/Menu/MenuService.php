<?php

declare(strict_types=1);

namespace ZxArt\Menu;

use Cache;
use LanguagesManager;
use structureManager;
use ZxArt\Menu\Rest\MenuRestItemDto;

readonly class MenuService
{
    public function __construct(
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
        private Cache $cache,
    ) {}

    /** @return MenuRestItemDto[] */
    public function getMenuItems(?string $languageCode = null): array
    {
        $this->cache->enable(true, true, true);
        $code = $languageCode ?? $this->languagesManager->getCurrentLanguageCode();
        $cacheKey = 'menu:' . $code;

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $items = $this->buildMenuItems($code);
        if (!empty($items)) {
            $this->cache->set($cacheKey, $items, 3600);
        }
        return $items;
    }

    /** @return MenuRestItemDto[] */
    private function buildMenuItems(string $languageCode): array
    {
        $language = $this->languagesManager->checkLanguageCode($languageCode, null);
        if ($language === false) {
            return [];
        }
        $languageId = $language->id;

        $languageElement = $this->structureManager->getElementById($languageId);
        if ($languageElement === null) {
            return [];
        }

        $items = [];
        foreach ($this->structureManager->getElementsChildren($languageElement->id, 'container', null, null, true) as $child) {
            if ($child->hidden || $child->structureType === 'search') {
                continue;
            }

            $children = [];
            foreach ($this->structureManager->getElementsChildren($child->id, 'container', null, null, true) as $subChild) {
                if ($subChild->hidden || $subChild->structureType === 'search') {
                    continue;
                }
                $children[] = new MenuRestItemDto(
                    id: (int)$subChild->id,
                    title: (string)$subChild->title,
                    url: (string)$subChild->URL,
                );
            }

            $items[] = new MenuRestItemDto(
                id: (int)$child->id,
                title: (string)$child->title,
                url: (string)$child->URL,
                children: $children,
            );
        }

        return $items;
    }
}
