<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Parties\Dbal\PartySearchDbal;

/**
 * @implements ReadTool<FindPartiesRequestDto>
 *
 * @psalm-api
 */
final readonly class FindPartiesTool implements ReadTool
{
    private const int MAX_LIMIT = 100;

    public function __construct(
        private PartySearchDbal $partySearchDbal,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'find_parties';
    }

    #[Override]
    public function description(): string
    {
        return 'Finds parties by a part of the title, abbreviation or original name, optionally of one year, with '
            . 'their ids for a production\'s partyId. Answers each with its title, abbreviation and year.';
    }

    #[Override]
    public function requestClass(): string
    {
        return FindPartiesRequestDto::class;
    }

    #[Override]
    public function execute(object $request): FoundPartiesDto
    {
        $limit = min(max(1, $request->limit), self::MAX_LIMIT);

        return new FoundPartiesDto($this->partySearchDbal->find($request->phrase, $request->year, $limit));
    }
}
