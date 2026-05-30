<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use authorElement;
use authorAliasElement;
use DesignTheme;
use DesignThemesManager;
use groupAliasElement;
use groupElement;
use partyElement;
use structureElement;
use translationsManager;
use ZxArt\Prods\Dto\ProdAuthorInfoDto;
use ZxArt\Prods\Dto\ProdGroupRefDto;
use ZxArt\Prods\Dto\ProdHardwareInfoDto;
use ZxArt\Prods\Dto\ProdLanguageInfoDto;
use ZxArt\Prods\Dto\ProdLinkInfoDto;
use ZxArt\Prods\Dto\ProdPartyInfoDto;
use ZxArt\Shared\EntityType;
use zxProdElement;
use zxReleaseElement;

/**
 * Shared builders for sub-DTOs that appear on both zxProd and zxRelease pages
 * (party, languages, hardware, external links). Keeps `ProdCoreService` and
 * `ProdReleasesService` free of duplication.
 */
readonly class ProdInfoBuilder
{
    public function __construct(
        private translationsManager $translationsManager,
        private DesignThemesManager $designThemesManager,
    ) {
    }

    public function resolveCurrentTheme(): ?DesignTheme
    {
        $theme = $this->designThemesManager->getCurrentTheme();
        return $theme instanceof DesignTheme ? $theme : null;
    }

    public function buildParty(zxProdElement|zxReleaseElement $element): ?ProdPartyInfoDto
    {
        $party = $element->getPartyElement();
        if (!$party instanceof partyElement) {
            return null;
        }

        $compoLabel = null;
        $compo = $element->compo;
        if ($compo !== '') {
            $compoLabel = $this->translate('party.compo_' . $compo);
        }

        return new ProdPartyInfoDto(
            id: $party->getId(),
            title: $this->decodeText($party->title),
            abbreviation: $party->abbreviation !== '' ? $this->decodeText($party->abbreviation) : null,
            url: (string)$party->getUrl(),
            place: $element->getPartyPlace(),
            compoLabel: $compoLabel,
        );
    }

    /**
     * @return ProdLanguageInfoDto[]
     */
    public function buildLanguages(zxProdElement|zxReleaseElement $element): array
    {
        $languages = [];
        /**
         * @var array<string, string> $map
         */
        $map = $element->getSupportedLanguagesMap();
        foreach ($map as $code => $title) {
            $languages[] = new ProdLanguageInfoDto(
                code: $code,
                title: $title,
                emoji: $element->getLanguageEmoji($code),
                catalogueUrl: $element->getCatalogueUrl(['languages' => $code]),
            );
        }
        return $languages;
    }

    /**
     * @return ProdHardwareInfoDto[]
     */
    public function buildHardware(zxProdElement|zxReleaseElement $element): array
    {
        $hardware = [];
        /**
         * @var list<array{id: string, title: string}> $rows
         */
        $rows = $element->getHardwareInfo();
        foreach ($rows as $row) {
            $hardware[] = new ProdHardwareInfoDto(
                id: $row['id'],
                catalogueUrl: $element->getCatalogueUrl(['hw' => $row['id']]),
            );
        }
        return $hardware;
    }

    /**
     * @return ProdLinkInfoDto[]
     */
    public function buildLinks(zxProdElement|zxReleaseElement $element, ?DesignTheme $theme): array
    {
        $links = [];
        /**
         * @var list<array{url: string, name: string, image: string, type?: string, id?: string}> $rows
         */
        $rows = $element->getLinksInfo();
        foreach ($rows as $linkInfo) {
            $imageFile = $linkInfo['image'];
            $imageUrl = '';
            if ($imageFile !== '' && $theme !== null) {
                $imageUrl = $theme->getImageUrl($imageFile);
            }
            $links[] = new ProdLinkInfoDto(
                url: $linkInfo['url'],
                name: $linkInfo['name'],
                image: $imageUrl,
            );
        }
        return $links;
    }

    /**
     * @return ProdAuthorInfoDto[]
     */
    public function buildReleaseAuthors(zxReleaseElement $release): array
    {
        $authors = [];
        /**
         * @var list<array{authorElement: structureElement, roles?: list<string>}> $records
         */
        $records = $release->getAuthorsInfo(EntityType::Release->value);
        foreach ($records as $info) {
            $authorElement = $info['authorElement'];
            if (!$authorElement instanceof authorElement && !$authorElement instanceof authorAliasElement) {
                continue;
            }
            $roles = $info['roles'] ?? [];
            $authors[] = new ProdAuthorInfoDto(
                id: $authorElement->getId(),
                title: $this->decodeText($authorElement->title),
                url: (string)$authorElement->getUrl(),
                roles: $roles,
            );
        }
        return $authors;
    }

    /**
     * @return ProdGroupRefDto[]
     */
    public function buildReleaseBy(zxReleaseElement $release): array
    {
        return $this->buildPublisherRefs($release->getReleaseBy());
    }

    /**
     * @return ProdGroupRefDto[]
     */
    public function buildReleasePublishers(zxReleaseElement $release): array
    {
        return $this->buildPublisherRefs($release->publishers);
    }

    /**
     * @param iterable<mixed> $publishers
     * @return ProdGroupRefDto[]
     */
    private function buildPublisherRefs(iterable $publishers): array
    {
        $refs = [];
        foreach ($publishers as $publisher) {
            if (
                !$publisher instanceof groupElement
                && !$publisher instanceof groupAliasElement
                && !$publisher instanceof authorElement
                && !$publisher instanceof authorAliasElement
            ) {
                continue;
            }
            $refs[] = new ProdGroupRefDto(
                id: $publisher->getId(),
                title: $this->decodeText($publisher->title),
                url: (string)$publisher->getUrl(),
            );
        }
        return $refs;
    }

    public function decodeText(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function translate(string $key): string
    {
        return (string)$this->translationsManager->getTranslationByName($key);
    }
}
