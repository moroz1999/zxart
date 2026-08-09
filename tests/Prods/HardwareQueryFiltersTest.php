<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use DI\Container;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Prods\Repositories\ProdHardwareRepository;
use zxProdHardwareQueryFilter;
use zxReleaseEffectiveHardwareQueryFilter;
use zxReleaseHardwareQueryFilter;

/**
 * The hardware filters.
 *
 * What matters here is the contract between the layers: the URL and the public
 * API speak **codes**, the link tables store **ids**, so a filter that forgot to
 * resolve them would silently match nothing. Each filter also has to target the
 * right table for the type it declares.
 */
#[AllowMockObjectsWithoutExpectations]
class HardwareQueryFiltersTest extends TestCase
{
    private const array CODE_TO_ID = ['zx48' => 1, 'samcoupe' => 26];

    public static function setUpBeforeClass(): void
    {
        // query filters are not autoloaded; the CMS includes them on demand
        require_once ROOT_PATH . 'trickster-cms/cms/core/QueryFilter.php';
        foreach (['zxProdHardware', 'zxReleaseEffectiveHardware', 'zxReleaseHardware'] as $filter) {
            require_once ROOT_PATH . 'project/modules/queryFilters/' . $filter . '.class.php';
        }
    }

    public function testProdFilterResolvesCodesAndTargetsTheProdColumn(): void
    {
        $repository = $this->createMock(ProdHardwareRepository::class);
        $query = $this->createMock(Builder::class);

        $repository->expects($this->once())
            ->method('addProdHardwareFilter')
            ->with($query, 'module_zxprod.id', [1, 26])
            ->willReturn($query);

        $filter = new zxProdHardwareQueryFilter();
        $filter->setContainer($this->makeContainer($repository));

        $this->assertSame('zxProd', $filter->getRequiredType());
        $this->assertSame($query, $filter->getFilteredIdList(['zx48', 'samcoupe'], $query));
    }

    public function testEffectiveReleaseFilterResolvesCodesAndTargetsTheReleaseColumn(): void
    {
        $repository = $this->createMock(ProdHardwareRepository::class);
        $query = $this->createMock(Builder::class);

        $repository->expects($this->once())
            ->method('addReleaseHardwareFilter')
            ->with($query, 'module_zxrelease.id', [1])
            ->willReturn($query);

        $filter = new zxReleaseEffectiveHardwareQueryFilter();
        $filter->setContainer($this->makeContainer($repository));

        $this->assertSame('zxRelease', $filter->getRequiredType());
        $this->assertSame($query, $filter->getFilteredIdList(['zx48'], $query));
    }

    public function testUnknownCodesResolveToNoIdsRatherThanBeingPassedThrough(): void
    {
        $repository = $this->createMock(ProdHardwareRepository::class);
        $query = $this->createMock(Builder::class);

        $repository->expects($this->once())
            ->method('addProdHardwareFilter')
            ->with($query, 'module_zxprod.id', [1])
            ->willReturn($query);

        $filter = new zxProdHardwareQueryFilter();
        $filter->setContainer($this->makeContainer($repository));

        $filter->getFilteredIdList(['zx48', 'not_a_code'], $query);
    }

    /**
     * The documented public API filter keeps its literal meaning — releases
     * carrying the code themselves — and must not start matching inherited ones.
     */
    public function testTheApiReleaseFilterStaysOwnCodesOnly(): void
    {
        $repository = $this->createMock(ProdHardwareRepository::class);
        $repository->expects($this->never())->method('addReleaseHardwareFilter');

        $query = $this->createMock(Builder::class);
        $query->method('whereIn')->willReturnSelf();

        $filter = new zxReleaseHardwareQueryFilter();
        $filter->setContainer($this->makeContainer($repository));

        $this->assertSame('zxRelease', $filter->getRequiredType());
        $this->assertSame($query, $filter->getFilteredIdList(['zx48'], $query));
    }

    private function makeContainer(ProdHardwareRepository&MockObject $repository): Container&MockObject
    {
        $catalog = $this->createMock(HardwareCatalogService::class);
        $catalog->method('getIdsByCodes')->willReturnCallback(
            static fn(array $codes): array => array_values(array_filter(array_map(
                static fn(string $code): ?int => self::CODE_TO_ID[$code] ?? null,
                $codes,
            ))),
        );

        $container = $this->createMock(Container::class);
        $container->method('get')->willReturnCallback(
            static fn(string $id): object => match ($id) {
                HardwareCatalogService::class => $catalog,
                ProdHardwareRepository::class => $repository,
                default => throw new \RuntimeException('unexpected service ' . $id),
            },
        );

        return $container;
    }
}
