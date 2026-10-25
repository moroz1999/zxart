<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools;

use LogicException;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Assistant\Tools\ToolRegistry;
use ZxArt\Assistant\Tools\UnknownToolException;
use ZxArt\Assistant\Tools\WriteTool;

final class ToolRegistryTest extends TestCase
{
    public function testToolsAreResolvedByName(): void
    {
        $search = $this->readTool('search');
        $update = $this->writeTool('update_prod');
        $registry = new ToolRegistry([$search, $update]);

        self::assertSame($search, $registry->get('search'));
        self::assertSame($update, $registry->get('update_prod'));
        self::assertSame([$search, $update], $registry->all());
    }

    public function testAnUnknownNameIsRefused(): void
    {
        $registry = new ToolRegistry([$this->readTool('search')]);

        $this->expectException(UnknownToolException::class);
        $this->expectExceptionMessage('delete_everything');

        $registry->get('delete_everything');
    }

    public function testTwoToolsOfOneNameAreRefused(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('search');

        new ToolRegistry([$this->readTool('search'), $this->writeTool('search')]);
    }

    private function readTool(string $name): ReadTool
    {
        $tool = $this->createStub(ReadTool::class);
        $tool->method('name')->willReturn($name);

        return $tool;
    }

    private function writeTool(string $name): WriteTool
    {
        $tool = $this->createStub(WriteTool::class);
        $tool->method('name')->willReturn($name);

        return $tool;
    }
}
