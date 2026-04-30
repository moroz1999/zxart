<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class ProdFilesRestDto
{
    /**
     * @param ProdFileRestDto[] $files
     */
    public function __construct(
        #[Map(transform: MapCollection::class)]
        public array $files,
    ) {
    }
}
