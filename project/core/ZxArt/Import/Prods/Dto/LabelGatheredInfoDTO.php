<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods\Dto;

final readonly class LabelGatheredInfoDTO
{
    public function __construct(
        public string  $id,
        public ?string $title = null,
        public ?bool   $isAlias = null,
        public ?bool   $isPerson = null,
        public ?bool   $isGroup = null,
        public ?string $realName = null,
        public ?int    $countryId = null,
        public ?int    $cityId = null,
        public ?string $locationName = null,
        public ?string $countryName = null,
        public ?string $cityName = null,

        public ?array  $groupsData = null,

        public ?string $authorId = null,
        public ?string $groupId = null,
        public ?array  $groupRoles = null,
    )
    {
    }

    public static function fromArray(array $a): self
    {
        $groups = $a['groupsData'] ?? null;
        if (is_array($groups)) {
            $groups = array_values(array_map('strval', $groups));
        } else {
            $groups = null;
        }

        return new self(
            id: isset($a['id']) ? (string)$a['id'] : null,
            title: isset($a['title']) ? (string)$a['title'] : null,
            isAlias: array_key_exists('isAlias', $a) ? (bool)$a['isAlias'] : null,
            isPerson: array_key_exists('isPerson', $a) ? (bool)$a['isPerson'] : null,
            isGroup: array_key_exists('isGroup', $a) ? (bool)$a['isGroup'] : null,
            realName: isset($a['realName']) ? (string)$a['realName'] : null,
            countryId: isset($a['countryId']) ? (int)$a['countryId'] : null,
            cityId: isset($a['cityId']) ? (int)$a['cityId'] : null,
            groupsData: $groups,
            authorId: isset($a['authorId']) ? (string)$a['authorId'] : null,
            groupId: isset($a['groupId']) ? (string)$a['groupId'] : null,
            groupRoles: isset($a['groupRoles']) && is_array($a['groupRoles']) ? $a['groupRoles'] : null,
        );
    }
}
