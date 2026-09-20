<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxArt\Releases\Services\ArchiveFileResolverService;

/**
 * Which files inside a release archive are offered as releases in their own
 * right, which depends on the machines the release states.
 */
#[CoversClass(ArchiveFileResolverService::class)]
final class ArchiveFileResolverServiceTest extends TestCase
{
    private ArchiveFileResolverService $service;

    protected function setUp(): void
    {
        $this->service = new ArchiveFileResolverService();
    }

    /**
     * A Next title keeps its data in `.bin` files beside the program. The types
     * of every machine in the effective set are pooled, so a Next release that
     * also states a 128K would otherwise take `bin` from the 128K's list and
     * offer a data file as something to run.
     */
    public function testTheNextDoesNotOfferDataBinsAsReleases(): void
    {
        $structure = [
            ['fileName' => 'game.nex', 'parentId' => 0],
            ['fileName' => 'data0.bin', 'parentId' => 0],
        ];

        $names = static fn(array $files): array => array_column($files, 'fileName');

        $this->assertSame(['game.nex'], $names($this->service->filterArchiveFiles($structure, ['zxnext'])));
        $this->assertSame(
            ['game.nex'],
            $names($this->service->filterArchiveFiles($structure, ['zxnext', 'zx128'])),
        );
    }

    /** The exclusion belongs to the Next alone: a 128K release still has its `.bin`. */
    public function testA128kReleaseKeepsItsBin(): void
    {
        $structure = [['fileName' => 'loader.bin', 'parentId' => 0]];

        $this->assertSame(
            ['loader.bin'],
            array_column($this->service->filterArchiveFiles($structure, ['zx128']), 'fileName'),
        );
    }
}
