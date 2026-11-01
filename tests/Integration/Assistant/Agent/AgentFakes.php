<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant\Agent;

use RuntimeException;
use Throwable;
use ZxArt\Assistant\Llm\LlmClient;
use ZxArt\Assistant\Llm\LlmMessageDto;
use ZxArt\Assistant\Llm\LlmReplyDto;
use ZxArt\Assistant\Llm\LlmToolDto;
use ZxArt\Assistant\Tools\ReadTool;

/**
 * Answers with the replies it was given, in order, and keeps what it was asked.
 */
final class ScriptedLlmClient implements LlmClient
{
    /** @var list<list<LlmMessageDto>> */
    public array $askedWith = [];
    /** @var list<list<string>> tool names */
    public array $offeredTools = [];
    /** @var ?callable(int): void called with the run after each reply */
    public $afterReply = null;

    /**
     * @param list<LlmReplyDto|Throwable> $replies
     */
    public function __construct(private array $replies)
    {
    }

    public function complete(array $messages, array $tools, int $conversationId, int $runId): LlmReplyDto
    {
        $this->askedWith[] = $messages;
        $this->offeredTools[] = array_map(static fn(LlmToolDto $tool): string => $tool->name, $tools);
        $reply = array_shift($this->replies) ?? throw new RuntimeException('No reply scripted');
        if ($reply instanceof Throwable) {
            throw $reply;
        }
        if ($this->afterReply !== null) {
            ($this->afterReply)($runId);
        }

        return $reply;
    }
}

final readonly class EchoRequestDto
{
    public function __construct(
        public string $text,
    ) {
    }
}

final readonly class EchoResultDto
{
    public function __construct(
        public string $echoed,
    ) {
    }
}

/**
 * @implements ReadTool<EchoRequestDto>
 */
final readonly class EchoTool implements ReadTool
{
    public function name(): string
    {
        return 'echo';
    }

    public function description(): string
    {
        return 'Repeats the text.';
    }

    public function requestClass(): string
    {
        return EchoRequestDto::class;
    }

    public function execute(object $request): EchoResultDto
    {
        return new EchoResultDto($request->text);
    }
}
