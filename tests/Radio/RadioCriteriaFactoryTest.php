<?php

declare(strict_types=1);

namespace ZxArt\Tests\Radio;

use App\Users\CurrentUser;
use ConfigManager;
use PHPUnit\Framework\TestCase;
use ZxArt\Radio\Services\RadioCriteriaFactory;

class RadioCriteriaFactoryTest extends TestCase
{
    public function testFromArrayNormalizesLists(): void
    {
        $factory = new RadioCriteriaFactory(
            $this->createMock(ConfigManager::class),
            new class extends CurrentUser {
                public function __construct()
                {
                }

                public function __destruct()
                {
                }
            },
        );

        $criteria = $factory->fromArray(
            [
                'minRating' => '4.5',
                'yearsInclude' => ['2020', '2021', 'bad'],
                'countriesInclude' => ['1', 2, 'bad'],
                'formatGroupsInclude' => ['ay', ' ', 'ts'],
                'formatsExclude' => ['pt3', '', 'asc'],
                'bestVotesLimit' => '100',
                'maxPlays' => '10',
                'minPartyPlace' => '1000',
                'requireGame' => 'true',
                'hasParty' => 'true',
                'notVotedByUserId' => '42',
            ]
        );

        $this->assertSame(4.5, $criteria->minRating);
        $this->assertSame([2020, 2021], $criteria->yearsInclude);
        $this->assertSame([1, 2], $criteria->countriesInclude);
        $this->assertSame(['ay', 'ts'], $criteria->formatGroupsInclude);
        $this->assertSame(['pt3', 'asc'], $criteria->formatsExclude);
        $this->assertSame(100, $criteria->bestVotesLimit);
        $this->assertSame(10, $criteria->maxPlays);
        $this->assertSame(1000, $criteria->minPartyPlace);
        $this->assertTrue($criteria->requireGame);
        $this->assertTrue($criteria->hasParty);
        $this->assertSame(42, $criteria->notVotedByUserId);
    }
}
