<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared;

use PHPUnit\Framework\TestCase;
use ZxArt\Shared\SortDirection;
use ZxArt\Shared\SortingParams;

final class SortingParamsTest extends TestCase
{
    private array $allowed = ['title', 'graphicsRating', 'musicRating', 'id'];

    public function testValidColumnAndDirectionParsed(): void
    {
        $p = SortingParams::fromRequest('title,asc', $this->allowed);
        $this->assertSame('title', $p->column);
        $this->assertSame(SortDirection::ASC, $p->direction);
    }

    public function testDescDirectionParsed(): void
    {
        $p = SortingParams::fromRequest('id,desc', $this->allowed);
        $this->assertSame(SortDirection::DESC, $p->direction);
    }

    public function testInvalidColumnFallsBackToDefault(): void
    {
        $p = SortingParams::fromRequest('unknown,asc', $this->allowed);
        $this->assertSame('title', $p->column);
    }

    public function testCustomDefaultColumnUsed(): void
    {
        $p = SortingParams::fromRequest('invalid,asc', $this->allowed, 'id');
        $this->assertSame('id', $p->column);
    }

    public function testMissingDirectionDefaultsToAsc(): void
    {
        $p = SortingParams::fromRequest('title', $this->allowed);
        $this->assertSame(SortDirection::ASC, $p->direction);
    }

    public function testInvalidDirectionFallsBackToDefault(): void
    {
        $p = SortingParams::fromRequest('title,sideways', $this->allowed);
        $this->assertSame(SortDirection::ASC, $p->direction);
    }

    public function testCustomDefaultDirectionUsed(): void
    {
        $p = SortingParams::fromRequest('title,invalid', $this->allowed, 'title', SortDirection::DESC);
        $this->assertSame(SortDirection::DESC, $p->direction);
    }

    public function testDateAliasRemappedToDateAdded(): void
    {
        $p = SortingParams::fromRequest('date,asc', ['date']);
        $this->assertSame('dateAdded', $p->column);
    }

    public function testDirectionIsCaseInsensitive(): void
    {
        $p = SortingParams::fromRequest('title,DESC', $this->allowed);
        $this->assertSame(SortDirection::DESC, $p->direction);
    }

    public function testWhitespaceAroundColumnAndDirectionTrimmed(): void
    {
        $p = SortingParams::fromRequest(' title , asc ', $this->allowed);
        $this->assertSame('title', $p->column);
        $this->assertSame(SortDirection::ASC, $p->direction);
    }
}
