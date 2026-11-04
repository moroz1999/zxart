<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Actions;

/**
 * The page a typed search answers: `limit` and `offset` beside its filter.
 */
final readonly class PageArguments
{
    public const string PROPERTIES = <<<'JSON'
        "limit": {"type": ["integer", "null"], "description": "the most results to answer, 20 when not given, up to 100"},
        "offset": {"type": ["integer", "null"], "description": "how many results of the order to skip"}
        JSON;

    private const int LIMIT = 20;
    private const int MAX_LIMIT = 100;

    public function __construct(
        public int $limit,
        public int $offset,
    ) {
    }

    /**
     * @throws ToolArgumentsException
     */
    public static function from(Arguments $arguments): self
    {
        return new self(
            limit: self::limit($arguments),
            offset: max(0, $arguments->number('offset') ?? 0),
        );
    }

    /**
     * `limit` kept between 1 and 100, 20 when not given.
     *
     * @throws ToolArgumentsException
     */
    public static function limit(Arguments $arguments): int
    {
        return min(max(1, $arguments->number('limit') ?? self::LIMIT), self::MAX_LIMIT);
    }
}
