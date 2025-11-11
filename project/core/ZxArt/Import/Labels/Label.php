<?php
declare(strict_types=1);

namespace ZxArt\Import\Labels;

final readonly class Label
{

    /**
     * @param GroupLabel[]|null $groupLabels
     */
    public function __construct(
        public ?string    $id = null,
        public ?string    $name = null,
        public ?string    $realName = null,
        public ?string    $cityName = null,
        public ?string    $countryName = null,
        public ?bool      $isAlias = null,
        public ?bool      $isPerson = null,
        public ?bool      $isGroup = null,
        public ?int       $countryId = null,
        public ?int       $cityId = null,
        public ?string    $locationName = null,
        public ?LabelType $type = null,
        public ?array     $memberNames = null,

        public ?array     $groupIds = null,
        public ?array     $groupLabels = null,

        public ?string    $authorId = null,
        public ?string    $groupId = null,
        public ?array     $groupRoles = null,

        public ?string    $abbreviation = null,
        public ?string    $website = null,
    ) {}

    public static function fromArray(array $a): self
    {
        $groupIds = $a['groups'] ?? null;
        if (is_array($groupIds)) {
            $groupIds = array_values(array_map('strval', $groupIds));
        } else {
            $groupIds = null;
        }

        return new self(
            id: isset($a['id']) ? (string)$a['id'] : null,
            name: isset($a['title']) ? (string)$a['title'] : null,
            realName: isset($a['realName']) ? (string)$a['realName'] : null,
            isAlias: array_key_exists('isAlias', $a) ? $a['isAlias'] : null,
            isPerson: array_key_exists('isPerson', $a) ? $a['isPerson'] : null,
            isGroup: array_key_exists('isGroup', $a) ? $a['isGroup'] : null,
            countryId: isset($a['countryId']) ? (int)$a['countryId'] : null,
            cityId: isset($a['cityId']) ? (int)$a['cityId'] : null,
            groupIds: $groupIds,
            groupLabels: $groupIds,
            authorId: isset($a['authorId']) ? (string)$a['authorId'] : null,
            groupId: isset($a['groupId']) ? (string)$a['groupId'] : null,
            groupRoles: isset($a['groupRoles']) && is_array($a['groupRoles']) ? $a['groupRoles'] : null,
            abbreviation: isset($a['abbreviation']) ? (string)$a['abbreviation'] : null,
            website: isset($a['website']) ? (string)$a['website'] : null,
        );
    }
}
