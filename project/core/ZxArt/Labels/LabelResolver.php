<?php
declare(strict_types=1);

namespace ZxArt\Labels;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use structureElement;
use structureManager;
use ZxArt\Authors\Repositories\AuthorsRepository;
use ZxArt\Authors\Repositories\AuthorAliasesRepository;
use ZxArt\Authors\Repositories\GroupsRepository;
use ZxArt\Authors\Repositories\GroupAliasesRepository;

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
            fn(string $name) => $this->groupsRepository->findGroupIdsByName($name),
            fn(string $name) => $this->groupAliasesRepository->findAliasIdsByName($name),
            fn($alias) => $alias->getGroupElement(),
            function (groupElement $element, ?string $name, Label $label) {
                $score = 0;
                if ($this->valueMatches($name, $label->name)) {
                    $score += 10;
                }
                if ($element->matchesCity($label->city ?? '')) {
                    $score += 5;
                }
                if ($element->matchesCountry($label->country ?? '')) {
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
            fn(string $name) => $this->authorsRepository->findAuthorIdsByName($name, $label->realName),
            fn(string $name) => $this->authorAliasesRepository->findAliasIdsByName($name),
            fn($alias) => $alias->getAuthorElement(),
            function (authorElement $element, ?string $name, Label $label) {
                $score = 0;
                if ($this->valueMatches($name, $label->name)) {
                    $score += 10;
                }
                if ($this->valueMatches($element->realName, $label->realName)) {
                    $score += 10;
                }
                if ($element->matchesCity($label->city ?? '')) {
                    $score += 5;
                }
                if ($element->matchesCountry($label->country ?? '')) {
                    $score += 5;
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
            $candidates[] = [
                'element' => $entity,
                'score' => $score,
            ];
        }

        foreach ($aliases as $alias) {
            $entity = $getElementFromAlias($alias);
            $score = $calculateScoreForElement($entity, $alias->getTitle(), $label);
            $candidates[] = [
                'element' => $entity,
                'score' => $score,
            ];
        }

        usort($candidates, static fn($a, $b) => $b['score'] <=> $a['score']);

        return $candidates[0]['element'] ?? null;
    }

    private function valueMatches(?string $value1, ?string $value2): bool
    {
        return !empty($value1) && !empty($value2) && trim($value1) === trim($value2);
    }
}