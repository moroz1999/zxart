<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use ZxArt\Releases\Dto\FoundReleaseDto;
use ZxArt\Shared\Dto\CreditDto;
use ZxArt\Shared\Dto\ListedFileDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * A production as an edit needs to see it.
 *
 * @psalm-api
 */
final readonly class ProdCardDto
{
    /**
     * @param list<EntityRefDto> $refs the entities the fields name by id
     * @param list<CreditDto> $authors
     * @param list<FoundReleaseDto> $releases
     * @param list<ListedFileDto> $files
     * @param list<EntityRefDto> $inSeries the series productions it is a part of
     * @param list<EntityRefDto> $inCompilations the compilations that include it
     */
    public function __construct(
        public int $id,
        /** the fields as `update_prod` takes them */
        public ProdFieldsDto $fields,
        public array $refs,
        public array $authors,
        public array $releases,
        public array $files,
        public array $inSeries,
        public array $inCompilations,
    ) {
    }
}
