<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Search;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\Search\FindPartiesRequestDto;
use ZxArt\Assistant\Tools\Search\FindPartiesTool;
use ZxArt\Assistant\Tools\Search\FoundPartiesDto;
use ZxArt\Parties\Dbal\PartySearchDbal;
use ZxArt\Parties\Dto\FoundPartyDto;

final class FindPartiesToolTest extends TestCase
{
    public function testItIsNamedFindParties(): void
    {
        $tool = new FindPartiesTool($this->createStub(PartySearchDbal::class));

        self::assertSame('find_parties', $tool->name());
        self::assertSame(FindPartiesRequestDto::class, $tool->requestClass());
    }

    public function testItAnswersThePartiesOfThePhraseAndYear(): void
    {
        $party = new FoundPartyDto(5, 'Chaos Constructions 2008', "CC'08", 2008);
        $dbal = $this->createMock(PartySearchDbal::class);
        $dbal->expects(self::once())->method('find')->with('chaos', 2008, 20)->willReturn([$party]);

        self::assertEquals(
            new FoundPartiesDto([$party]),
            (new FindPartiesTool($dbal))->execute(new FindPartiesRequestDto('chaos', 2008)),
        );
    }

    public function testTheLimitIsKeptBetweenOneAndAHundred(): void
    {
        $dbal = $this->createMock(PartySearchDbal::class);
        $dbal->expects(self::exactly(2))->method('find')
            ->willReturnCallback(static function (string $phrase, ?int $year, int $limit): array {
                self::assertContains($limit, [1, 100]);
                return [];
            });
        $tool = new FindPartiesTool($dbal);

        $tool->execute(new FindPartiesRequestDto('chaos', limit: 0));
        $tool->execute(new FindPartiesRequestDto('chaos', limit: 500));
    }
}
