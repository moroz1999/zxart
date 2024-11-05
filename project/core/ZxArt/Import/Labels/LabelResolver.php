<?php
declare(strict_types=1);

namespace ZxArt\Import\Labels;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use structureElement;
use structureManager;
use ZxArt\Authors\Repositories\AuthorAliasesRepository;
use ZxArt\Authors\Repositories\AuthorsRepository;
use ZxArt\Groups\Repositories\GroupAliasesRepository;
use ZxArt\Groups\Repositories\GroupsRepository;

final readonly class LabelResolver
{
    public function __construct(
        private AuthorsRepository       $authorsRepository,
        private AuthorAliasesRepository $authorAliasesRepository,
        private GroupsRepository        $groupsRepository,
        private GroupAliasesRepository  $groupAliasesRepository,
        private structureManager        $structureManager,
    )
    {

    }

    /**
     * @return authorElement|authorAliasElement|groupElement|groupAliasElement|null
     */
    public function resolve(Label $label): ?structureElement
    {
        return match ($label->type) {
            LabelType::group => $this->resolveGroup($label),
            LabelType::person => $this->resolveAuthor($label),
            default => $this->resolveUnknown($label),
        };
    }

    /**
     * @return groupElement|groupAliasElement|null
     */
    private function resolveGroup(Label $label): ?structureElement
    {
        return $this->resolveEntity(
            $label,
            fn(?string $name) => $this->groupsRepository->findGroupIdsByName($name),
            fn(?string $name) => $this->groupAliasesRepository->findAliasIdsByName($name),
            static fn($alias) => $alias->getGroupElement(),
            /**
             * @var groupElement|groupAliasElement $groupElement
             */
            function (structureElement $groupElement, ?string $elementName, Label $label) {
                $elementCountryTitle = $groupElement->getCountryTitle();
                $labelCountry = $label->country ?? null;

                if ($elementCountryTitle !== null && $labelCountry !== null && !$groupElement->matchesCountry($labelCountry)) {
                    return 0;
                }

                $score = 0;

                if ($this->valueMatches($elementName, $label->name)) {
                    $score += 10;
                }
                if ($groupElement->matchesCity($label->city ?? '')) {
                    $score += 5;
                }
                if ($groupElement->matchesCountry($labelCountry ?? '')) {
                    $score += 5;
                }
                return $score;
            }
        );
    }

    /**
     * @return authorElement|authorAliasElement|null
     */
    private function resolveAuthor(Label $label): ?structureElement
    {
        return $this->resolveEntity(
            $label,
            fn(?string $name) => $this->authorsRepository->findAuthorIdsByName($name, $label->realName),
            fn(?string $name) => $this->authorAliasesRepository->findAliasIdsByName($name),
            static fn($alias) => $alias->getAuthorElement(),
            /**
             * @var authorElement|authorAliasElement $author
             */
            function (structureElement $author, ?string $elementName, Label $label) {
                $authorGroups = $author->getGroupsList();
                $authorRealName = $author->realName ?? '';
                $labelName = $label->name;
                $isRealNameFull = str_contains($authorRealName, ' ');
                $isNickNameExisting = $labelName !== '' && $labelName !== null;
                $areGroupsExisting = count($authorGroups) > 0;

                if (!($isRealNameFull || $isNickNameExisting || $areGroupsExisting)) {
                    return 0; //too few data for author to match it with existing
                }

                $authorCountry = $author->getCountryTitle();
                $labelCountry = $label->country ?? null;

                //don't match authors from different countries
                if ($authorCountry !== null && $labelCountry !== null && !$author->matchesCountry($labelCountry)) {
                    return 0;
                }

                $labelGroups = $label->groups ?? [];

                // if both have groups, then exclude non-matching
                if (count($authorGroups) > 0 && count($labelGroups) > 0) {
                    $authorGroupTitles = array_map(static fn($authorGroup) => $authorGroup->getTitle(), $authorGroups);
                    $labelGroupTitles = array_map(static fn($groupLabel) => $groupLabel->name, $labelGroups);

                    $groupMatches = array_intersect($authorGroupTitles, $labelGroupTitles);

                    if (count($groupMatches) === 0) {
                        return 0;
                    }
                }

                $score = 0;

                if ($this->valueMatches($elementName, $labelName)) {
                    $score += 10;
                }
                if ($isRealNameFull && $this->valueMatches($authorRealName, $label->realName)) {
                    $score += 20;
                }
                if ($author->matchesCity($label->city ?? '')) {
                    $score += 5;
                }
                if ($author->matchesCountry($labelCountry ?? '')) {
                    $score += 5;
                }

                if (isset($groupMatches) && count($groupMatches) > 0) {
                    $score += 20;
                }

                return $score;
            }
        );
    }

    /**
     * @return authorElement|authorAliasElement|groupElement|groupAliasElement|null
     */
    private function resolveUnknown(Label $label): ?structureElement
    {
        return $this->resolveAuthor($label) ?? $this->resolveGroup($label);
    }

    private function resolveEntity(
        Label    $label,
        callable $findEntityIdsByName,
        callable $findAliasIdsByName,
        callable $getElementFromAlias,
        callable $calculateScoreForElement,
    ): ?structureElement
    {
        $entityIds = $findEntityIdsByName($label->name);
        $entities = $entityIds ? array_map(fn(int $id) => $this->structureManager->getElementById($id), $entityIds) : [];

        $aliasIds = $findAliasIdsByName($label->name);
        $aliases = $aliasIds ? array_map(fn(int $id) => $this->structureManager->getElementById($id), $aliasIds) : [];

        $candidates = [];

        foreach ($entities as $entity) {
            $score = $calculateScoreForElement($entity, $entity->getTitle(), $label);
            if ($score === 0) {
                continue;
            }
            $candidates[] = [
                'element' => $entity,
                'score' => $score,
            ];
        }

        foreach ($aliases as $alias) {
            $entity = $getElementFromAlias($alias);
            // score must be calculated from main entity, which has all groups, locations and so on
            // but alias title is for comparison
            $score = $calculateScoreForElement($entity, $alias->getTitle(), $label);
            if ($score === 0) {
                continue;
            }
            $candidates[] = [
                'element' => $alias,
                'score' => $score,
            ];
        }

        usort($candidates, static fn($a, $b) => $b['score'] <=> $a['score']);

        return $candidates[0]['element'] ?? null;
    }

    private function valueMatches(?string $value1, ?string $value2): bool
    {
        return !empty($value1) && !empty($value2) && mb_strtolower(trim($value1)) === mb_strtolower(trim($value2));
    }
}