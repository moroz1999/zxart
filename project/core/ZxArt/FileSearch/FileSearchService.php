<?php

declare(strict_types=1);

namespace ZxArt\FileSearch;

use structureManager;
use ZxArt\FileSearch\Dto\FileSearchResultDto;
use ZxArt\FileSearch\Repositories\FileSearchRepository;
use ZxArt\Urls\EntityUrlResolver;

/**
 * File search: finds registered files by name or md5 and resolves the entity
 * (prod/release) each file belongs to. Query building lives in the repository.
 */
readonly class FileSearchService
{
    private const int LIMIT = 200;
    private const int MIN_LENGTH = 2;

    public function __construct(
        private FileSearchRepository $fileSearchRepository,
        private structureManager $structureManager,
        private EntityUrlResolver $entityUrlResolver,
    ) {
    }

    /**
     * @return FileSearchResultDto[]
     */
    public function search(string $query): array
    {
        $query = trim($query);
        if (mb_strlen($query) < self::MIN_LENGTH) {
            return [];
        }

        $isMd5 = preg_match('/^[0-9a-fA-F]{32}$/', $query) === 1;
        $rows = $isMd5
            ? $this->fileSearchRepository->searchByMd5(strtolower($query), self::LIMIT)
            : $this->fileSearchRepository->searchByFileName($query, self::LIMIT);

        $results = [];
        foreach ($rows as $row) {
            $element = $this->structureManager->getElementById($row['elementId']);
            if ($element === null) {
                continue;
            }
            $results[] = new FileSearchResultDto(
                fileName: $row['fileName'],
                md5: $row['md5'],
                title: html_entity_decode($element->title, ENT_QUOTES),
                url: $this->entityUrlResolver->urlFor($element),
                type: $element->structureType,
            );
        }

        return $results;
    }
}
