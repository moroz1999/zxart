<?php
declare(strict_types=1);


namespace ZxArt\Import\Authors;

readonly final class AuthorSufficiencyChecker
{
    public static function isDataSufficient(
        string $authorRealName,
        string $labelName,
        array  $authorGroups,
        array  $authorGroupsIds = [],
    ): bool
    {
        $isRealNameFull = str_contains($authorRealName, ' ');
        $isNickNameExisting = $labelName !== '';
        $areGroupsExisting = (count($authorGroups) > 0) || (count($authorGroupsIds) > 0);

        if (!($isRealNameFull || $isNickNameExisting || $areGroupsExisting)) {
            return false; //too few data for author to match it with existing
        }
        return true;
    }
}