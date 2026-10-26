<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Catalogues;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Tags\Dbal\TagSearchDbal;

/**
 * @implements ReadTool<FindTagsRequestDto>
 *
 * @psalm-api
 */
final readonly class FindTagsTool implements ReadTool
{
    private const int MAX_LIMIT = 100;

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
    public function requestClass(): string
    {
        return FindTagsRequestDto::class;
    }

    #[Override]
    public function execute(object $request): FoundTagsDto
    {
        return new FoundTagsDto($this->tagSearchDbal->find($request->phrase, min(max(1, $request->limit), self::MAX_LIMIT)));
    }
}
