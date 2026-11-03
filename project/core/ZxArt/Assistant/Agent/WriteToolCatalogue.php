<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Agent;

use JsonException;
use stdClass;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Llm\LlmToolDto;
use ZxArt\Assistant\Actions\ToolRegistry;
use ZxArt\Assistant\Actions\UnknownToolException;

/**
 * The write tools as the model learns them. The model never calls them; it
 * writes them into a plan. The plan tool lists them by name and purpose only,
 * and `describe_write_tool` answers the arguments of one when the model is
 * about to use it, so their descriptions and schemas do not ride along with
 * every request.
 */
final readonly class WriteToolCatalogue
{
    public const string TOOL_NAME = 'describe_write_tool';

    public function __construct(
        private ToolRegistry $tools,
    ) {
    }

    /**
     * The names of the write tools, which say what each does.
     */
    public function list(): string
    {
        return implode(', ', array_map(static fn(WriteAction $tool): string => $tool->name(), $this->writeTools()));
    }

    public function describeTool(): LlmToolDto
    {
        return new LlmToolDto(
            name: self::TOOL_NAME,
            description: 'Answers what a write tool does and the JSON schema of its arguments. Read it before you write the tool into a plan.',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'name' => [
                        'type' => 'string',
                        'enum' => array_map(static fn(WriteAction $tool): string => $tool->name(), $this->writeTools()),
                    ],
                ],
                'required' => ['name'],
                'additionalProperties' => false,
            ],
        );
    }

    /**
     * @return string the tool's name, purpose and arguments schema, as JSON
     * @throws PlanRefusedException for anything but the name of a write tool
     * @throws JsonException
     */
    public function describe(string $arguments): string
    {
        try {
            $decoded = json_decode($arguments, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new PlanRefusedException('The arguments are not valid JSON: ' . $exception->getMessage(), 0, $exception);
        }
        $name = $decoded instanceof stdClass && is_string($decoded->name ?? null) ? $decoded->name : null;
        if ($name === null) {
            throw new PlanRefusedException('Give the name of a write tool');
        }
        try {
            $tool = $this->tools->get($name);
        } catch (UnknownToolException) {
            throw new PlanRefusedException("There is no tool named $name");
        }
        if (!$tool instanceof WriteAction) {
            throw new PlanRefusedException("$name is not a write tool; call it directly");
        }

        return json_encode([
            'name' => $tool->name(),
            'description' => $tool->description(),
            'arguments' => Arguments::schema($tool->schema()),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return list<WriteAction>
     */
    private function writeTools(): array
    {
        $writeTools = [];
        foreach ($this->tools->all() as $tool) {
            if ($tool instanceof WriteAction) {
                $writeTools[] = $tool;
            }
        }

        return $writeTools;
    }
}
