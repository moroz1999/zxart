<?php

declare(strict_types=1);

namespace ZxArt\Tests\Content;

use PHPUnit\Framework\TestCase;
use ZxArt\Content\ContentService;

final class ContentServiceTest extends TestCase
{
    public function testReturnsRequestedBundledPage(): void
    {
        $content = (new ContentService())->getContent('about', 'ru');

        self::assertNotNull($content);
        self::assertNotSame('', trim($content));
    }

    public function testFallsBackToEnglishForUnsupportedLanguage(): void
    {
        $service = new ContentService();

        self::assertSame(
            $service->getContent('about', 'en'),
            $service->getContent('about', 'de'),
        );
    }

    public function testRejectsUnknownPage(): void
    {
        self::assertNull((new ContentService())->getContent('../config', 'en'));
    }
}
