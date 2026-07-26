<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use PHPUnit\Framework\TestCase;
use Symfony\Component\ObjectMapper\ObjectMapper;
use ZxArt\Authors\Dto\AuthorAliasCreatedDto;
use ZxArt\Authors\Dto\AuthorAliasFormAuthorDto;
use ZxArt\Authors\Dto\AuthorAliasFormDto;
use ZxArt\Authors\Rest\AuthorAliasCreatedRestDto;
use ZxArt\Authors\Rest\AuthorAliasFormRestDto;

final class AuthorAliasFormRestMappingTest extends TestCase
{
    public function testMapsFormContextToRestDto(): void
    {
        $mapped = (new ObjectMapper())->map(
            new AuthorAliasFormDto(new AuthorAliasFormAuthorDto(42, 'Author')),
            AuthorAliasFormRestDto::class,
        );

        self::assertInstanceOf(AuthorAliasFormRestDto::class, $mapped);
        self::assertSame(42, $mapped->author->id);
        self::assertSame('Author', $mapped->author->title);
    }

    public function testMapsCreatedAliasToRestDto(): void
    {
        $mapped = (new ObjectMapper())->map(
            new AuthorAliasCreatedDto(73),
            AuthorAliasCreatedRestDto::class,
        );

        self::assertInstanceOf(AuthorAliasCreatedRestDto::class, $mapped);
        self::assertSame(73, $mapped->id);
    }
}
