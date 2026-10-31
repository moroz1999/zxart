<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\ToolRegistry;
use ZxArt\Assistant\Tools\ToolRequestBuilder;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * A write tool as a plan runs it: resolved by name, built from JSON, previewed,
 * then executed.
 */
abstract class WriteToolTestCase extends EntityWriteTestCase
{
    private const array ACTIONS = ['publicReceive', 'publicDelete', 'join', 'deleteAuthor', 'convertToGroup', 'convertToAuthor', 'delete'];
    private const array TYPES = ['zxProd', 'zxRelease', 'author', 'authorAlias', 'group', 'groupAlias', 'file'];

    protected function preview(string $toolName, string $arguments): ChangePreviewDto
    {
        $tool = $this->writeTool($toolName);

        return $tool->preview($this->container->get(ToolRequestBuilder::class)->build($tool->requestClass(), $arguments));
    }

    protected function execute(string $toolName, string $arguments): object
    {
        $tool = $this->writeTool($toolName);

        return $tool->execute($this->container->get(ToolRequestBuilder::class)->build($tool->requestClass(), $arguments));
    }

    protected function signInAsRegisteredUser(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
    }

    /**
     * A registered user who may also change, delete, join and convert every
     * entity the tools write.
     */
    protected function signInAsEditor(): void
    {
        $privileges = [];
        foreach (self::TYPES as $type) {
            foreach (self::ACTIONS as $action) {
                $privileges[] = [$type, $action];
            }
        }
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], $privileges);
    }

    private function writeTool(string $toolName): WriteTool
    {
        $tool = $this->container->get(ToolRegistry::class)->get($toolName);
        self::assertInstanceOf(WriteTool::class, $tool);

        return $tool;
    }
}
