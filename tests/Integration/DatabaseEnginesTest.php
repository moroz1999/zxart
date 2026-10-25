<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration;

use controller;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;

/**
 * Transactions need every table on InnoDB: a MyISAM table ignores them, so a
 * rollback would undo only part of a change.
 */
final class DatabaseEnginesTest extends TestCase
{
    public function testEveryTableIsInnoDb(): void
    {
        $db = controller::getInstance()->getContainer()->get(Connection::class);
        $otherEngines = $db->select(
            "SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' AND ENGINE <> 'InnoDB'
             ORDER BY TABLE_NAME",
        );

        self::assertSame([], array_map(
            static fn(array $row): string => $row['TABLE_NAME'] . ' (' . $row['ENGINE'] . ')',
            $otherEngines,
        ));
    }

    /**
     * InnoDB clusters rows by the primary key, or by the first unique key when
     * there is none; a table with neither gets a hidden key nothing can use.
     */
    public function testEveryTableHasAPrimaryOrUniqueKey(): void
    {
        $db = controller::getInstance()->getContainer()->get(Connection::class);
        $withoutKey = $db->select(
            "SELECT t.TABLE_NAME FROM information_schema.TABLES t
             WHERE t.TABLE_SCHEMA = DATABASE() AND t.TABLE_TYPE = 'BASE TABLE'
               AND NOT EXISTS (
                   SELECT 1 FROM information_schema.TABLE_CONSTRAINTS c
                   WHERE c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME
                     AND c.CONSTRAINT_TYPE IN ('PRIMARY KEY', 'UNIQUE')
               )
             ORDER BY t.TABLE_NAME",
        );

        self::assertSame([], array_column($withoutKey, 'TABLE_NAME'));
    }
}
