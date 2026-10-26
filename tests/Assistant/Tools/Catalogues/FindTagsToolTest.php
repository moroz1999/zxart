<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Catalogues;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\Catalogues\FindTagsRequestDto;
use ZxArt\Assistant\Tools\Catalogues\FindTagsTool;
use ZxArt\Assistant\Tools\Catalogues\FoundTagsDto;
use ZxArt\Tags\Dbal\TagSearchDbal;
use ZxArt\Tags\Dto\FoundTagDto;

final class FindTagsToolTest extends TestCase
{
    public function testItIsNamedFindTags(): void
    {
        $tool = new FindTagsTool($this->createStub(TagSearchDbal::class));

        self::assertSame('find_tags', $tool->name());
        self::assertSame(FindTagsRequestDto::class, $tool->requestClass());
    }

    public function testItAnswersTheTagsOfThePhraseWithinTheLimitKeptUpToAHundred(): void
    {
        $tags = [new FoundTagDto(1, 'Games', '', 3000)];
        $dbal = $this->createMock(TagSearchDbal::class);
        $dbal->expects(self::exactly(2))->method('find')
            ->with('game', self::callback(static fn(int $limit): bool => $limit === 5 || $limit === 100))
            ->willReturn($tags);
        $tool = new FindTagsTool($dbal);

        self::assertEquals(new FoundTagsDto($tags), $tool->execute(new FindTagsRequestDto('game', limit: 5)));
        $tool->execute(new FindTagsRequestDto('game', limit: 1000));
    }
}
