<?php

declare(strict_types=1);

namespace ZxArt\Shared\UseCases;

use Illuminate\Database\Connection;
use Throwable;

/**
 * Runs a use case in one database transaction: a failure anywhere takes back
 * everything it wrote, and the failure reaches the caller. Inside another
 * transaction it is a savepoint.
 *
 * Only the database is taken back. What the legacy managers keep in memory or
 * in the cache about elements the failed work created stays: a request that
 * failed ends anyway, and element ids are never handed out twice.
 *
 * @psalm-api
 */
final readonly class TransactionRunner
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @template TResult
     * @param callable(): TResult $work
     * @return TResult
     */
    public function run(callable $work): mixed
    {
        $this->db->beginTransaction();
        try {
            $result = $work();
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }

        return $result;
    }
}
