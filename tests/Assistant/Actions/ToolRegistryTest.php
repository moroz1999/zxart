<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Actions;

use LogicException;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\ToolRegistry;
use ZxArt\Assistant\Actions\UnknownToolException;
use ZxArt\Assistant\Actions\WriteAction;

final class ToolRegistryTest extends TestCase
{
    public function testToolsAreResolvedByName(): void
    {
        $search = $this->readAction('search');
        $update = $this->writeAction('update_prod');
        $registry = new ToolRegistry([$search, $update]);

        self::assertSame($search, $registry->get('search'));
        self::assertSame($update, $registry->get('update_prod'));
        self::assertSame([$search, $update], $registry->all());
    }

    public function testAnUnknownNameIsRefused(): void
    {
        $registry = new ToolRegistry([$this->readAction('search')]);

        $this->expectException(UnknownToolException::class);
        $this->expectExceptionMessage('delete_everything');

        $registry->get('delete_everything');
    }

    public function testTwoToolsOfOneNameAreRefused(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('search');

        new ToolRegistry([$this->readAction('search'), $this->writeAction('search')]);
    }

    private function readAction(string $name): ReadAction
    {
        $tool = $this->createStub(ReadAction::class);
        $tool->method('name')->willReturn($name);

        return $tool;
    }

    private function writeAction(string $name): WriteAction
    {
        $tool = $this->createStub(WriteAction::class);
        $tool->method('name')->willReturn($name);

        return $tool;
    }
}
