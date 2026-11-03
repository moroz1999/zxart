<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Actions;

use ZxArt\Shared\EntityType;

/**
 * An action that changes the site. It runs only in a plan the user approved;
 * the plan shows its arguments as the model gave them, beside the state of
 * the entity it changes.
 *
 * @psalm-api
 */
interface WriteAction extends Action
{
    public function kind(): ChangeKind;

    /**
     * The type of the entity the action changes.
     */
    public function entityType(): EntityType;

    /**
     * The entity the action changes as it is now; null for one it creates.
     */
    public function state(Arguments $arguments): ?object;

    /**
     * @return object the use case's result, `{id}` of what it created or changed
     */
    public function write(Arguments $arguments): object;
}
