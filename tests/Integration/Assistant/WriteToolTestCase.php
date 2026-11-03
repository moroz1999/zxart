<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\ToolRegistry;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * An action of the assistant as a plan runs it: resolved by name, its
 * arguments parsed by its schema, its target's state read, then written.
 */
abstract class WriteToolTestCase extends EntityWriteTestCase
{
    private const array ACTIONS = ['publicReceive', 'publicDelete', 'join', 'deleteAuthor', 'convertToGroup', 'convertToAuthor', 'delete'];
    private const array TYPES = ['zxProd', 'zxRelease', 'author', 'authorAlias', 'group', 'groupAlias', 'file'];

    protected function actionState(string $actionName, string $arguments): ?object
    {
        $action = $this->writeAction($actionName);

        return $action->state(Arguments::parse($arguments, $action->schema()));
    }

    protected function actionWrite(string $actionName, string $arguments): object
    {
        $action = $this->writeAction($actionName);

        return $action->write(Arguments::parse($arguments, $action->schema()));
    }

    protected function actionRead(string $actionName, string $arguments): object
    {
        $action = $this->container->get(ToolRegistry::class)->get($actionName);
        self::assertInstanceOf(ReadAction::class, $action);

        return $action->read(Arguments::parse($arguments, $action->schema()));
    }

    /**
     * @return array<string, mixed> the JSON schema of the action's arguments
     */
    protected function actionSchema(string $actionName): array
    {
        $action = $this->container->get(ToolRegistry::class)->get($actionName);
        self::assertTrue($action instanceof ReadAction || $action instanceof WriteAction);

        return Arguments::schema($action->schema());
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

    protected function writeAction(string $actionName): WriteAction
    {
        $action = $this->container->get(ToolRegistry::class)->get($actionName);
        self::assertInstanceOf(WriteAction::class, $action);

        return $action;
    }
}
