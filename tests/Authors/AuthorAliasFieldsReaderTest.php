<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

require_once __DIR__ . '/../Doubles/Elements/AuthorAliasElementSpy.php';

use PHPUnit\Framework\TestCase;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Services\AuthorAliasFieldsReader;
use ZxArt\Tests\Doubles\Elements\AuthorAliasElementSpy;

class AuthorAliasFieldsReaderTest extends TestCase
{
    public function testReadAnswersEveryEditableField(): void
    {
        $alias = new AuthorAliasElementSpy();
        $alias->values = [
            'title' => 'R&amp;R',
            'authorId' => '77',
            'startDate' => 946684800,
            'endDate' => 978307200,
            'displayInMusic' => '1',
            'displayInGraphics' => '0',
        ];

        self::assertEquals(new AuthorAliasFieldsDto(
            title: 'R&R',
            authorId: 77,
            startDate: 946684800,
            endDate: 978307200,
            displayInMusic: true,
            displayInGraphics: false,
        ), (new AuthorAliasFieldsReader())->read($alias));
    }

    public function testDatesNeverSetReadAsZero(): void
    {
        $fields = (new AuthorAliasFieldsReader())->read(new AuthorAliasElementSpy());

        self::assertSame(0, $fields->startDate);
        self::assertSame(0, $fields->endDate);
        self::assertSame(0, $fields->authorId);
    }
}
