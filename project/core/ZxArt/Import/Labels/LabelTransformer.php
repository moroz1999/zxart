<?php
declare(strict_types=1);

namespace ZxArt\Import\Labels;

final class LabelTransformer
{
    public function toGroupLabel(Label $label): GroupLabel
    {
        return new GroupLabel(
            id: $label->id,
            isAlias: $label->isAlias,
            name: $label->name,
            cityName: $label->cityName,
            countryName: $label->countryName,
            countryId: $label->countryId,
            locationName: $label->locationName,
            memberNames: $label->memberNames,
            parentGroupIds: $label->groupIds,
            type: $label->type?->value ?? null,
            abbreviation: $label->abbreviation,
            website: $label->website,
            aliasParentGroupId: $label->groupId,
        );
    }

    public function toPersonLabel(Label $label): PersonLabel
    {
        return new PersonLabel(
            id: $label->id,
            isAlias: $label->isAlias,
            name: $label->name,
            realName: $label->realName,
            cityName: $label->cityName,
            countryName: $label->countryName,
            countryId: $label->countryId,
            cityId: $label->cityId,
            locationName: $label->locationName,
            groupIds: $label->groupIds,
            groupLabels: $label->groupLabels,
            groupRoles: $label->groupRoles,
            authorId: $label->authorId ? (int)$label->authorId : null,
        );
    }
}
