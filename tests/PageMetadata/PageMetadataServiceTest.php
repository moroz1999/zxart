<?php

declare(strict_types=1);

namespace ZxArt\Tests\PageMetadata;

use LanguageLinksService;
use LanguagesManager;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SectionLogics;
use structureManager;
use translationsManager;
use ZxArt\PageMetadata\PageMetadataService;
use ZxArt\Spa\SpaRouter;
use ZxArt\Tags\TagPageService;

final class PageMetadataServiceTest extends TestCase
{
    public function testMissingEntityRouteIsMarkedAsMissingAndNoIndex(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->expects(self::exactly(2))->method('getElementById')->with(589884)->willReturn(null);
        $languagesManager = $this->createStub(LanguagesManager::class);
        $translationsManager = $this->createStub(translationsManager::class);
        $translationsManager->method('getTranslationByName')->willReturn('ZX-Art');
        $service = new PageMetadataService(
            $structureManager,
            $languagesManager,
            $translationsManager,
            $this->createStub(LanguageLinksService::class),
            $this->createStub(SpaRouter::class),
            $this->createTagPageService(),
        );

        $metadata = $service->getForPath('/tune/589884');

        self::assertSame('ZX-Art', $metadata->title);
        self::assertTrue($metadata->noIndex);
    }

    public function testProviderMapToleratesLegacyFalseValues(): void
    {
        $service = new PageMetadataService(
            $this->createStub(structureManager::class),
            $this->createStub(LanguagesManager::class),
            $this->createStub(translationsManager::class),
            $this->createStub(LanguageLinksService::class),
            $this->createStub(SpaRouter::class),
            $this->createTagPageService(),
        );
        $method = new ReflectionMethod($service, 'decodeStringMap');

        self::assertSame(
            ['title' => '', 'description' => 'A & B'],
            $method->invoke($service, ['title' => false, 'description' => 'A &amp; B']),
        );
    }

    private function createTagPageService(): TagPageService
    {
        return new TagPageService(
            $this->createStub(structureManager::class),
            $this->createStub(SectionLogics::class),
            $this->createStub(translationsManager::class),
            $this->createStub(LanguagesManager::class),
        );
    }
}
