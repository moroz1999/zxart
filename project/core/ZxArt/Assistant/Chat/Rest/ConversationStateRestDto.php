<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Rest;

use ZxArt\Shared\Rest\MentionedEntityRestDto;

final readonly class ConversationStateRestDto
{
    /**
     * @param list<MessageRestDto> $messages
     * @param list<PlanRestDto> $plans
     * @param array<string, MentionedEntityRestDto> $refs
     */
    public function __construct(
        public int $conversationId,
        public array $messages,
        public array $plans,
        public ?RunRestDto $activeRun,
        public array $refs,
    ) {
    }
}
