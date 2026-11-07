<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

use ZxArt\Search\Dto\EntityLookupItemDto;
use ZxArt\Search\Dto\PhraseSearchHitDto;
use ZxArt\Search\Dto\PhraseSearchQueryDto;

/**
 * What a relation picker offers for a typed phrase: one page of the entities
 * of the picker's types, through `PhraseSearchEngine` — titles only, aliases a
 * type of their own, the closest titles of each type first.
 *
 * @psalm-api
 */
final readonly class EntityLookupService
{
    private const int LIMIT = 30;

    public function __construct(
        private PhraseSearchEngine $phraseSearchEngine,
        private FoundElementTitle $foundElementTitle,
    ) {
    }

    /**
     * @param PhraseSearchType[] $types
     * @return EntityLookupItemDto[]
     */
    public function lookup(string $phrase, array $types, int $languageId): array
    {
        $result = $this->phraseSearchEngine->find(new PhraseSearchQueryDto(
            phrase: $phrase,
            types: $types,
            limit: self::LIMIT,
            mode: PhraseSearchMode::SinglePage,
            languageId: $languageId,
        ));

        return array_map(
            fn(PhraseSearchHitDto $hit): EntityLookupItemDto => new EntityLookupItemDto(
                $hit->element->getId(),
                $this->foundElementTitle->of($hit->element),
            ),
            $result->hits,
        );
    }
}
