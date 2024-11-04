<?php
declare(strict_types=1);

namespace ZxArt\Labels;

final class Label
{
    /**
     * @param Label[]|null $groups
     */
    public function __construct(
        public ?string    $id = null,
        public ?string    $name = null,
        public ?string    $realName = null,
        public ?string    $city = null,
        public ?string    $country = null,
        public ?array     $groups = null,
        public ?LabelType $type = null,
        public ?bool      $isAlias = null,
    )
    {

    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ?: $this->name ?: $this->realName,
            'title' => $this->name,
            'realName' => $this->realName,
            'cityName' => $this->city,
            'countryName' => $this->country,
            'groups' => array_map(static fn($group) => $group->toArray(), $this->groups ?? []),
            'isAlias' => $this->isAlias,
            'isGroup' => $this->type === LabelType::group,
            'isPerson' => $this->type === LabelType::person,
        ];
    }
}