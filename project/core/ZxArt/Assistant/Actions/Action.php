<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Actions;

/**
 * What the model calls by name: an action of the assistant's own. Its arguments
 * DTO holds the JSON schema written for the model, with only the fields the
 * task needs, and builds itself from the parsed arguments; the action maps it
 * straight to a domain service or a use case's command.
 *
 * @psalm-api
 */
interface Action
{
    /**
     * The name the model calls the action by, in snake case.
     */
    public function name(): string;

    /**
     * What the action does, for the model.
     */
    public function description(): string;

    /**
     * The JSON schema of its arguments, as its arguments DTO holds it.
     */
    public function schema(): string;
}
