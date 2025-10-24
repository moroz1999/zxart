<?php
declare(strict_types=1);

namespace ZxArt\Import\Labels;

final class LabelTransformer
{
    public function toGroupLabel(Label $label): GroupLabel
    {
        return new GroupLabel(
            id: $label->id,
            name: $label->name,
            cityName: $label->cityName,
            countryName: $label->countryName,
            countryId: $label->countryId,
            locationName: $label->locationName,
            groups: $label->groups,
            isAlias: $label->isAlias,
            memberNames: $label->memberNames,
            parentGroupIds: null,
            type: $label->type?->value ?? null,
            abbreviation: null,
            website: null,
            groupId: $label->groupId,
        );
    }

    public function toPersonLabel(Label $label): PersonLabel
    {
        return new PersonLabel(
            id: $label->id,
            name: $label->name,
            realName: $label->realName,
            cityName: $label->cityName,
            countryName: $label->countryName,
            countryId: $label->countryId,
            cityId: $label->cityId,
            locationName: $label->locationName,
            groupImportIds: $label->groups,
            groupsIds: null,
            groupRoles: $label->groupRoles,
            isAlias: $label->isAlias,
            authorId: $label->authorId ? (int)$label->authorId : null,
        );
    }
}
