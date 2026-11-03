<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Catalogues;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Tags\Dbal\TagSearchDbal;

/**
 * @psalm-api
 */
final readonly class FindTagsAction implements ReadAction
{
    public function __construct(
        private TagSearchDbal $tagSearchDbal,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'find_tags';
    }

    #[Override]
    public function description(): string
    {
        return 'Finds tags by a part of their name or of a synonym in any language, the most used first, with their '
            . 'ids for the production search, their synonyms and how many works carry them.';
    }

    #[Override]
    public function schema(): string
    {
        return FindTagsArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): FoundTagsDto
    {
        $request = FindTagsArgumentsDto::from($arguments);
        return new FoundTagsDto($this->tagSearchDbal->find($request->phrase, $request->limit));
    }
}
