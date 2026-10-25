<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Search;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\JsonSchemaGenerator;
use ZxArt\Assistant\Tools\Search\SearchAuthorsRequestDto;
use ZxArt\Assistant\Tools\Search\SearchGroupsRequestDto;
use ZxArt\Assistant\Tools\Search\SearchProdsRequestDto;
use ZxArt\Assistant\Tools\Search\SearchReleasesRequestDto;

/**
 * The typed searches hand the model their filter DTOs as they are, so every
 * field of them must have a schema.
 */
final class SearchToolSchemasTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function requests(): iterable
    {
        yield 'prods' => [SearchProdsRequestDto::class];
        yield 'releases' => [SearchReleasesRequestDto::class];
        yield 'authors' => [SearchAuthorsRequestDto::class];
        yield 'groups' => [SearchGroupsRequestDto::class];
    }

    /**
     * @param class-string $requestClass
     */
    #[DataProvider('requests')]
    public function testTheFilterIsARequiredObjectBesideTheOptionalPage(string $requestClass): void
    {
        $schema = (new JsonSchemaGenerator())->forClass($requestClass);

        self::assertSame(['filter'], $schema['required']);
        self::assertSame('object', $schema['properties']['filter']['type']);
        self::assertSame(['filter', 'limit', 'offset'], array_keys($schema['properties']));
    }

    public function testAnEnumOfTheFilterIsListedByItsValues(): void
    {
        $filter = (new JsonSchemaGenerator())->forClass(SearchAuthorsRequestDto::class)['properties']['filter'];

        self::assertSame(['made', 'published', null], $filter['properties']['prodLink']['enum']);
        self::assertSame(['object', 'null'], $filter['properties']['prods']['type']);
    }
}
