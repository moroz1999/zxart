<?php

declare(strict_types=1);

namespace ZxArt\Tests\Radio;

use Cache;
use countryElement;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Radio\Services\RadioOptionsService;
use ZxArt\Tunes\Repositories\TunesRepository;

class RadioOptionsServiceTest extends TestCase
{
    public function testGetOptionsBuildsPayloadFromRepository(): void
    {
        $cache = $this->createMock(Cache::class);
        $cache->method('get')
            ->with('radio_options')
            ->willReturn(null);

        $country = $this->createMock(countryElement::class);
        $country->method('getTitle')->willReturn('Latvia');

        $structureManager = $this->createMock(structureManager::class);
        $structureManager->method('getElementById')
            ->willReturnCallback(function (int $id) use ($country) {
                if ($id === 1) {
                    return $country;
                }
                return null;
            });

        $repository = $this->createMock(TunesRepository::class);
        $repository->method('getYearRange')->willReturn(['min' => 1986, 'max' => 1995]);
        $repository->method('getAvailableFormatGroups')->willReturn(['ay']);
        $repository->method('getAvailableFormats')->willReturn(['pt3']);
        $repository->method('getAuthorCountryIds')->willReturn([1]);

        $cache->expects($this->once())
            ->method('set')
            ->with(
                'radio_options',
                $this->callback(function (array $value): bool {
                    return $value['yearRange'] === ['min' => 1986, 'max' => 1995]
                        && $value['formatGroups'] === ['ay']
                        && $value['formats'] === ['pt3']
                        && $value['countries'] === [['id' => 1, 'title' => 'Latvia']]
                        && $value['partyOptions'] === ['any', 'yes', 'no'];
                }),
                3600
            );

        $service = new RadioOptionsService($cache, $structureManager, $repository);
        $options = $service->getOptions();

        $this->assertSame(['min' => 1986, 'max' => 1995], $options['yearRange']);
        $this->assertSame(['ay'], $options['formatGroups']);
        $this->assertSame(['pt3'], $options['formats']);
        $this->assertSame([['id' => 1, 'title' => 'Latvia']], $options['countries']);
        $this->assertSame(['any', 'yes', 'no'], $options['partyOptions']);
    }
}
