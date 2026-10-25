<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

require_once __DIR__ . '/../Doubles/Elements/GroupAliasElementSpy.php';

use PHPUnit\Framework\TestCase;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Services\GroupAliasFieldsReader;
use ZxArt\Tests\Doubles\Elements\GroupAliasElementSpy;

class GroupAliasFieldsReaderTest extends TestCase
{
    public function testReadAnswersEveryEditableField(): void
    {
        $alias = new GroupAliasElementSpy();
        $alias->values = [
            'title' => 'R&amp;R',
            'groupId' => '88',
            'startDate' => 946684800,
            'endDate' => 978307200,
        ];

        self::assertEquals(new GroupAliasFieldsDto(
            title: 'R&R',
            groupId: 88,
            startDate: 946684800,
            endDate: 978307200,
        ), (new GroupAliasFieldsReader())->read($alias));
    }

    public function testFieldsNeverSetReadAsEmpty(): void
    {
        $fields = (new GroupAliasFieldsReader())->read(new GroupAliasElementSpy());

        self::assertSame('', $fields->title);
        self::assertSame(0, $fields->groupId);
        self::assertSame(0, $fields->endDate);
    }
}
