<?php

declare(strict_types=1);

namespace ZxArt\Tests\Search;

use PHPUnit\Framework\TestCase;
use ZxArt\Search\Dto\TypeCountDto;
use ZxArt\Search\Dto\TypeSliceDto;
use ZxArt\Search\Services\PageWindow;
use ZxArt\Search\Services\PhraseSearchType;

/**
 * A page of the paged search is a window into one list: the results of the
 * types one after another, in the order the types are given.
 */
final class PageWindowTest extends TestCase
{
    public function testAWindowInsideOneTypeTakesOnlyFromIt(): void
    {
        self::assertEquals(
            [new TypeSliceDto(PhraseSearchType::Author, 1, 2)],
            $this->cut(1, 2),
        );
    }

    public function testAWindowAcrossTheBorderTakesTheEndOfOneTypeAndTheStartOfTheNext(): void
    {
        self::assertEquals(
            [new TypeSliceDto(PhraseSearchType::Author, 2, 1), new TypeSliceDto(PhraseSearchType::Group, 0, 3)],
            $this->cut(2, 4),
        );
    }

    public function testAWindowCanSpanAWholeTypeAndGoOnToTheNext(): void
    {
        self::assertEquals(
            [new TypeSliceDto(PhraseSearchType::Group, 0, 5), new TypeSliceDto(PhraseSearchType::ZxProd, 0, 2)],
            $this->cut(3, 10),
        );
    }

    public function testAWindowPastTheEndIsEmpty(): void
    {
        self::assertSame([], $this->cut(10, 5));
    }

    /**
     * Three authors, five groups, two productions.
     *
     * @return TypeSliceDto[]
     */
    private function cut(int $offset, int $limit): array
    {
        return (new PageWindow())->slices($offset, $limit, [
            new TypeCountDto(PhraseSearchType::Author, 3),
            new TypeCountDto(PhraseSearchType::Group, 5),
            new TypeCountDto(PhraseSearchType::ZxProd, 2),
        ]);
    }
}
