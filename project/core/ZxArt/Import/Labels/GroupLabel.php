<?php
declare(strict_types=1);

namespace ZxArt\Import\Labels;

final class GroupLabel
{
    public function __construct(
        public ?string $id = null,
        public ?bool   $isAlias = null,
        public ?string $name = null,
        public ?string $cityName = null,
        public ?string $countryName = null,
        public ?int    $countryId = null,
        public ?string $locationName = null,
        public ?array  $memberNames = null,
        public ?array  $parentGroupIds = null,
        public ?string $type = null,
        public ?string $abbreviation = null,
        public ?string $website = null,
        public ?string $aliasParentGroupId = null,
    )
    {
        $this->id = $this->id ?: $this->name;
    }
}