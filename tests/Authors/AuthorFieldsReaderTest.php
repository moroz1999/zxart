<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

require_once __DIR__ . '/../Doubles/Elements/AuthorElementSpy.php';

use PHPUnit\Framework\TestCase;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Services\AuthorFieldsReader;
use ZxArt\Tests\Doubles\Elements\AuthorElementSpy;

class AuthorFieldsReaderTest extends TestCase
{
    public function testReadAnswersEveryEditableFieldDecoded(): void
    {
        $author = new AuthorElementSpy();
        $author->values = [
            'title' => 'Rock &amp; Roll',
            'realName' => 'Jan &quot;J&quot; Kowalski',
            'country' => '47450',
            'city' => '47451',
            'wikiLink' => 'https://wiki.invalid/a?b=1&amp;c=2',
            'denyVoting' => '1',
            'denyComments' => '0',
            'deny3a' => '1',
            'displayInMusic' => '1',
            'displayInGraphics' => '0',
            'chipType' => 'ym',
            'channelsType' => 'ABC',
            'frequency' => '1773400',
            'intFrequency' => '50',
            'palette' => 'pulsar',
            'zxTunesId' => '1234',
        ];

        self::assertEquals(new AuthorFieldsDto(
            title: 'Rock & Roll',
            realName: 'Jan "J" Kowalski',
            countryId: 47450,
            cityId: 47451,
            wikiLink: 'https://wiki.invalid/a?b=1&c=2',
            denyVoting: true,
            denyComments: false,
            deny3a: true,
            displayInMusic: true,
            displayInGraphics: false,
            chipType: 'ym',
            channelsType: 'ABC',
            frequency: '1773400',
            intFrequency: '50',
            palette: 'pulsar',
            zxTunesId: 1234,
        ), (new AuthorFieldsReader())->read($author));
    }

    public function testFieldsNeverSetReadAsEmpty(): void
    {
        $fields = (new AuthorFieldsReader())->read(new AuthorElementSpy());

        self::assertSame('', $fields->title);
        self::assertSame(0, $fields->countryId);
        self::assertSame(0, $fields->zxTunesId);
        self::assertFalse($fields->deny3a);
    }
}
