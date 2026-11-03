<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Actions;

/**
 * An action that never writes; the assistant calls it freely.
 *
 * @psalm-api
 */
interface ReadAction extends Action
{
    /**
     * @return object a result DTO, serialized for the model
     */
    public function read(Arguments $arguments): object;
}
