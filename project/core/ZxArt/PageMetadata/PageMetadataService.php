<?php

declare(strict_types=1);

namespace ZxArt\PageMetadata;

use LanguageLinksService;
use LanguagesManager;
use languageElement;
use LdJsonProviderInterface;
use MetadataProviderInterface;
use OpenGraphDataProviderInterface;
use structureElement;
use structureManager;
use translationsManager;
use TwitterDataProviderInterface;
use ZxArt\Shared\StructureType;
use ZxArt\Spa\SpaRouter;

final readonly class PageMetadataService
{
    public function __construct(
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
        private translationsManager $translationsManager,
        private LanguageLinksService $languageLinksService,
        private SpaRouter $spaRouter,
    ) {
    }

    public function getForPath(string $path): PageMetadataDto
    {
        $element = $this->resolveElement($path);
        $siteName = $this->getSiteName();
        if ($element === null) {
            return new PageMetadataDto(
                $siteName,
                '',
                !$this->pathExists($path) || $this->isCurrentLanguageHidden(),
                [],
                [],
                [],
                null,
            );
        }

        $title = $this->getTitle($element);
        if ($siteName !== '') {
            $title = $title !== '' ? $title . ' - ' . $siteName : $siteName;
        }

        return new PageMetadataDto(
            title: $title,
            description: $element instanceof MetadataProviderInterface
                ? $this->decode($element->getMetaDescription())
                : '',
            noIndex: ($element instanceof MetadataProviderInterface
                && (bool)$element->getMetaDenyIndex()) || $this->isCurrentLanguageHidden(),
            openGraph: $element instanceof OpenGraphDataProviderInterface
                ? $this->decodeStringMap($element->getOpenGraphData())
                : [],
            twitter: $element instanceof TwitterDataProviderInterface
                ? $this->decodeStringMap($element->getTwitterData())
                : [],
            languageLinks: $this->languageLinksService->getLanguageLinks($element),
            structuredData: $this->getStructuredData($element),
        );
    }

    public function pathExists(string $path): bool
    {
        $routePath = (string)(parse_url($path, PHP_URL_PATH) ?? '');
        if (preg_match('#^/(?:author|author-alias|group|group-alias|party|prod|release|picture|tune|press)/(\d+)(?:/|$)#', $routePath) === 1) {
            return $this->resolveElement($routePath) !== null;
        }

        return $this->spaRouter->isSpaRequest($routePath);
    }

    private function resolveElement(string $path): ?structureElement
    {
        $path = (string)(parse_url($path, PHP_URL_PATH) ?? '');
        if (preg_match('#^/(author|author-alias|group|group-alias|party|prod|release|picture|tune|press)/(\d+)(?:/|$)#', $path, $matches) !== 1) {
            return $this->resolveSectionElement($path);
        }

        $element = $this->structureManager->getElementById((int)$matches[2]);
        $expectedTypes = match ($matches[1]) {
            'author' => [StructureType::Author, StructureType::AuthorAlias],
            'author-alias' => [StructureType::AuthorAlias],
            'group' => [StructureType::Group, StructureType::GroupAlias],
            'group-alias' => [StructureType::GroupAlias],
            'party' => [StructureType::Party],
            'prod' => [StructureType::ZxProd],
            'release' => [StructureType::ZxRelease],
            'picture' => [StructureType::ZxPicture],
            'tune' => [StructureType::ZxMusic],
            'press' => [StructureType::PressArticle],
        };
        $expectedValues = array_map(
            static fn(StructureType $type): string => $type->value,
            $expectedTypes,
        );
        if (!$element instanceof structureElement || !in_array($element->structureType, $expectedValues, true)) {
            return null;
        }

        return $element;
    }

    private function resolveSectionElement(string $path): ?structureElement
    {
        if ($path === '/') {
            /** @var languageElement|null $languageElement */
            $languageElement = $this->languagesManager->getCurrentLanguageElement();
            $firstPageElement = $languageElement instanceof languageElement
                ? $languageElement->getFirstPageElement()
                : null;
            return $firstPageElement instanceof structureElement ? $firstPageElement : null;
        }

        $structureType = match (true) {
            str_starts_with($path, '/authors') => StructureType::AuthorsCatalogue,
            str_starts_with($path, '/groups') => StructureType::GroupsCatalogue,
            str_starts_with($path, '/parties') => StructureType::PartiesCatalogue,
            str_starts_with($path, '/prods') => StructureType::ZxProdCategoriesCatalogue,
            str_starts_with($path, '/pictures') => StructureType::PicturesCatalogue,
            str_starts_with($path, '/music') => StructureType::MusicCatalogue,
            str_starts_with($path, '/geo') => StructureType::CountriesList,
            str_starts_with($path, '/stats') => StructureType::Stats,
            str_starts_with($path, '/comments') => StructureType::CommentsList,
            str_starts_with($path, '/feedback') => StructureType::Feedback,
            str_starts_with($path, '/register') => StructureType::Registration,
            str_starts_with($path, '/playlists') => StructureType::UserPlaylists,
            default => null,
        };
        if ($structureType === null) {
            return null;
        }

        $elements = $this->structureManager->getElementsByType(
            $structureType->value,
            (int)$this->languagesManager->getCurrentLanguageId(),
            [],
            1,
        );
        $element = reset($elements);
        return $element instanceof structureElement ? $element : null;
    }

    private function getTitle(structureElement $element): string
    {
        if ($element instanceof MetadataProviderInterface) {
            return $this->decode((string)$element->getMetaTitle());
        }

        return $this->decode((string)$element->getTitle());
    }

    private function getSiteName(): string
    {
        return $this->decode((string)$this->translationsManager->getTranslationByName('site.name', null, false));
    }

    private function isCurrentLanguageHidden(): bool
    {
        $language = $this->languagesManager->getCurrentLanguage();
        return is_object($language) && (bool)($language->hidden ?? false);
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * @param array<string, string> $values
     * @return array<string, string>
     */
    private function decodeStringMap(array $values): array
    {
        $result = [];
        foreach ($values as $key => $value) {
            $result[$key] = $this->decode($value);
        }

        return $result;
    }

    /** @return array<array-key, mixed>|null */
    private function getStructuredData(structureElement $element): ?array
    {
        if (!$element instanceof LdJsonProviderInterface) {
            return null;
        }

        return $element->getLdJsonScriptData();
    }
}
