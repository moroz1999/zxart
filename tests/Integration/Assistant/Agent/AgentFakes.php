<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant\Agent;

use RuntimeException;
use Throwable;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Llm\LlmClient;
use ZxArt\Assistant\Llm\LlmMessageDto;
use ZxArt\Assistant\Llm\LlmReplyDto;
use ZxArt\Assistant\Llm\LlmToolDto;
use ZxArt\Shared\EntityType;

/**
 * Answers with the replies it was given, in order, and keeps what it was asked.
 */
final class ScriptedLlmClient implements LlmClient
{
    /** @var list<list<LlmMessageDto>> */
    public array $askedWith = [];
    /** @var list<list<string>> tool names */
    public array $offeredTools = [];
    /** @var list<list<LlmToolDto>> */
    public array $offered = [];
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
        $this->offered[] = $tools;
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

final readonly class EchoResultDto
{
    public function __construct(
        public string $echoed,
    ) {
    }
}

/**
 * A read action of the assistant's own: repeats the text.
 */
final readonly class EchoAction implements ReadAction
{
    public const string SCHEMA = '{"type": "object", "properties": {"text": {"type": "string"}}, "required": ["text"], "additionalProperties": false}';

    public function name(): string
    {
        return 'echo';
    }

    public function description(): string
    {
        return 'Repeats the text.';
    }

    public function schema(): string
    {
        return self::SCHEMA;
    }

    public function read(Arguments $arguments): EchoResultDto
    {
        return new EchoResultDto((string)$arguments->text('text'));
    }
}

/**
 * A write action of the assistant's own that writes nothing.
 */
final readonly class RenameAction implements WriteAction
{
    public function name(): string
    {
        return 'rename_thing';
    }

    public function description(): string
    {
        return 'Renames a thing.';
    }

    public function schema(): string
    {
        return '{"type": "object", "properties": {"id": {"type": "integer"}}, "required": ["id"], "additionalProperties": false}';
    }

    public function kind(): ChangeKind
    {
        return ChangeKind::Update;
    }

    public function entityType(): EntityType
    {
        return EntityType::Prod;
    }

    public function state(Arguments $arguments): ?object
    {
        return null;
    }

    public function write(Arguments $arguments): object
    {
        return new EchoResultDto('renamed');
    }
}
