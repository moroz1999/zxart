<?php

declare(strict_types=1);

namespace ZxArt\Tests\Tags;

require_once __DIR__ . '/../Doubles/Elements/TagElementStub.php';

use LanguagesManager;
use PHPUnit\Framework\TestCase;
use SectionLogics;
use structureElement;
use structureManager;
use translationsManager;
use ZxArt\Tags\TagPageService;
use ZxArt\Tags\TagSection;
use ZxArt\Tests\Doubles\Elements\TagElementStub;

final class TagPageServiceTest extends TestCase
{
    public function testBuildsSectionHeadingAndMetadataForLocalizedTag(): void
    {
        $tag = new TagElementStub(42, 'Metallica &amp; Co');
        $sectionElement = $this->createStub(structureElement::class);
        $sectionElement->method('getTitle')->willReturn('Music');

        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturnCallback(
            static fn(int $id, ?int $parentId = null): ?structureElement => match ([$id, $parentId]) {
                [42, 100] => $tag,
                [100, null] => $sectionElement,
                default => null,
            },
        );
        $sectionLogics = $this->createStub(SectionLogics::class);
        $sectionLogics->method('getSectionIdByType')->willReturn(100);
        $translationsManager = $this->createStub(translationsManager::class);
        $translationsManager->method('getTranslationByName')->willReturn('ZX-Art');
        $languagesManager = $this->createStub(LanguagesManager::class);
        $languagesManager->method('getCurrentLanguageCode')->willReturn('rus');

        $result = (new TagPageService(
            $structureManager,
            $sectionLogics,
            $translationsManager,
            $languagesManager,
        ))->get(42, TagSection::Music);

        self::assertNotNull($result);
        self::assertSame('Metallica & Co', $result->title);
        self::assertSame('Музыка с тегом "Metallica & Co"', $result->heading);
        self::assertSame('Музыка с тегом "Metallica & Co" - ZX-Art', $result->metadata->title);
        self::assertSame('Музыка с тегом "Metallica & Co"', $result->metadata->description);
    }

    public function testReturnsNullWhenTagIsNotAvailableInSection(): void
    {
        $sectionLogics = $this->createStub(SectionLogics::class);
        $sectionLogics->method('getSectionIdByType')->willReturn(100);

        $service = new TagPageService(
            $this->createStub(structureManager::class),
            $sectionLogics,
            $this->createStub(translationsManager::class),
            $this->createStub(LanguagesManager::class),
        );

        self::assertNull($service->get(42, TagSection::Graphics));
    }
}
