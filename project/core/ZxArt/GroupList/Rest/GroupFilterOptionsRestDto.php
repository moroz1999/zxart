<?php

declare(strict_types=1);

namespace ZxArt\GroupList\Rest;

readonly class GroupFilterOptionsRestDto
{
    /**
     * @param GroupFilterOptionRestDto[] $countries
     * @param GroupFilterOptionRestDto[] $cities
     */
    public function __construct(
        public array $countries,
        public array $cities,
    ) {
    }
}
