<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Import;

require_once __DIR__ . '/ImportGoldenMasterTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RuntimeException;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Shared\EntityType;

/**
 * A production import is one use case: when it stops halfway, nothing it wrote
 * stays.
 */
#[RunTestsInSeparateProcesses]
final class ProdImportAtomicityTest extends ImportGoldenMasterTestCase
{
    private const int MISSING_ELEMENT_ID = 999999999;

    public function testAnImportStoppedByAHalfCreatedAuthorWritesNoElements(): void
    {
        $this->mapImportId(self::MISSING_ELEMENT_ID, 'gm-ghost', ImportOrigin::Zxdb, EntityType::Author);

        try {
            $this->container->get(ProdsService::class)->importProd(
                new ProdImportDTO(
                    id: 'gm-atomic',
                    title: 'Goldmaster Atomic',
                    year: 1995,
                    authorRoles: ['gm-ghost' => ['code']],
                ),
                ImportOrigin::Zxdb,
            );
            self::fail('The import went on with an author the structure cannot load');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('half-created', $exception->getMessage());
        }

        self::assertSame([], $this->newElementIds());
    }

    public function testAReleaseStoppedByAHalfCreatedAuthorTakesItsProductionWithIt(): void
    {
        $this->mapImportId(self::MISSING_ELEMENT_ID, 'gm-ghost', ImportOrigin::Zxdb, EntityType::Author);

        try {
            $this->container->get(ProdsService::class)->importProd(
                new ProdImportDTO(
                    id: 'gm-atomic',
                    title: 'Goldmaster Atomic',
                    year: 1995,
                    releases: [
                        new ReleaseImportDTO(id: 'gm-atomic-r1', title: 'Goldmaster Atomic', authors: ['gm-ghost' => ['code']]),
                    ],
                ),
                ImportOrigin::Zxdb,
            );
            self::fail('The import went on with an author the structure cannot load');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('half-created', $exception->getMessage());
        }

        self::assertSame([], $this->newElementIds());
    }
}
