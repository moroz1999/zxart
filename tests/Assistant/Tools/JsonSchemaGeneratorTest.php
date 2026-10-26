<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools;

require_once __DIR__ . '/Fixtures/EmptyRequestDto.php';
require_once __DIR__ . '/Fixtures/SampleKind.php';
require_once __DIR__ . '/Fixtures/SampleItemDto.php';
require_once __DIR__ . '/Fixtures/SampleRequestDto.php';
require_once __DIR__ . '/Fixtures/UntypedRequestDto.php';

use LogicException;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\JsonSchemaGenerator;
use ZxArt\Tests\Assistant\Tools\Fixtures\EmptyRequestDto;
use ZxArt\Tests\Assistant\Tools\Fixtures\SampleRequestDto;
use ZxArt\Tests\Assistant\Tools\Fixtures\UntypedRequestDto;

final class JsonSchemaGeneratorTest extends TestCase
{
    public function testARequestDtoBecomesAnObjectSchema(): void
    {
        self::assertSame([
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'description' => 'the title to set'],
                'year' => ['type' => ['integer', 'null'], 'description' => 'the year it came out'],
                'categoryIds' => [
                    'type' => ['array', 'null'],
                    'items' => ['type' => 'integer'],
                    'description' => 'the categories, replacing the current ones',
                ],
                'kind' => ['type' => ['string', 'null'], 'enum' => ['game', 'demo', null]],
                'html' => ['type' => 'boolean'],
                'rating' => ['type' => 'number'],
                'credits' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'authorId' => ['type' => 'integer', 'description' => 'the author to credit'],
                            'roles' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => "the author's roles"],
                        ],
                        'required' => ['authorId', 'roles'],
                        'additionalProperties' => false,
                    ],
                ],
                'kinds' => ['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['game', 'demo']]],
            ],
            'required' => ['title'],
            'additionalProperties' => false,
        ], (new JsonSchemaGenerator())->forClass(SampleRequestDto::class));
    }

    public function testARequestWithoutFieldsHasAnEmptyObjectOfProperties(): void
    {
        $schema = (new JsonSchemaGenerator())->forClass(EmptyRequestDto::class);

        self::assertSame('{"type":"object","properties":{},"required":[],"additionalProperties":false}', json_encode($schema, JSON_THROW_ON_ERROR));
    }

    public function testAPropertyWithoutAUsableTypeIsRefused(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('anything');

        (new JsonSchemaGenerator())->forClass(UntypedRequestDto::class);
    }
}
