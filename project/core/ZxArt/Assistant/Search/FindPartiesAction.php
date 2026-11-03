<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Search;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Parties\Dbal\PartySearchDbal;

/**
 * @psalm-api
 */
final readonly class FindPartiesAction implements ReadAction
{
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
    public function schema(): string
    {
        return FindPartiesArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): FoundPartiesDto
    {
        $request = FindPartiesArgumentsDto::from($arguments);
        return new FoundPartiesDto($this->partySearchDbal->find($request->phrase, $request->year, $request->limit));
    }
}
