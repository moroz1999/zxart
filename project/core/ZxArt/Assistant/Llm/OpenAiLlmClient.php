<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Llm;

use OpenAI\Contracts\ClientContract;
use OpenAI\Responses\Responses\Output\OutputFunctionToolCall;
use JsonException;
use Throwable;
use ZxArt\Logs\Log;
use ZxArt\Shared\Images\ImageDto;

/**
 * The OpenAI Responses API with function tools. Nothing is stored on OpenAI's
 * side: every request carries the whole conversation.
 *
 * Every exchange is logged under its conversation and run, as what is new: the
 * first request of a run from the model's last turn on (what came before is in
 * the log of the earlier runs), each later one from where the previous request
 * of the run ended. Tools are named only, images left out; the response is
 * logged raw but for its echo of the tools.
 */
final class OpenAiLlmClient implements LlmClient
{
    /** @var array<int, int> the input items already logged, by run */
    private array $loggedItems = [];

    public function __construct(
        private readonly ClientContract $client,
        private readonly string $model,
        private readonly Log $log,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function complete(array $messages, array $tools, int $conversationId, int $runId): LlmReplyDto
    {
        $input = [];
        foreach ($messages as $message) {
            array_push($input, ...$this->items($message));
        }
        $request = ['model' => $this->model, 'store' => false, 'input' => $input];
        if ($tools !== []) {
            $request['tools'] = array_map($this->tool(...), $tools);
        }
        $prefix = "conversation $conversationId ";
        $this->logRequest($prefix, $runId, $input, $tools);
        $startedAt = microtime(true);
        try {
            $response = $this->client->responses()->create($request);
        } catch (Throwable $throwable) {
            $this->log->logMessage($prefix . 'failed after ' . $this->elapsed($startedAt) . ': ' . $throwable->getMessage(), E_USER_WARNING, $runId);
            throw new LlmException('The model could not be asked: ' . $throwable->getMessage(), 0, $throwable);
        }
        $logged = $response->toArray();
        unset($logged['tools']);
        $this->log->logMessage($prefix . 'response after ' . $this->elapsed($startedAt) . ' ' . $this->json($logged), E_USER_NOTICE, $runId);
        if ($response->error !== null) {
            throw new LlmException('The model failed: ' . $response->error->message);
        }
        $toolCalls = [];
        foreach ($response->output as $item) {
            if ($item instanceof OutputFunctionToolCall) {
                $toolCalls[] = new LlmToolCallDto($item->callId, $item->name, $item->arguments);
            }
        }

        return new LlmReplyDto(
            text: $response->outputText ?? '',
            toolCalls: $toolCalls,
            tokensIn: $response->usage?->inputTokens ?? 0,
            tokensOut: $response->usage?->outputTokens ?? 0,
        );
    }

    /**
     * @param list<array<string, mixed>> $input
     * @param list<LlmToolDto> $tools
     * @throws JsonException
     */
    private function logRequest(string $prefix, int $runId, array $input, array $tools): void
    {
        $from = $this->loggedItems[$runId] ?? $this->afterModelsLastTurn($input);
        $count = count($input);
        $this->loggedItems[$runId] = $count;
        $names = implode(', ', array_map(static fn(LlmToolDto $tool): string => $tool->name, $tools));
        $this->log->logMessage(
            $prefix . 'request ' . $this->model . ' input[' . $from . '..' . ($count - 1) . '] of ' . $count
            . ($names === '' ? '' : ', tools: ' . $names) . ' '
            . $this->json($this->withoutImages(array_slice($input, $from))),
            E_USER_NOTICE,
            $runId,
        );
    }

    /**
     * @param list<array<string, mixed>> $input
     */
    private function afterModelsLastTurn(array $input): int
    {
        for ($index = count($input) - 1; $index >= 0; $index--) {
            $item = $input[$index];
            if (($item['role'] ?? null) === LlmRole::Assistant->value || isset($item['type'])) {
                return $index + 1;
            }
        }

        return 0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function items(LlmMessageDto $message): array
    {
        if ($message->role === LlmRole::Tool) {
            return [['type' => 'function_call_output', 'call_id' => $message->toolCallId, 'output' => $message->text]];
        }
        $items = [];
        if ($message->images !== []) {
            $items[] = [
                'role' => $message->role->value,
                'content' => [
                    ['type' => 'input_text', 'text' => $message->text],
                    ...array_map(
                        static fn(ImageDto $image): array => [
                            'type' => 'input_image',
                            'image_url' => 'data:' . $image->mediaType . ';base64,' . $image->data,
                        ],
                        $message->images,
                    ),
                ],
            ];
        } elseif ($message->text !== '' || $message->toolCalls === []) {
            $items[] = ['role' => $message->role->value, 'content' => $message->text];
        }
        foreach ($message->toolCalls as $call) {
            $items[] = ['type' => 'function_call', 'call_id' => $call->id, 'name' => $call->name, 'arguments' => $call->arguments];
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function tool(LlmToolDto $tool): array
    {
        return [
            'type' => 'function',
            'name' => $tool->name,
            'description' => $tool->description,
            'parameters' => $tool->parameters,
        ];
    }

    /**
     * @param array<array-key, mixed> $value
     * @return array<array-key, mixed>
     */
    private function withoutImages(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->withoutImages($item);
            } elseif ($key === 'image_url' && is_string($item) && preg_match('#^data:([^;]+);base64,(.*)$#s', $item, $match) === 1) {
                $value[$key] = '<' . $match[1] . ', ' . (int)(strlen($match[2]) * 3 / 4) . ' bytes>';
            }
        }

        return $value;
    }

    /**
     * @param array<array-key, mixed> $value
     * @throws JsonException
     */
    private function json(array $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function elapsed(float $startedAt): string
    {
        return (int)round((microtime(true) - $startedAt) * 1000) . ' ms';
    }
}
