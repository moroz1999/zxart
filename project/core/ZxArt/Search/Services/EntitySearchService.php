<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use InvalidArgumentException;
use structureElement;
use ZxArt\Search\Dto\FoundEntityDto;
use ZxArt\Search\Dto\PhraseSearchHitDto;
use ZxArt\Search\Dto\PhraseSearchQueryDto;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\EntityType;
use zxProdElement;
use zxReleaseElement;

/**
 * Finds prods, releases, authors, groups and their aliases by a phrase in
 * their titles (and an author's real name), through `PhraseSearchEngine`:
 * one page, aliases a type of their own, the closest titles first whatever
 * their type. Each result carries what tells it apart.
 *
 * @psalm-api
 */
final readonly class EntitySearchService
{
    private const int MAX_LIMIT = 100;

    /** Entity type => the structure type the legacy search knows it by. */
    private const array SEARCHED_TYPES = [
        EntityType::Prod->value => 'zxProd',
        EntityType::Release->value => 'zxRelease',
        EntityType::Author->value => 'author',
        EntityType::AuthorAlias->value => 'authorAlias',
        EntityType::Group->value => 'group',
        EntityType::GroupAlias->value => 'groupAlias',
    ];

    public function __construct(
        private PhraseSearchEngine $phraseSearchEngine,
    ) {
    }

    /**
     * @param EntityType[] $types the types to search; none means all six
     * @return FoundEntityDto[]
     * @throws InvalidArgumentException for a type the search does not cover
     */
    public function search(string $phrase, array $types, int $limit): array
    {
        $result = $this->phraseSearchEngine->find(new PhraseSearchQueryDto(
            phrase: $phrase,
            types: $this->structureTypes($types),
            limit: min(max(1, $limit), self::MAX_LIMIT),
            mode: PhraseSearchMode::SinglePage,
            closestFirst: true,
        ));

        $found = array_map(fn(PhraseSearchHitDto $hit): ?FoundEntityDto => $this->toDto($hit->element), $result->hits);

        return array_values(array_filter($found, static fn(?FoundEntityDto $dto): bool => $dto !== null));
    }

    /**
     * @param EntityType[] $types
     * @return string[]
     */
    private function structureTypes(array $types): array
    {
        if ($types === []) {
            return array_values(self::SEARCHED_TYPES);
        }
        $structureTypes = [];
        foreach ($types as $type) {
            $structureType = self::SEARCHED_TYPES[$type->value] ?? null;
            if ($structureType === null) {
                throw new InvalidArgumentException("The search does not cover {$type->value}");
            }
            $structureTypes[] = $structureType;
        }

        return array_values(array_unique($structureTypes));
    }

    private function toDto(structureElement $element): ?FoundEntityDto
    {
        return match (true) {
            $element instanceof zxProdElement => new FoundEntityDto(
                EntityType::Prod,
                $element->getId(),
                ElementFields::text($element, 'title'),
                $this->yearOrNull(ElementFields::number($element, 'year')),
                implode(', ', array_column($element->getGroupsInfo(), 'title')),
            ),
            $element instanceof zxReleaseElement => new FoundEntityDto(
                EntityType::Release,
                $element->getId(),
                ElementFields::text($element, 'title'),
                $element->getYear(),
                ($prod = $element->getProd()) !== null ? ElementFields::text($prod, 'title') : '',
            ),
            $element instanceof authorElement => new FoundEntityDto(
                EntityType::Author,
                $element->getId(),
                ElementFields::text($element, 'title'),
                null,
                $this->joined([ElementFields::text($element, 'realName'), $this->decoded($element->getCountryTitle())]),
            ),
            $element instanceof authorAliasElement => new FoundEntityDto(
                EntityType::AuthorAlias,
                $element->getId(),
                ElementFields::text($element, 'title'),
                null,
                ($author = $element->getAuthorElement()) !== null ? ElementFields::text($author, 'title') : '',
            ),
            $element instanceof groupElement => new FoundEntityDto(
                EntityType::Group,
                $element->getId(),
                ElementFields::text($element, 'title'),
                null,
                $this->decoded($element->getCountryTitle()),
            ),
            $element instanceof groupAliasElement => new FoundEntityDto(
                EntityType::GroupAlias,
                $element->getId(),
                ElementFields::text($element, 'title'),
                null,
                ($group = $element->getGroupElement()) !== null ? ElementFields::text($group, 'title') : '',
            ),
            default => null,
        };
    }

    private function yearOrNull(int $year): ?int
    {
        return $year > 0 ? $year : null;
    }

    private function decoded(?string $text): string
    {
        return html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * @param string[] $parts
     */
    private function joined(array $parts): string
    {
        return implode(', ', array_filter($parts, static fn(string $part): bool => $part !== ''));
    }
}
