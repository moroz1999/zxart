<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Entities;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\Entities\GetAuthorAliasTool;
use ZxArt\Assistant\Tools\Entities\GetAuthorTool;
use ZxArt\Assistant\Tools\Entities\GetEntityRequestDto;
use ZxArt\Assistant\Tools\Entities\GetGroupAliasTool;
use ZxArt\Assistant\Tools\Entities\GetGroupTool;
use ZxArt\Assistant\Tools\Entities\GetProdTool;
use ZxArt\Assistant\Tools\Entities\GetReleaseTool;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Authors\Dto\AuthorAliasCardDto;
use ZxArt\Authors\Dto\AuthorCardDto;
use ZxArt\Authors\Services\AuthorAliasCardReader;
use ZxArt\Authors\Services\AuthorCardReader;
use ZxArt\Groups\Dto\GroupAliasCardDto;
use ZxArt\Groups\Dto\GroupCardDto;
use ZxArt\Groups\Services\GroupAliasCardReader;
use ZxArt\Groups\Services\GroupCardReader;
use ZxArt\Prods\Dto\ProdCardDto;
use ZxArt\Prods\Services\ProdCardReader;
use ZxArt\Releases\Dto\ReleaseCardDto;
use ZxArt\Releases\Services\ReleaseCardReader;

final class GetEntityToolsTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string, class-string, class-string, string}>
     */
    public static function tools(): iterable
    {
        yield 'prod' => [GetProdTool::class, ProdCardReader::class, ProdCardDto::class, 'get_prod'];
        yield 'release' => [GetReleaseTool::class, ReleaseCardReader::class, ReleaseCardDto::class, 'get_release'];
        yield 'author' => [GetAuthorTool::class, AuthorCardReader::class, AuthorCardDto::class, 'get_author'];
        yield 'author alias' => [GetAuthorAliasTool::class, AuthorAliasCardReader::class, AuthorAliasCardDto::class, 'get_author_alias'];
        yield 'group' => [GetGroupTool::class, GroupCardReader::class, GroupCardDto::class, 'get_group'];
        yield 'group alias' => [GetGroupAliasTool::class, GroupAliasCardReader::class, GroupAliasCardDto::class, 'get_group_alias'];
    }

    /**
     * @param class-string $toolClass
     * @param class-string $readerClass
     * @param class-string $cardClass
     */
    #[DataProvider('tools')]
    public function testTheToolAnswersTheCardOfTheId(string $toolClass, string $readerClass, string $cardClass, string $name): void
    {
        $card = $this->createStub($cardClass);
        $reader = $this->createMock($readerClass);
        $reader->expects(self::once())->method('read')->with(42)->willReturn($card);
        $tool = new $toolClass($reader);

        self::assertInstanceOf(ReadTool::class, $tool);
        self::assertSame($name, $tool->name());
        self::assertSame(GetEntityRequestDto::class, $tool->requestClass());
        self::assertSame($card, $tool->execute(new GetEntityRequestDto(42)));
    }
}
