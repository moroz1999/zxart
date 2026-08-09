<?php

declare(strict_types=1);

namespace ZxArt\Tests\Hardware;

use LanguagesManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use ZxArt\Hardware\Dto\HardwareNameInputDto;
use ZxArt\Hardware\Dto\HardwareSaveDto;
use ZxArt\Hardware\Exception\HardwareException;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Hardware\HardwareGroup;
use ZxArt\Hardware\Repositories\HardwareRepository;
use ZxArt\Shared\InterfaceLanguage;

#[AllowMockObjectsWithoutExpectations]
class HardwareCatalogServiceTest extends TestCase
{
    private HardwareRepository&MockObject $repository;
    private LanguagesManager&MockObject $languagesManager;
    private HardwareCatalogService $service;
    /** @var list<array{id: int, code: string, category: string, position: int}> */
    private array $rows = [];

    protected function setUp(): void
    {
        $this->repository = $this->createMock(HardwareRepository::class);
        $this->languagesManager = $this->createMock(LanguagesManager::class);
        $this->languagesManager->method('getLanguagesList')->willReturn([
            $this->language('en'),
            $this->language('ru'),
            $this->language('es'),
        ]);
        $this->service = new HardwareCatalogService($this->repository, $this->languagesManager);

        $this->rows = [
            ['id' => 1, 'code' => 'zx48', 'category' => 'computers', 'position' => 0],
            ['id' => 2, 'code' => 'tape', 'category' => 'storage', 'position' => 1],
            ['id' => 3, 'code' => 'trdos', 'category' => 'dos', 'position' => 2],
        ];
        // reads the current rows, so an insert during a test is visible afterwards
        $this->repository->method('getAll')->willReturnCallback(fn(): array => $this->rows);
        $this->repository->method('getAllNames')->willReturn([
            ['hardwareId' => 1, 'languageCode' => 'en', 'name' => 'ZX Spectrum 48K', 'shortName' => '48'],
            ['hardwareId' => 1, 'languageCode' => 'ru', 'name' => 'ZX Spectrum 48K', 'shortName' => '48'],
            ['hardwareId' => 2, 'languageCode' => 'en', 'name' => 'Tape', 'shortName' => 'Tape'],
        ]);
        $this->repository->method('getUsageCounts')->willReturn([1 => 43247, 2 => 432]);
    }

    public function testGroupsCodesInCatalogOrderAndOmitsEmptyGroups(): void
    {
        $this->assertSame(
            [
                'computers' => ['zx48'],
                'storage' => ['tape'],
                'dos' => ['trdos'],
            ],
            $this->service->getGroupedCodes(),
        );
    }

    public function testResolvesCategoryAndIdsFromCodes(): void
    {
        $this->assertSame(HardwareGroup::STORAGE, $this->service->getCategoryOf('tape'));
        $this->assertNull($this->service->getCategoryOf('nope'));
        $this->assertSame([2, 1], $this->service->getIdsByCodes(['tape', 'zx48', 'nope']));
    }

    public function testLabelsFallBackToTheCodeWhenATranslationIsMissing(): void
    {
        $labels = $this->service->getLabels(InterfaceLanguage::Ru);

        $this->assertSame(['name' => 'ZX Spectrum 48K', 'shortName' => '48'], $labels['zx48']);
        // tape has no russian row, and trdos has none at all
        $this->assertSame(['name' => 'tape', 'shortName' => 'tape'], $labels['tape']);
        $this->assertSame(['name' => 'trdos', 'shortName' => 'trdos'], $labels['trdos']);
    }

    public function testCreateStoresTheNormalizedCodeAndNames(): void
    {
        $this->repository->method('findIdByCode')->willReturn(null);
        $this->repository->expects($this->once())
            ->method('insert')
            ->with('mb02', 'storage', 57)
            ->willReturnCallback(function (string $code, string $category, int $position): int {
                $this->rows[] = ['id' => 9, 'code' => $code, 'category' => $category, 'position' => $position];
                return 9;
            });
        $this->repository->expects($this->once())
            ->method('replaceNames')
            ->with(9, [
                'en' => ['name' => 'MB-02+', 'shortName' => 'MB-02'],
                'ru' => ['name' => 'MB-02+', 'shortName' => 'MB-02'],
                'es' => ['name' => 'MB-02+', 'shortName' => 'MB-02'],
            ]);

        // whitespace and case are normalized away before anything is stored
        $item = $this->service->create($this->saveRequest(code: '  MB02  '));

        $this->assertSame(9, $item->id);
        $this->assertSame('mb02', $item->code);
        $this->assertSame(HardwareGroup::STORAGE, $item->category);
    }

    public function testCreateRejectsADuplicateCode(): void
    {
        $this->repository->method('findIdByCode')->willReturn(5);

        $this->expectException(HardwareException::class);
        $this->expectExceptionMessage('Hardware code already exists: mb02');

        $this->service->create($this->saveRequest());
    }

    public function testCodeShapeIsValidated(): void
    {
        $this->expectException(HardwareException::class);
        $this->expectExceptionMessage('lowercase letters, digits');

        $this->service->create($this->saveRequest(code: 'MB 02!'));
    }

    public function testEveryPublicLanguageNeedsBothLabels(): void
    {
        $this->expectException(HardwareException::class);
        $this->expectExceptionMessage('required for language es');

        $this->service->create($this->saveRequest(names: [
            'en' => new HardwareNameInputDto('MB-02+', 'MB-02'),
            'ru' => new HardwareNameInputDto('MB-02+', 'MB-02'),
        ]));
    }

    public function testUpdateWithoutAnIdIsRejected(): void
    {
        $this->expectException(HardwareException::class);
        $this->expectExceptionMessage('Missing required field: id');

        $this->service->update($this->saveRequest());
    }

    public function testDeleteRefusesWhileTheItemIsInUse(): void
    {
        $this->repository->method('exists')->willReturn(true);

        $this->expectException(HardwareException::class);
        $this->expectExceptionMessage('still used by 43247 item(s)');

        $this->service->delete(1);
    }

    public function testDeleteRemovesAnUnusedItem(): void
    {
        $this->repository->method('exists')->willReturn(true);
        $this->repository->expects($this->once())->method('delete')->with(3);

        $this->service->delete(3);
    }

    public function testDeleteOfAnUnknownItemIsNotFound(): void
    {
        $this->repository->method('exists')->willReturn(false);

        $this->expectException(HardwareException::class);
        $this->expectExceptionMessage('Hardware item not found');

        $this->service->delete(999);
    }

    /**
     * @param array<string, HardwareNameInputDto>|null $names
     */
    private function saveRequest(?int $id = null, string $code = 'mb02', ?array $names = null): HardwareSaveDto
    {
        return new HardwareSaveDto(
            id: $id,
            code: $code,
            category: HardwareGroup::STORAGE,
            position: 57,
            names: $names ?? [
                'en' => new HardwareNameInputDto('MB-02+', 'MB-02'),
                'ru' => new HardwareNameInputDto('MB-02+', 'MB-02'),
                'es' => new HardwareNameInputDto('MB-02+', 'MB-02'),
            ],
        );
    }

    private function language(string $iso6391): stdClass
    {
        $language = new stdClass();
        $language->iso6391 = $iso6391;

        return $language;
    }
}
