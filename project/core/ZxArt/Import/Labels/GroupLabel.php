<?php
declare(strict_types=1);

namespace ZxArt\Import\Labels;

final class GroupLabel
{
    /**
     * @param GroupLabel[]|null $groups
     */
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?array  $groups = null,
        public ?bool   $isAlias = null,
        public ?array  $memberNames = null,
        public ?array  $parentGroupIds = null,
        public ?string $type = null,
    )
    {
        $this->id = $this->id ?: $this->name;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ?: $this->name,
            'title' => $this->name,
            'cityName' => $this->city,
            'countryName' => $this->country,
            'memberNames' => $this->memberNames,
            'parentGroupIds' => $this->parentGroupIds,
            'groups' => array_map(static fn($group) => $group->toArray(), $this->groups ?? []),
            'isAlias' => $this->isAlias,
            'type' => $this->type,
            'isGroup' => true,
            'isPerson' => false,
        ];
    }
}