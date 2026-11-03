<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Catalogues;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\NoArgumentsDto;
use ZxArt\Hardware\HardwareCatalogService;

/**
 * @psalm-api
 */
final readonly class ListHardwareAction implements ReadAction
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
    public function schema(): string
    {
        return NoArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): HardwareListDto
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
