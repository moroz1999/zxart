<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use DesignTheme;
use DesignThemesManager;
use partyElement;
use translationsManager;
use ZxArt\Prods\Dto\ProdHardwareInfoDto;
use ZxArt\Prods\Dto\ProdLanguageInfoDto;
use ZxArt\Prods\Dto\ProdLinkInfoDto;
use ZxArt\Prods\Dto\ProdPartyInfoDto;
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

        $partyplace = $element->partyplace;

        return new ProdPartyInfoDto(
            id: $party->getId(),
            title: $this->decodeText($party->title),
            abbreviation: $party->abbreviation !== '' ? $this->decodeText($party->abbreviation) : null,
            url: (string)$party->getUrl(),
            place: $partyplace > 0 ? $partyplace : null,
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
    public function buildHardware(zxProdElement|zxReleaseElement $element, bool $shortHardwareTitles = false): array
    {
        $hardware = [];
        /**
         * @var list<array{id: string, title: string}> $rows
         */
        $rows = $element->getHardwareInfo($shortHardwareTitles);
        foreach ($rows as $row) {
            $hardware[] = new ProdHardwareInfoDto(
                id: $row['id'],
                title: $row['title'],
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

    public function decodeText(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function translate(string $key): string
    {
        return (string)$this->translationsManager->getTranslationByName($key);
    }
}
