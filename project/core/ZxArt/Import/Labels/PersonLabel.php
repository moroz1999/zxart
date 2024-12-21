<?php
declare(strict_types=1);

namespace ZxArt\Import\Labels;

final class PersonLabel
{
    /**
     * @param GroupLabel[]|null $groups
     */
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $realName = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?array  $groups = null,
        public ?array  $groupsIds = null,
        public ?array  $groupRoles = null,
        public ?bool   $isAlias = null,
    )
    {
        $this->id = $this->id ?: $this->name ?: $this->realName;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ?: $this->name ?: $this->realName,
            'title' => $this->name !== '' ? $this->name : null,
            'realName' => $this->realName !== '' ? $this->realName : null,
            'cityName' => $this->city,
            'countryName' => $this->country,
            'groupsIds' => $this->groupsIds,
            'groupRoles' => $this->groupRoles,
            'groups' => array_map(static fn(GroupLabel $group) => $group->toArray(), $this->groups ?? []),
            'isAlias' => $this->isAlias,
            'isGroup' => false,
            'isPerson' => true,
        ];
    }
}