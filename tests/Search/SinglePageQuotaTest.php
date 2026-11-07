<?php

declare(strict_types=1);

namespace ZxArt\Tests\Search;

use PHPUnit\Framework\TestCase;
use ZxArt\Search\Dto\TypeCountDto;
use ZxArt\Search\Dto\TypeSliceDto;
use ZxArt\Search\Services\PhraseSearchType;
use ZxArt\Search\Services\SinglePageQuota;

/**
 * One page of every type found: the limit is shared out evenly, and what a
 * type with fewer results leaves goes to the others.
 */
final class SinglePageQuotaTest extends TestCase
{
    public function testEveryTypeFitsWholeUnderTheLimit(): void
    {
        self::assertEquals(
            [$this->slice(PhraseSearchType::Author, 3), $this->slice(PhraseSearchType::ZxProd, 4)],
            $this->split(10, [PhraseSearchType::Author->value => 3, PhraseSearchType::ZxProd->value => 4]),
        );
    }

    public function testTheLimitIsSharedEvenly(): void
    {
        self::assertEquals(
            [$this->slice(PhraseSearchType::Author, 5), $this->slice(PhraseSearchType::ZxProd, 5)],
            $this->split(10, [PhraseSearchType::Author->value => 20, PhraseSearchType::ZxProd->value => 20]),
        );
    }

    public function testWhatAShortTypeLeavesGoesToTheOthers(): void
    {
        self::assertEquals(
            [
                $this->slice(PhraseSearchType::Author, 2),
                $this->slice(PhraseSearchType::Group, 4),
                $this->slice(PhraseSearchType::ZxProd, 4),
            ],
            $this->split(10, [PhraseSearchType::Author->value => 2, PhraseSearchType::Group->value => 20, PhraseSearchType::ZxProd->value => 20]),
        );
    }

    public function testTheRemainderGoesToTheTypesThatComeFirst(): void
    {
        self::assertEquals(
            [
                $this->slice(PhraseSearchType::Author, 4),
                $this->slice(PhraseSearchType::Group, 3),
                $this->slice(PhraseSearchType::ZxProd, 3),
            ],
            $this->split(10, [PhraseSearchType::Author->value => 20, PhraseSearchType::Group->value => 20, PhraseSearchType::ZxProd->value => 20]),
        );
    }

    public function testATypeWithNothingFoundIsLeftOut(): void
    {
        self::assertEquals(
            [$this->slice(PhraseSearchType::ZxProd, 10)],
            $this->split(10, [PhraseSearchType::Author->value => 0, PhraseSearchType::ZxProd->value => 20]),
        );
    }

    public function testNoTypesGiveNoSlices(): void
    {
        self::assertSame([], $this->split(10, []));
    }

    /**
     * @param array<string, int> $counts
     * @return TypeSliceDto[]
     */
    private function split(int $limit, array $counts): array
    {
        $typeCounts = [];
        foreach ($counts as $type => $count) {
            $typeCounts[] = new TypeCountDto(PhraseSearchType::from($type), $count);
        }

        return (new SinglePageQuota())->slices($limit, $typeCounts);
    }

    private function slice(PhraseSearchType $type, int $limit): TypeSliceDto
    {
        return new TypeSliceDto($type, 0, $limit);
    }
}
