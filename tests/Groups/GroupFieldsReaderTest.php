<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

require_once __DIR__ . '/../Doubles/Elements/GroupElementSpy.php';

use PHPUnit\Framework\TestCase;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Services\GroupFieldsReader;
use ZxArt\Tests\Doubles\Elements\GroupElementSpy;

class GroupFieldsReaderTest extends TestCase
{
    public function testReadAnswersEveryEditableFieldDecoded(): void
    {
        $group = new GroupElementSpy(linkedSubGroupIds: [5, 6]);
        $group->values = [
            'title' => 'Rock &amp; Roll',
            'abbreviation' => 'R&amp;R',
            'type' => 'demogroup',
            'country' => '47450',
            'city' => '47451',
            'website' => 'https://group.invalid/',
            'wikiLink' => 'https://wiki.invalid/',
            'slogan' => '&quot;Never&quot; stop',
            'startDate' => 946684800,
            'endDate' => 0,
        ];

        self::assertEquals(new GroupFieldsDto(
            title: 'Rock & Roll',
            abbreviation: 'R&R',
            type: 'demogroup',
            countryId: 47450,
            cityId: 47451,
            website: 'https://group.invalid/',
            wikiLink: 'https://wiki.invalid/',
            slogan: '"Never" stop',
            startDate: 946684800,
            endDate: 0,
            subGroupIds: [5, 6],
        ), (new GroupFieldsReader())->read($group));
    }

    public function testFieldsNeverSetReadAsEmpty(): void
    {
        $fields = (new GroupFieldsReader())->read(new GroupElementSpy());

        self::assertSame('', $fields->type);
        self::assertSame(0, $fields->startDate);
        self::assertSame([], $fields->subGroupIds);
    }
}
