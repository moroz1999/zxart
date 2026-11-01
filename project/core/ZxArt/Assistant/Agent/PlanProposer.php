<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Agent;

use Exception;
use JsonException;
use stdClass;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\SerializerInterface;
use ZxArt\Assistant\Chat\Dbal\ConversationsDbal;
use ZxArt\Assistant\Chat\Dbal\MessagesDbal;
use ZxArt\Assistant\Chat\Dbal\PlansDbal;
use ZxArt\Assistant\Chat\Dto\PlanDto;
use ZxArt\Assistant\Chat\MessageKind;
use ZxArt\Assistant\Chat\MessageRole;
use ZxArt\Assistant\Llm\LlmToolDto;
use ZxArt\Assistant\Tools\ToolRegistry;
use ZxArt\Assistant\Tools\ToolRequestBuilder;
use ZxArt\Assistant\Tools\UnknownToolException;
use ZxArt\Assistant\Tools\WriteTool;

/**
 * The `propose_plan` call: checks every action, previews those it can, and
 * stores the plan for the user to approve. An action that takes an id an earlier
 * action creates is not previewed — what it refers to does not exist yet.
 */
final readonly class PlanProposer
{
    public const string TOOL_NAME = 'propose_plan';

    public function __construct(
        private ToolRegistry $tools,
        private ToolRequestBuilder $requestBuilder,
        private SerializerInterface $serializer,
        private PlanReferences $references,
        private PlansDbal $plans,
        private MessagesDbal $messages,
        private ConversationsDbal $conversations,
        private WriteToolCatalogue $writeTools,
    ) {
    }

    /**
     * What the model is told once its plan is proposed.
     */
    public static function proposedNote(int $planId): string
    {
        return "Plan $planId is proposed and waits for the user to approve it.";
    }

    public function tool(): LlmToolDto
    {
        return new LlmToolDto(
            name: self::TOOL_NAME,
            description: 'Proposes changes to the site as a plan of write tool calls, run in order once the user approves it. '
                . 'Each action names a write tool and its arguments; {"$ref": N} in place of an id takes the id action N creates.'
                . ' Read what a write tool does and its arguments with ' . WriteToolCatalogue::TOOL_NAME . ' before you use it.'
                . ' The write tools: ' . $this->writeTools->list() . '.',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'title' => ['type' => 'string', 'description' => 'What the plan does, in the user\'s language'],
                    'actions' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'tool' => ['type' => 'string'],
                                'arguments' => ['type' => 'object'],
                            ],
                            'required' => ['tool', 'arguments'],
                        ],
                    ],
                ],
                'required' => ['title', 'actions'],
            ],
        );
    }

    /**
     * @throws PlanRefusedException
     * @throws JsonException
     */
    public function propose(int $conversationId, string $callId, string $arguments): PlanDto
    {
        try {
            $decoded = json_decode($arguments, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new PlanRefusedException('The arguments are not valid JSON: ' . $exception->getMessage(), 0, $exception);
        }
        if (!$decoded instanceof stdClass || !is_string($decoded->title ?? null) || !is_array($decoded->actions ?? null)) {
            throw new PlanRefusedException('The plan needs a title and a list of actions');
        }
        if ($decoded->actions === []) {
            throw new PlanRefusedException('The plan has no actions');
        }
        $actions = [];
        foreach (array_values($decoded->actions) as $index => $action) {
            $actions[] = $this->checked($index + 1, $action);
        }

        $now = time();
        $planId = $this->plans->create($conversationId, $decoded->title, $actions, $now);
        $this->messages->append($conversationId, MessageRole::Assistant, MessageKind::Plan, $decoded->title, [
            'callId' => $callId,
            'arguments' => $arguments,
            'planId' => $planId,
        ], $now);
        $this->conversations->touch($conversationId, $now);

        return $this->plans->find($planId) ?? throw new PlanRefusedException('The plan was not stored');
    }

    /**
     * @return array<string, mixed> the action as the plan stores it
     * @throws PlanRefusedException
     */
    private function checked(int $number, mixed $action): array
    {
        if (!$action instanceof stdClass || !is_string($action->tool ?? null) || !($action->arguments ?? null) instanceof stdClass) {
            throw new PlanRefusedException("Action $number needs a tool name and an arguments object");
        }
        try {
            $tool = $this->tools->get($action->tool);
        } catch (UnknownToolException) {
            throw new PlanRefusedException("Action $number: there is no tool named {$action->tool}");
        }
        if (!$tool instanceof WriteTool) {
            throw new PlanRefusedException("Action $number: {$action->tool} does not change the site; call it directly");
        }
        $dependsOn = $this->references->find($action->arguments);
        foreach ($dependsOn as $referred) {
            if ($referred < 1 || $referred >= $number) {
                throw new PlanRefusedException("Action $number refers to action $referred, which does not come before it");
            }
        }
        $arguments = json_encode($action->arguments, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $preview = null;
        if ($dependsOn === []) {
            try {
                $preview = $tool->preview($this->requestBuilder->build($tool->requestClass(), $arguments));
            } catch (Exception $exception) {
                throw new PlanRefusedException("Action $number ({$action->tool}): " . $exception->getMessage(), 0, $exception);
            }
        }

        return [
            'tool' => $action->tool,
            'arguments' => $arguments,
            'preview' => $preview === null ? null : json_decode($this->serializer->serialize($preview, 'json', [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE]), true, 512, JSON_THROW_ON_ERROR),
            'dependsOn' => $dependsOn,
            'status' => PlanActionStatus::Pending->value,
            'result' => null,
            'error' => null,
        ];
    }
}
