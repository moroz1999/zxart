<?php
declare(strict_types=1);

namespace ZxArt\Import\Labels;

final class PersonLabel
{
    /**
     * @param Array<string|null>|null $groupImportIds
     */
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $realName = null,
        public ?string $cityName = null,
        public ?string $countryName = null,
        public ?int    $countryId = null,
        public ?int    $cityId = null,
        public ?string $locationName = null,
        public ?array  $groupImportIds = null,
        public ?array  $groupsIds = null,
        public ?array  $groupRoles = null,
        public ?int    $authorId = null,
    )
    {
        $this->id = $this->id ?: $this->name ?: $this->realName;
    }
}