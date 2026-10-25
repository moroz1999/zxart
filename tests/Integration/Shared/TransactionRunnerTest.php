<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Shared;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RuntimeException;
use ZxArt\Shared\UseCases\TransactionRunner;
use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * A use case runs in one transaction: all of it is written, or none of it.
 */
#[RunTestsInSeparateProcesses]
final class TransactionRunnerTest extends EntityWriteTestCase
{
    private const int ELEMENT_ID = 999999901;

    public function testTheWorkIsKeptAndItsResultAnswered(): void
    {
        $result = $this->runner()->run(function (): string {
            $this->insertOrigin();
            return 'done';
        });

        self::assertSame('done', $result);
        self::assertSame(1, $this->originRows());
    }

    public function testAFailureTakesBackEverythingTheWorkWrote(): void
    {
        try {
            $this->runner()->run(function (): void {
                $this->insertOrigin();
                throw new RuntimeException('refused halfway');
            });
            self::fail('The failure must reach the caller');
        } catch (RuntimeException $exception) {
            self::assertSame('refused halfway', $exception->getMessage());
        }

        self::assertSame(0, $this->originRows());
    }

    private function insertOrigin(): void
    {
        $this->db->table('import_origin')->insert([
            'elementId' => self::ELEMENT_ID,
            'importOrigin' => 'zxdb',
            'importId' => 'transaction-test',
            'type' => 'prod',
        ]);
    }

    private function originRows(): int
    {
        return $this->db->table('import_origin')->where('elementId', '=', self::ELEMENT_ID)->count();
    }

    private function runner(): TransactionRunner
    {
        return $this->container->get(TransactionRunner::class);
    }
}
