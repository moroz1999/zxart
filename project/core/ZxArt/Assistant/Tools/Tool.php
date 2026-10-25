<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools;

/**
 * A capability the assistant calls by name with a request DTO. A tool is a thin
 * adapter over a domain service: privileges, logic and the actions log stay in
 * the service.
 *
 * @template TRequest of object
 *
 * @psalm-api
 */
interface Tool
{
    /**
     * The name the model calls the tool by, in snake case.
     */
    public function name(): string;

    /**
     * What the tool does, for the model.
     */
    public function description(): string;

    /**
     * @return class-string<TRequest> the request DTO the call's arguments build
     */
    public function requestClass(): string;

    /**
     * @param TRequest $request
     * @return object a result DTO, serialized for the model
     */
    public function execute(object $request): object;
}
