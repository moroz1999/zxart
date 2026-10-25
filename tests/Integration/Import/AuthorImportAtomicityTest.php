<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Import;

require_once __DIR__ . '/ImportGoldenMasterTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RuntimeException;
use ZxArt\Authors\Services\AuthorImportService;
use ZxArt\Groups\UseCases\ImportGroupCommand;
use ZxArt\Groups\UseCases\ImportGroupHandler;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Import\Labels\PersonLabel;
use ZxArt\Import\Services\ImportIdOperator;

/**
 * An author, author alias or group import is one use case: when it stops
 * halfway, nothing it wrote stays.
 */
#[RunTestsInSeparateProcesses]
final class AuthorImportAtomicityTest extends ImportGoldenMasterTestCase
{
    public function testAnAuthorWhoseImportIdCannotBeStoredIsNotLeft(): void
    {
        $this->failImportIdStoring();

        $this->assertStopped(fn() => $this->container->get(AuthorImportService::class)->importAuthor(
            new PersonLabel(id: 'gm-atomic', name: 'Goldmaster Atomic Author'),
            ImportOrigin::Zxdb,
        ));

        self::assertSame([], $this->newElementIds());
    }

    public function testAnAuthorAliasWhoseImportIdCannotBeStoredIsNotLeft(): void
    {
        $this->failImportIdStoring();

        $this->assertStopped(fn() => $this->container->get(AuthorImportService::class)->importAuthorAlias(
            new PersonLabel(id: 'gm-atomic', name: 'Goldmaster Atomic Alias'),
            ImportOrigin::Zxdb,
        ));

        self::assertSame([], $this->newElementIds());
    }

    public function testAGroupWhoseImportIdCannotBeStoredIsNotLeft(): void
    {
        $this->failImportIdStoring();

        $this->assertStopped(fn() => $this->container->get(ImportGroupHandler::class)->handle(new ImportGroupCommand(
            new GroupLabel(id: 'gm-atomic', name: 'Goldmaster Atomic Group'),
            ImportOrigin::Zxdb,
        )));

        self::assertSame([], $this->newElementIds());
    }

    private function failImportIdStoring(): void
    {
        $importIdOperator = $this->createStub(ImportIdOperator::class);
        $importIdOperator->method('getElementByImportId')->willReturn(null);
        $importIdOperator->method('saveImportId')->willThrowException(new RuntimeException('The import id store is down'));
        $this->container->set(ImportIdOperator::class, $importIdOperator);
    }

    private function assertStopped(callable $import): void
    {
        try {
            $import();
            self::fail('The import went on without its import id');
        } catch (RuntimeException $exception) {
            self::assertSame('The import id store is down', $exception->getMessage());
        }
    }
}
