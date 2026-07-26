<?php

declare(strict_types=1);

namespace ZxArt\Tests\Press;

use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Press\Exception\PressDetailsException;
use ZxArt\Press\Services\PressDetailsService;
use ZxArt\Urls\EntityUrlResolver;

final class PressDetailsServiceTest extends TestCase
{
    public function testMissingArticleReturnsNotFoundError(): void
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturn(null);
        $service = new PressDetailsService(
            $structureManager,
            $this->createStub(EntityUrlResolver::class),
        );

        try {
            $service->getDetails(42);
            self::fail('Expected PressDetailsException');
        } catch (PressDetailsException $exception) {
            self::assertSame(404, $exception->getStatusCode());
        }
    }
}
