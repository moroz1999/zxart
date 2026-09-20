<?php

declare(strict_types=1);

namespace ZxArt\Tests\Import;

use PHPUnit\Framework\TestCase;
use ZxArt\Import\ZxdbRoleType;

class ZxdbRoleTypeTest extends TestCase
{
    public function testEveryZxdbCodeMapsToAnAuthorshipRole(): void
    {
        $expected = [
            'C' => 'code',
            'D' => 'gamedesign',
            'G' => 'graphics',
            'A' => 'illustrating',
            'V' => 'leveldesign',
            'S' => 'loading_screen',
            'T' => 'localization',
            'M' => 'music',
            'X' => 'sfx',
            'W' => 'story',
        ];

        $actual = [];
        foreach (ZxdbRoleType::cases() as $roleType) {
            $actual[$roleType->value] = $roleType->authorshipRole();
        }

        $this->assertSame($expected, $actual);
    }

    public function testCodeOutsideZxdbRoleTypesIsNotResolved(): void
    {
        $this->assertNull(ZxdbRoleType::tryFrom('Q'));
    }
}
