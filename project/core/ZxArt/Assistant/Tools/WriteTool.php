<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools;

use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;

/**
 * A tool that changes the site. It runs only as an action of a plan the user
 * approved; the plan shows its preview first.
 *
 * @template TRequest of object
 * @extends Tool<TRequest>
 *
 * @psalm-api
 */
interface WriteTool extends Tool
{
    /**
     * @param TRequest $request
     */
    public function preview(object $request): ChangePreviewDto;
}
