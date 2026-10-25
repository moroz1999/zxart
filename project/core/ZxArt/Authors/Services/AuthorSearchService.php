<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use ZxArt\Authors\Dbal\AuthorSearchDbal;
use ZxArt\Authors\Dto\AuthorSearchFilterDto;
use ZxArt\Shared\Dto\IdPageDto;

/**
 * @psalm-api
 */
final readonly class AuthorSearchService
{
    public function __construct(
        private AuthorSearchDbal $searchRepository,
    ) {
    }

    public function search(AuthorSearchFilterDto $filter, int $start, int $limit): IdPageDto
    {
        $name = $filter->name !== null ? trim($filter->name) : null;

        return new IdPageDto(
            total: $this->searchRepository->count($name, $filter->countryId, $filter->cityId, $filter->groupId),
            ids: $this->searchRepository->findIds($name, $filter->countryId, $filter->cityId, $filter->groupId, $start, $limit),
        );
    }
}
