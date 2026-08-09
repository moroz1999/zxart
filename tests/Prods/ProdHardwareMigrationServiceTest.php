<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Hardware\HardwareGroup;
use ZxArt\Prods\Repositories\ProdHardwareRepository;
use ZxArt\Prods\Services\ProdHardwareMigrationService;
use ZxArt\Prods\Services\ProdHardwareService;
use ZxArt\Releases\ReleaseTypes;
use zxProdElement;

/**
 * The rule for splitting a production's hardware between the production and its
 * releases.
 *
 * The plan is computed and returned rather than applied, so these tests pin the
 * decision itself — including the cases where the answer is "do nothing", which
 * is what protects productions whose releases genuinely disagree.
 */
#[AllowMockObjectsWithoutExpectations]
class ProdHardwareMigrationServiceTest extends TestCase
{
    private ProdHardwareMigrationService $service;

    protected function setUp(): void
    {
        $this->service = new ProdHardwareMigrationService($this->makeCatalog());
    }

    public function testSharedCodesMoveToTheProdAndDeviationsStayOnTheReleases(): void
    {
        $prod = $this->makeProd([
            [1, ReleaseTypes::original->value, ['zx48', 'ay', 'kempston']],
            [2, ReleaseTypes::original->value, ['zx128', 'ay', 'kempston']],
        ]);

        $plan = $this->service->plan($prod);

        $this->assertSame(['ay', 'kempston'], array_values($plan['prod']));
        $this->assertSame(['zx48'], $plan['releases'][1]);
        $this->assertSame(['zx128'], $plan['releases'][2]);
    }

    public function testEveryReleaseWithHardwareNarrowsTheSharedSet(): void
    {
        $prod = $this->makeProd([
            [1, ReleaseTypes::original->value, ['zx48', 'ay']],
            [2, 'crack', ['zx48', 'ay', 'kempston']],
        ]);

        $plan = $this->service->plan($prod);

        $this->assertSame(['zx48', 'ay'], array_values($plan['prod']));
        // release 1 says exactly what the production says, in both categories
        $this->assertSame([], $plan['releases'][1]);
        // release 2 keeps its controls whole — the category is its own statement,
        // so it is not inherited back and must not be taken apart
        $this->assertSame(['kempston'], $plan['releases'][2]);
    }

    public function testReleaseTypeDoesNotDecideTheSharedSet(): void
    {
        $prod = $this->makeProd([
            [1, 'rerelease', ['zx48', 'ay']],
            [2, 'crack', ['zx48', 'kempston']],
        ]);

        $plan = $this->service->plan($prod);

        $this->assertSame(['zx48'], array_values($plan['prod']));
    }

    /**
     * The regression that made this rule what it is. Collecting the shared set
     * from the `original` releases alone let the production claim both machines,
     * and the 128K-only re-release — which is not a source — then had `zx128`
     * subtracted as "already said" and inherited `zx48` on top. A release that
     * meant one machine ended up meaning two.
     */
    public function testAReleaseNarrowerThanTheOriginalsKeepsItsMeaning(): void
    {
        $prod = $this->makeProd([
            [1, ReleaseTypes::original->value, ['zx48', 'zx128', 'ay']],
            [2, ReleaseTypes::original->value, ['zx48', 'zx128', 'ay']],
            [3, 'rerelease', ['zx128', 'ay']],
        ]);

        $plan = $this->service->plan($prod);

        // collected from the originals, which both carry all three
        $this->assertSame(['zx48', 'zx128', 'ay'], array_values($plan['prod']));
        // the originals state the production's set exactly, in every category
        $this->assertSame([], $plan['releases'][1]);
        $this->assertSame([], $plan['releases'][2]);
        // the 128K-only re-release differs on machines, so that category stays
        // with it whole and is not inherited; only its sound moves up
        $this->assertSame(['zx128'], $plan['releases'][3]);
    }

