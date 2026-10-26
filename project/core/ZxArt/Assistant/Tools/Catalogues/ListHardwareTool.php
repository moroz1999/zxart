<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Catalogues;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Hardware\HardwareCatalogService;

/**
 * @implements ReadTool<ListHardwareRequestDto>
 *
 * @psalm-api
 */
final readonly class ListHardwareTool implements ReadTool
{
    public function __construct(
        private HardwareCatalogService $hardwareCatalogService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'list_hardware';
    }

    #[Override]
    public function description(): string
    {
        return 'Lists the hardware codes productions and releases can require, by group (computers, storage, DOS, '
            . 'sound, controls, expansion), each with its name. Hardware filters and fields take the codes.';
    }

    #[Override]
    public function requestClass(): string
    {
        return ListHardwareRequestDto::class;
    }

    #[Override]
    public function execute(object $request): HardwareListDto
    {
        $labels = $this->hardwareCatalogService->getLabels();
        $groups = [];
        foreach ($this->hardwareCatalogService->getGroupedCodes() as $group => $codes) {
            $items = array_map(static fn(string $code): HardwareOptionDto => new HardwareOptionDto($code, $labels[$code]['name'] ?? $code), $codes);
            $groups[] = new HardwareGroupDto((string)$group, $items);
        }

        return new HardwareListDto($groups);
    }
}
