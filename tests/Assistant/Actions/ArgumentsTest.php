<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Actions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;

final class ArgumentsTest extends TestCase
{
    private const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "id": {"type": "integer"},
                "title": {"type": "string"},
                "countryId": {"type": "integer"},
                "asAlias": {"type": "boolean"},
                "subGroupIds": {"type": "array", "items": {"type": "integer"}},
                "roles": {"type": "array", "items": {"type": "string"}}
            },
            "required": ["id"],
            "additionalProperties": false
        }
        JSON;

    private const string PRODS = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "title": {"type": "string"},
                "year": {"type": "integer"}
            },
            "required": ["title"],
            "additionalProperties": false
        }
        JSON;

    private const string NESTED = '{"type": "object", "properties": {"title": {"type": "string"}, "prods": '
        . self::PRODS . '}, "additionalProperties": false}';

    public function testTheValuesAreReadByTheirTypes(): void
    {
        $arguments = Arguments::parse('{"id": 5, "title": "Rock & Roll", "countryId": 0, "asAlias": true, "subGroupIds": [7, 8], "roles": ["coder", "musician"]}', self::SCHEMA);

        self::assertSame(5, $arguments->id('id'));
        self::assertSame('Rock & Roll', $arguments->text('title'));
        self::assertSame(0, $arguments->number('countryId'));
        self::assertTrue($arguments->flag('asAlias'));
        self::assertSame([7, 8], $arguments->ids('subGroupIds'));
        self::assertSame(['coder', 'musician'], $arguments->texts('roles'));
    }

    public function testAnOptionalValueNotGivenIsNullAndAFlagNotGivenIsFalse(): void
    {
        $arguments = Arguments::parse('{"id": 5}', self::SCHEMA);

        self::assertNull($arguments->text('title'));
        self::assertNull($arguments->number('countryId'));
        self::assertFalse($arguments->flag('asAlias'));
        self::assertNull($arguments->ids('subGroupIds'));
        self::assertNull($arguments->texts('roles'));
    }

    public function testAValueOfAClosedListIsReadWhenItIsOneOfIt(): void
    {
        $arguments = Arguments::parse('{"id": 5, "title": "scene"}', self::SCHEMA);

        self::assertSame('scene', $arguments->oneOf('title', ['company', 'scene']));
        self::assertNull(Arguments::parse('{"id": 5}', self::SCHEMA)->oneOf('title', ['scene']));
    }

    public function testAValueOutsideTheClosedListIsRefusedNamingTheFieldAndTheValues(): void
    {
        $arguments = Arguments::parse('{"id": 5, "title": "band"}', self::SCHEMA);

        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage('The field title must be one of: company, scene');

        $arguments->oneOf('title', ['company', 'scene']);
    }

    public function testTextsOfAClosedListAreReadWhenEachIsOneOfIt(): void
    {
        $arguments = Arguments::parse('{"id": 5, "roles": ["coder", "musician"]}', self::SCHEMA);

        self::assertSame(['coder', 'musician'], $arguments->textsOf('roles', ['coder', 'graphician', 'musician']));
        self::assertNull(Arguments::parse('{"id": 5}', self::SCHEMA)->textsOf('roles', ['coder']));
    }

    public function testATextOutsideTheClosedListIsRefusedNamingTheFieldAndTheValues(): void
    {
        $arguments = Arguments::parse('{"id": 5, "roles": ["coder", "drummer"]}', self::SCHEMA);

        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage('The field roles must hold only: coder, musician');

        $arguments->textsOf('roles', ['coder', 'musician']);
    }

    public function testANestedObjectIsReadAsArgumentsOfItsOwnSchema(): void
    {
        $arguments = Arguments::parse('{"title": "Elite", "prods": {"title": "Elite", "year": 1985}}', self::NESTED);

        $prods = $arguments->object('prods', self::PRODS);

        self::assertNotNull($prods);
        self::assertSame('Elite', $prods->text('title'));
        self::assertSame(1985, $prods->number('year'));
        self::assertNull(Arguments::parse('{"title": "Elite"}', self::NESTED)->object('prods', self::PRODS));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function malformedNestedObjects(): array
    {
        return [
            'not an object' => ['{"prods": [1, 2]}', 'The field prods must be an object'],
            'a field its schema does not have' => ['{"prods": {"title": "Elite", "addedFrom": 0}}', 'Unknown fields: prods.addedFrom'],
            'a required field is missing' => ['{"prods": {"year": 1985}}', 'Missing fields: prods.title'],
        ];
    }

    #[DataProvider('malformedNestedObjects')]
    public function testAMalformedNestedObjectIsRefusedNamingItsField(string $json, string $message): void
    {
        $arguments = Arguments::parse($json, self::NESTED);

        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage($message);

        $arguments->object('prods', self::PRODS);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function malformedArguments(): array
    {
        return [
            'not JSON' => ['{"id": ', 'JSON'],
            'not an object' => ['[1, 2]', 'object'],
            'a required field is missing' => ['{"title": "Demo"}', 'id'],
            'a field the schema does not have' => ['{"id": 5, "fields": {"title": "Demo"}}', 'fields'],
        ];
    }

    #[DataProvider('malformedArguments')]
    public function testMalformedArgumentsAreRefusedNamingTheField(string $json, string $named): void
    {
        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage($named);

        Arguments::parse($json, self::SCHEMA);
    }

    /**
     * @return array<string, array{string, callable(Arguments): mixed}>
     */
    public static function mistypedValues(): array
    {
        return [
            'an id as a string' => ['{"id": "5"}', static fn(Arguments $arguments): int => $arguments->id('id')],
            'a text as a number' => ['{"id": 5, "title": 1999}', static fn(Arguments $arguments): ?string => $arguments->text('title')],
            'a number as a string' => ['{"id": 5, "countryId": "47450"}', static fn(Arguments $arguments): ?int => $arguments->number('countryId')],
            'a flag as a number' => ['{"id": 5, "asAlias": 1}', static fn(Arguments $arguments): bool => $arguments->flag('asAlias')],
            'ids as a number' => ['{"id": 5, "subGroupIds": 7}', static fn(Arguments $arguments): ?array => $arguments->ids('subGroupIds')],
            'ids holding a string' => ['{"id": 5, "subGroupIds": [7, "8"]}', static fn(Arguments $arguments): ?array => $arguments->ids('subGroupIds')],
            'texts holding a number' => ['{"id": 5, "roles": ["coder", 1]}', static fn(Arguments $arguments): ?array => $arguments->texts('roles')],
        ];
    }

    /**
     * @param callable(Arguments): mixed $read
     */
    #[DataProvider('mistypedValues')]
    public function testAValueOfTheWrongTypeIsRefusedNamingTheField(string $json, callable $read): void
    {
        $arguments = Arguments::parse($json, self::SCHEMA);
        $named = array_key_last((array)json_decode($json, true));

        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage((string)$named);

        $read($arguments);
    }
}