    /**
     * The invariant the rule exists to hold, checked over every shape below.
     *
     * Category by category: whatever a release **stated** survives untouched, and
     * a category it was **silent** about ends up saying what the production says.
     * Nothing a release stated is ever narrowed, widened or dropped — that is the
     * failure that damaged the live data — while a silent category picking the
     * production's codes up is inheritance doing its job.
     */
    #[DataProvider('productionShapes')]
    public function testNoReleaseLosesOrChangesWhatItStated(array $releases): void
    {
        $plan = $this->service->plan($this->makeProd($releases));
        if ($plan === null) {
            $this->assertTrue(true);
            return;
        }

        $catalog = $this->makeCatalog();
        foreach ($releases as [$id, , $hardwareBefore]) {
            if ($hardwareBefore === []) {
                continue;
            }

            // resolved by the real service, so the migration and the inheritance
            // rules are checked against each other rather than restated here
            $repository = $this->createMock(ProdHardwareRepository::class);
            $repository->method('getProdCodesForRelease')->willReturn($plan['prod']);
            $resolver = new ProdHardwareService($repository, $catalog);
            $effectiveAfter = $resolver->getEffectiveCodes($id, $plan['releases'][$id] ?? $hardwareBefore);

            foreach ($this->categoriesOf([...$hardwareBefore, ...$plan['prod']], $catalog) as $category) {
                $statedBefore = $this->inCategory($hardwareBefore, $category, $catalog);
                $expected = $statedBefore !== []
                    ? $statedBefore
                    : $this->inCategory($plan['prod'], $category, $catalog);

                $this->assertSame(
                    $expected,
                    $this->inCategory($effectiveAfter, $category, $catalog),
                    'release ' . $id . ', category ' . $category,
                );
            }
        }
    }

    /**
     * @param string[] $codes
     * @return list<string>
     */
    private function categoriesOf(array $codes, HardwareCatalogService $catalog): array
    {
        $categories = [];
        foreach ($codes as $code) {
            $category = $catalog->getCategoryOf($code);
            if ($category !== null) {
                $categories[] = $category->value;
            }
        }

        return array_values(array_unique($categories));
    }

    /**
     * @param string[] $codes
     * @return list<string>
     */
    private function inCategory(array $codes, string $category, HardwareCatalogService $catalog): array
    {
        $matching = [];
        foreach ($codes as $code) {
            if ($catalog->getCategoryOf($code)?->value === $category) {
                $matching[] = $code;
            }
        }
        sort($matching);

        return $matching;
    }

    public static function productionShapes(): array
    {
        return [
            'originals wider than a re-release' => [[
                [1, ReleaseTypes::original->value, ['zx48', 'zx128', 'ay']],
                [2, ReleaseTypes::original->value, ['zx48', 'zx128', 'ay']],
                [3, 'rerelease', ['zx128', 'ay']],
            ]],
            'a crack narrower than the original' => [[
                [1, ReleaseTypes::original->value, ['zx48', 'ay', 'kempston']],
                [2, 'crack', ['zx48']],
            ]],
            'machines disagree entirely' => [[
                [1, ReleaseTypes::original->value, ['zx48', 'tape']],
                [2, ReleaseTypes::original->value, ['pentagon128', 'betadisk']],
            ]],
            'one release carries everything' => [[
                [1, ReleaseTypes::original->value, ['zx48', 'tape']],
            ]],
            'a release with no hardware takes no part' => [[
                [1, ReleaseTypes::original->value, ['zx48', 'ay']],
                [2, ReleaseTypes::original->value, []],
            ]],
        ];
    }

    public function testReleasesWithoutHardwareDoNotEmptyTheIntersection(): void
    {
        $prod = $this->makeProd([
            [1, ReleaseTypes::original->value, ['zx48', 'ay']],
            [2, ReleaseTypes::original->value, []],
        ]);

        $plan = $this->service->plan($prod);

        $this->assertSame(['zx48', 'ay'], array_values($plan['prod']));
    }

    /**
     * Originals that share nothing describe genuinely different things. The
     * production is left alone rather than given their union, which would claim
     * it needs two incompatible machines at once.
     */
    public function testSourcesSharingNothingLeaveTheProdEmpty(): void
    {
        $prod = $this->makeProd([
            [1, ReleaseTypes::original->value, ['zx48', 'tape']],
            [2, ReleaseTypes::original->value, ['pentagon128', 'betadisk']],
        ]);

        $this->assertNull($this->service->plan($prod));
    }

    public function testAProductionWithoutReleasesIsSkipped(): void
    {
        $this->assertNull($this->service->plan($this->makeProd([])));
    }

    public function testAProductionWhoseReleasesHaveNoHardwareIsSkipped(): void
    {
        $prod = $this->makeProd([
            [1, ReleaseTypes::original->value, []],
            [2, 'crack', []],
        ]);

        $this->assertNull($this->service->plan($prod));
    }

