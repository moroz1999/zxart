<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools;

require_once __DIR__ . '/Fixtures/SampleKind.php';
require_once __DIR__ . '/Fixtures/SampleItemDto.php';
require_once __DIR__ . '/Fixtures/SampleRequestDto.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\ToolArgumentsException;
use ZxArt\Assistant\Tools\ToolRequestBuilder;
use ZxArt\Shared\Serializer\RequestDenormalizerFactory;
use ZxArt\Tests\Assistant\Tools\Fixtures\SampleItemDto;
use ZxArt\Tests\Assistant\Tools\Fixtures\SampleKind;
use ZxArt\Tests\Assistant\Tools\Fixtures\SampleRequestDto;

final class ToolRequestBuilderTest extends TestCase
{
    public function testTheArgumentsBuildTheRequestDto(): void
    {
        self::assertEquals(
            new SampleRequestDto(
                title: 'Rock & Roll',
                kind: SampleKind::Demo,
                credits: [new SampleItemDto(authorId: 5, roles: ['code'])],
            ),
            $this->builder()->build(
                SampleRequestDto::class,
                '{"title": "Rock & Roll", "kind": "demo", "credits": [{"authorId": 5, "roles": ["code"]}]}',
            ),
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function malformedArguments(): array
    {
        return [
            'a required field is missing' => ['{"year": 1999}', 'title'],
            'a field of the wrong type' => ['{"title": "Demo", "year": "nineteen"}', 'year'],
            'a value outside the enum' => ['{"title": "Demo", "kind": "music"}', 'kind'],
            'a field the request does not have' => ['{"title": "Demo", "colour": "red"}', 'colour'],
            'not JSON' => ['{"title": ', 'JSON'],
            'not an object' => ['[1, 2]', 'object'],
        ];
    }

    #[DataProvider('malformedArguments')]
    public function testMalformedArgumentsAreRefusedNamingTheField(string $arguments, string $named): void
    {
        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage($named);

        $this->builder()->build(SampleRequestDto::class, $arguments);
    }

    private function builder(): ToolRequestBuilder
    {
        return new ToolRequestBuilder(RequestDenormalizerFactory::create());
    }
}
