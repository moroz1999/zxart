<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use ZxArt\Groups\Dbal\GroupSearchRepository;
use ZxArt\Groups\Dto\GroupSearchFilterDto;
use ZxArt\Shared\Dto\IdPageDto;

/**
 * @psalm-api
 */
final readonly class GroupSearchService
{
    public function __construct(
        private GroupSearchRepository $searchRepository,
    ) {
    }

    public function search(GroupSearchFilterDto $filter, int $start, int $limit): IdPageDto
    {
        $name = $filter->name !== null ? trim($filter->name) : null;

        return new IdPageDto(
            total: $this->searchRepository->count($name, $filter->countryId, $filter->cityId, $filter->type),
            ids: $this->searchRepository->findIds($name, $filter->countryId, $filter->cityId, $filter->type, $start, $limit),
        );
    }
}