    public function testASingleReleaseHandsItsWholeSetToTheProd(): void
    {
        $prod = $this->makeProd([[1, ReleaseTypes::original->value, ['zx48', 'tape']]]);

        $plan = $this->service->plan($prod);

        $this->assertSame(['zx48', 'tape'], array_values($plan['prod']));
        $this->assertSame([], $plan['releases'][1]);
    }

    public function testReleasesThatChangeNothingAreLeftOutOfThePlan(): void
    {
        $prod = $this->makeProd([
            [1, ReleaseTypes::original->value, ['zx48', 'tape']],
            // carries nothing, so nothing is removed from it
            [2, 'crack', []],
        ]);

        $plan = $this->service->plan($prod);

        $this->assertArrayHasKey(1, $plan['releases']);
        $this->assertArrayNotHasKey(2, $plan['releases']);
    }

    /**
     * A category is subtracted only as a whole. Release 2 lists two controls where
     * the production requires one, so it keeps both rather than being left owning
     * the joystick it merely adds.
     */
    public function testACategoryIsSubtractedOnlyWhenItMatchesExactly(): void
    {
        $prod = $this->makeProd([
            [1, ReleaseTypes::original->value, ['zx48', 'kempston']],
            [2, ReleaseTypes::original->value, ['zx48', 'kempston', 'sinclair2']],
        ]);

        $plan = $this->service->plan($prod);

        $this->assertSame(['zx48', 'kempston'], array_values($plan['prod']));
        $this->assertSame([], $plan['releases'][1]);
        $this->assertSame(['kempston', 'sinclair2'], $plan['releases'][2]);
    }

    private function makeCatalog(): HardwareCatalogService
    {
        $catalog = $this->createMock(HardwareCatalogService::class);
        $catalog->method('getCategoryOf')->willReturnCallback(
            static fn(string $code): ?HardwareGroup => match (true) {
                in_array($code, ['zx48', 'zx128', 'zx128+3', 'pentagon128', 'samcoupe'], true) => HardwareGroup::COMPUTERS,
                in_array($code, ['ay', 'beeper', 'gs'], true) => HardwareGroup::SOUND,
                in_array($code, ['kempston', 'sinclair2', 'cursor', 'int2_2'], true) => HardwareGroup::CONTROLS,
                in_array($code, ['tape', 'betadisk', '3dosdisk'], true) => HardwareGroup::STORAGE,
                in_array($code, ['trdos', '3dos'], true) => HardwareGroup::DOS,
                default => null,
            },
        );
        $catalog->method('getGroupedCodes')->willReturn([
            HardwareGroup::COMPUTERS->value => ['zx48', 'zx128', 'zx128+3', 'pentagon128', 'samcoupe'],
            HardwareGroup::SOUND->value => ['ay'],
            HardwareGroup::CONTROLS->value => ['kempston'],
            HardwareGroup::STORAGE->value => ['tape', 'betadisk'],
        ]);

        return $catalog;
    }

    /**
     * @param list<array{0: int, 1: string, 2: list<string>}> $releases id, type, hardware
     * @return zxProdElement&MockObject
     */
    private function makeProd(array $releases): zxProdElement
    {
        /** @var zxProdElement&MockObject $prod */
        $prod = $this->getMockBuilder(zxProdElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getReleasesList'])
            ->getMock();

        $prod->method('getReleasesList')->willReturn(array_map(
            fn(array $release): MigrationReleaseDouble => $this->makeRelease($release[0], $release[1], $release[2]),
            $releases,
        ));

        return $prod;
    }

    /**
     * @param list<string> $hardware
     */
    private function makeRelease(int $id, string $releaseType, array $hardware): MigrationReleaseDouble
    {
        return new MigrationReleaseDouble($id, $releaseType, $hardware);
    }
}

/**
 * Stands in for a release.
 *
 * A mocked `zxReleaseElement` cannot be used: `hardwareRequired` and
 * `releaseType` are magic properties backed by data chunks, so assigning them
 * without a DI container stores nothing and every test would see an empty
 * release.
 */
final class MigrationReleaseDouble
{
    /** @param list<string> $hardwareRequired */
    public function __construct(
        private readonly int $id,
        public string $releaseType,
        public array $hardwareRequired,
    ) {
    }

    public function getPersistedId(): int
    {
        return $this->id;
    }
}
