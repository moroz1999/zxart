<?php

declare(strict_types=1);

namespace ZxArt\AuthorList\Rest;

readonly class AuthorFilterOptionsRestDto
{
    /**
     * @param AuthorFilterOptionRestDto[] $countries
     * @param AuthorFilterOptionRestDto[] $cities
     */
    public function __construct(
        public array $countries,
        public array $cities,
    ) {
    }
}
