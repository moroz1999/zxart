<?php

declare(strict_types=1);

namespace ZxArt\Tags;

use LanguagesManager;
use SectionLogics;
use structureElement;
use structureManager;
use tagElement;
use translationsManager;
use ZxArt\PageMetadata\PageMetadataDto;
use ZxArt\Tags\Dto\TagPageDto;

final readonly class TagPageService
{
    public function __construct(
        private structureManager $structureManager,
        private SectionLogics $sectionLogics,
        private translationsManager $translationsManager,
        private LanguagesManager $languagesManager,
    ) {
    }

    public function get(int $tagId, TagSection $section): ?TagPageDto
    {
        if ($tagId <= 0) {
            return null;
        }

        $sectionId = (int)$this->sectionLogics->getSectionIdByType($section->value);
        if ($sectionId <= 0) {
            return null;
        }

        $tag = $this->structureManager->getElementById($tagId, $sectionId);
        $sectionElement = $this->structureManager->getElementById($sectionId);
        if (!$tag instanceof tagElement || !$sectionElement instanceof structureElement) {
            return null;
        }

        $title = $this->decode((string)$tag->getTitle());
        $heading = $this->buildHeading($section, $title);
        $siteName = $this->decode(
            (string)$this->translationsManager->getTranslationByName('site.name', null, false),
        );

        return new TagPageDto(
            id: $tagId,
            section: $section->value,
            title: $title,
            heading: $heading,
            metadata: new PageMetadataDto(
                title: $siteName !== '' ? $heading . ' - ' . $siteName : $heading,
                description: $heading,
                noIndex: false,
                openGraph: [],
                twitter: [],
                languageLinks: [],
                structuredData: null,
            ),
        );
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function buildHeading(TagSection $section, string $tagTitle): string
    {
        $languageCode = (string)$this->languagesManager->getCurrentLanguageCode();
        $itemType = match ($languageCode) {
            'rus' => match ($section) {
                TagSection::Graphics => 'Картинки',
                TagSection::Music => 'Музыка',
                TagSection::Software => 'Программы',
            },
            'spa' => match ($section) {
                TagSection::Graphics => 'Imágenes',
                TagSection::Music => 'Música',
                TagSection::Software => 'Programas',
            },
            default => match ($section) {
                TagSection::Graphics => 'Pictures',
                TagSection::Music => 'Music',
                TagSection::Software => 'Programs',
            },
        };
        $connector = match ($languageCode) {
            'rus' => ' с тегом ',
            'spa' => ' con la etiqueta ',
            default => ' tagged ',
        };

        return $itemType . $connector . '"' . $tagTitle . '"';
    }
}
