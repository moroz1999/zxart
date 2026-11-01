<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Llm;

/**
 * A chat model that may answer with tool calls.
 */
interface LlmClient
{
    /**
     * @param list<LlmMessageDto> $messages
     * @param list<LlmToolDto> $tools
     * @param int $conversationId the conversation asking, for the log
     * @param int $runId the run asking, for the log
     * @throws LlmException
     */
    public function complete(array $messages, array $tools, int $conversationId, int $runId): LlmReplyDto;
}
