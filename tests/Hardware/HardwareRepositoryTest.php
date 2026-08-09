<?php

declare(strict_types=1);

namespace ZxArt\Tests\Hardware;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\Hardware\Repositories\HardwareRepository;

/**
 * The repository's job is to hand the service typed rows.
 *
 * Query results arrive as plain arrays of strings (PDO::FETCH_ASSOC), so these
 * tests pin the casting: a service that received `'12'` where it expects `12`
 * would fail much later, in a strict comparison somewhere else entirely.
 */
#[AllowMockObjectsWithoutExpectations]
class HardwareRepositoryTest extends TestCase
{
    public function testCatalogRowsComeBackTyped(): void
    {
        $repository = $this->createRepository([
            'hardware' => [
                ['id' => '1', 'code' => 'zx48', 'category' => 'computers', 'position' => '0'],
                ['id' => '2', 'code' => 'tape', 'category' => 'storage', 'position' => '37'],
            ],
        ]);

        $this->assertSame(
            [
                ['id' => 1, 'code' => 'zx48', 'category' => 'computers', 'position' => 0],
                ['id' => 2, 'code' => 'tape', 'category' => 'storage', 'position' => 37],
            ],
            $repository->getAll(),
        );
    }

    public function testNameRowsComeBackTyped(): void
    {
        $repository = $this->createRepository([
            'hardware_name' => [
                ['hardwareId' => '1', 'languageCode' => 'en', 'name' => 'ZX Spectrum 48K', 'shortName' => '48'],
            ],
        ]);

        $this->assertSame(
            [['hardwareId' => 1, 'languageCode' => 'en', 'name' => 'ZX Spectrum 48K', 'shortName' => '48']],
            $repository->getAllNames(),
        );
    }

    public function testUsageCountsAreKeyedByHardwareIdAndTyped(): void
    {
        $repository = $this->createRepository([
            'module_zxrelease_hw_required' => [
                ['hardwareId' => '1', 'amount' => '43247'],
                ['hardwareId' => '2', 'amount' => '432'],
            ],
        ]);

        $this->assertSame([1 => 43247, 2 => 432], $repository->getUsageCounts());
    }

    public function testFindIdByCodeReturnsNullWhenTheCodeIsUnknown(): void
    {
        $repository = $this->createRepository([], null);

        $this->assertNull($repository->findIdByCode('nope'));
    }

    public function testFindIdByCodeCastsTheResult(): void
    {
        $repository = $this->createRepository([], '7');

        $this->assertSame(7, $repository->findIdByCode('zx48'));
    }

    /**
     * @param array<string, list<array<string, string>>> $rowsByTable
     */
    private function createRepository(array $rowsByTable, mixed $singleValue = null): HardwareRepository
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('table')->willReturnCallback(
            function (string $table) use ($rowsByTable, $singleValue): Builder&MockObject {
                $builder = $this->createMock(Builder::class);
                $builder->method('orderBy')->willReturnSelf();
                $builder->method('select')->willReturnSelf();
                $builder->method('selectRaw')->willReturnSelf();
                $builder->method('groupBy')->willReturnSelf();
                $builder->method('where')->willReturnSelf();
                $builder->method('get')->willReturn($rowsByTable[$table] ?? []);
                $builder->method('value')->willReturn($singleValue);

                return $builder;
            },
        );

        return new HardwareRepository($connection);
    }
}
