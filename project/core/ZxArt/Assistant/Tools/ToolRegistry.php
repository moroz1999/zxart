<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools;

use LogicException;

/**
 * The tools by name. It knows nothing of the chat, so any caller — the
 * assistant's run loop, a future MCP endpoint — resolves tools the same way.
 *
 * @psalm-api
 */
final readonly class ToolRegistry
{
    /** @var array<string, Tool> */
    private array $toolsByName;

    /**
     * @param Tool[] $tools
     * @throws LogicException when two tools share a name
     */
    public function __construct(array $tools)
    {
        $toolsByName = [];
        foreach ($tools as $tool) {
            $name = $tool->name();
            if (isset($toolsByName[$name])) {
                throw new LogicException("Two tools are named $name");
            }
            $toolsByName[$name] = $tool;
        }
        $this->toolsByName = $toolsByName;
    }

    /**
     * @return Tool[] in the order they were given
     */
    public function all(): array
    {
        return array_values($this->toolsByName);
    }

    /**
     * @throws UnknownToolException
     */
    public function get(string $name): Tool
    {
        return $this->toolsByName[$name] ?? throw new UnknownToolException("There is no tool named $name");
    }
}
